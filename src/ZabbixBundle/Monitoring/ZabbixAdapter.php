<?php

namespace ZabbixBundle\Monitoring;

class ZabbixAdapter {

    protected $server            = 'zabbixtest.intra.dmc.de';
    protected $port              = 10051;
    protected $timeoutConnection = 2; // seconds
    protected $timeoutStream     = 5; // seconds

    /**
     * Set server
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  string $value
     * @return self
     */
    public function setServer($value) {

        $this->server = $value;

        return $this;
    } // end: setServer()

    public function setPort($value) {

        $this->port = $value;

        return $this;
    } // end: setPort()

    public function setTimeoutConnection($value) {

        $this->timeoutConnection = $value;

        return $this;
    } // end: setTimeoutConnection()

    public function setTimeoutStream($value) {

        $this->timeoutStream = $value;

        return $this;
    } // end: setTimeoutStream()

    /**
     * Search for zabbix configuration and parse it
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  string $file    path and name of config file
     * @return array  $config
     */
    public function getDefaultConfig($file = '/var/zabbix/zabbix-agentd.conf') {

        if(!is_file($file)) {

            throw new Exception("Zabbix standard configuration not found ($file)");
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
     * @param  string $host  zabix host
     * @return string
     */
    public function send(array $data, $host) {

        $data2push = array();

        foreach($data as $entry) {

            $data2push[] = array(
                'host'  => $host,
                'key'   => $entry['key'],
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
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @return void
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

        if(!$fp) {

            throw new Exception("Connecting (" . Config::get('monitoring.server') . ":" . Config::get('monitoring.server') . "): $errstr ($errno)");
        } else {

            if(!fwrite($fp, $dataSend)) {

                throw new Exception('can\'t send data, shutting down.');
            }

            stream_set_timeout($fp, $this->timeoutStream);

            $receiving = '';

            while(!feof($fp)) {

                $receiving .= fgets($fp, 128);
            }

            $return = $this->parseReceivedData($receiving);

            fclose($fp);
        } // endif

        return $return;
    } // end: push()

    /**
     * Parse data
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  array $data
     * @return array $return
     */
    protected function parseReceivedData(array $data) {

        // use only json in received data
        $data = substr($data, strpos($data, '{'));
        $data = json_decode($data);

        if($data->response != 'success') {

            throw new Exception('Push failed: ' . print_r($data, true));
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