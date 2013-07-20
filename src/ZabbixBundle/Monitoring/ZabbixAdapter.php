<?php

namespace ZabbixBundle\Monitoring;

class ZabbixAdapter {

    protected $server            = 'monitoring.domain.tld';
    protected $port              = 10051;
    protected $timeoutConnection = 2; // seconds
    protected $timeoutStream     = 5; // seconds

    /**
     * Set server
     *
     * @param  string $value
     * @return self
     */
    public function setServer($value) {

        $this->server = $value;

        return $this;
    } // end: setServer()

    /**
     * Set port
     *
     * @param  integer $value
     * @return self
     */
    public function setPort($value) {

        $this->port = $value;

        return $this;
    } // end: setPort()

    /**
     * Set timeout connection in seconds
     *
     * @param  integer $value
     * @return self
     */
    public function setTimeoutConnection($value) {

        $this->timeoutConnection = $value;

        return $this;
    } // end: setTimeoutConnection()

    /**
     * Set timeout stream in seconds
     *
     * @param  integer $value
     * @return self
     */
    public function setTimeoutStream($value) {

        $this->timeoutStream = $value;

        return $this;
    } // end: setTimeoutStream()

    /**
     * Search for Zabbix configuration and parse it
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  string $file    path and name of config file
     * @return array  $config
     * @throws \Exception
     */
    public function getDefaultConfig($file = '/var/zabbix/zabbix-agentd.conf') {

        if(!is_file($file)) {

            throw new \Exception(sprintf('Zabbix standard configuration not found (%s)', $file));
        }

        $config = array();

        // remove comments and empty lines
        $configFile   = file($file);
        $configParsed = preg_grep("/^[[:space:]\#]/i", $configFile, PREG_GREP_INVERT);

        $configSplitted = array();

        // split entries (example: Server=127.0.0.1)
        foreach($configParsed as $entry) {

            $explode = explode('=', $entry);

            $configSplitted[trim($explode[0])] = trim($explode[1]);
        }

        foreach($configSplitted as $key => $value) {

            switch($key) {

                case 'Server':

                    // use only the first given address (example: 127.0.0.1,192.168.1.1)
                    $explode          = explode(',', $value);
                    $config['server'] = $explode[0];
                break;

                case 'Hostname':

                    $config['host'] = $value;
                break;

                case 'ServerPort':

                    $config['port'] = $value;
                break;
            }
        }

        return $config;
    } // end: getDefaultConfig()

    /**
     * Send data
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  array  $data  array('key' => ?, 'value' => ?, 'time' => unixtimestamp)
     * @param  string $host  zabbix host
     * @return string
     */
    public function send(array $data, $host) {

        $data2push = array();

        foreach($data as $key => $entry) {

            $data2push[] = array(
                'host'  => $host,
                'key'   => $key,
                'value' => (string) $entry['value'],
                'clock' => $entry['time']
            );
        }

        $this->request = array(
            'request' => 'sender data',
            'data'    => $data2push,
            'clock'   => time()
        );

        return $this->push();
    } // end: send()

    /**
     * Push data to Zabbix
     *
     * @todo if server is not reachable, it takes 5min and no exception will be thrown?!
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @return array $return
     * @throws \Exception
     */
    protected function push() {

        $return     = false;
        $header     = "ZBXD\1%s%s";
        $data       = json_encode($this->request);
        $dataLength = strlen($data);
        $dataHeader = pack('i', $dataLength) . "\0\0\0\0";
        $dataSend   = sprintf($header, $dataHeader, $data);

        /*
         * Socket
         */
        $fp = fsockopen($this->server, $this->port, $errno, $errstr, $this->timeoutConnection);

        if(!$fp) :

            throw new \Exception(sprintf('connecting (%s:%s): %s (%s)', $this->server, $this->port, $errstr, $errno));
        endif;

        if(!fwrite($fp, $dataSend)) {

            throw new \Exception('can\'t send data, shutting down.');
        }

        stream_set_timeout($fp, $this->timeoutStream);

        $receiving = '';

        while(!feof($fp)) {

            $receiving .= fgets($fp, 128);
        }

        if(!$receiving) :

            fclose($fp);
            throw new \Exception(sprintf('no data received (%s:%s)', $this->server, $this->port));
        endif;

        $return = $this->parseReceivedData($receiving);

        fclose($fp);

        return $return;
    } // end: push()

    /**
     * Parse data
     *
     * @param  string $data
     * @return array $return
     * @throws \Exception
     */
    protected function parseReceivedData($data) {

        // use only json in received data
        $data = substr($data, strpos($data, '{'));
        $data = json_decode($data);

        if($data->response != 'success') {

            throw new \Exception('Push failed: ' . print_r($data, true));
        }

        # example for $data->info: Processed 2 Failed 0 Total 2 Seconds spent 0.000529
        $info   = $data->info;
        $return = array();

        // processed
        $search = 'Processed';
        $start  = strpos($info, $search) + strlen($search) + 1;
        $end    = strpos($info, ' ', $start);

        $return['processed'] = substr($info, $start, $end - $start);


        // failed
        $search = 'Failed';
        $start  = strpos($info, $search) + strlen($search) + 1;
        $end    = strpos($info, ' ', $start);

        $return['failed'] = substr($info, $start, $end - $start);


        // total
        $search = 'Total';
        $start  = strpos($info, $search) + strlen($search) + 1;
        $end    = strpos($info, ' ', $start);

        $return['total'] = substr($info, $start, $end - $start);


        // duration
        $search = 'Seconds spent';
        $start  = strpos($info, $search) + strlen($search) + 1;

        $return['duration'] = substr($info, $start);

        return $return;
    } // end: parseReceivedData()
} // end: Zabbix