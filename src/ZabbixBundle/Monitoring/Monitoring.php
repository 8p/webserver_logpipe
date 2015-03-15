<?php

namespace ZabbixBundle\Monitoring;

use       \MainBundle\Container\ServiceContainer;

class Monitoring {

    protected $data = array();
    protected $container;
    protected $config;

    public function __construct() {

        $this->container = ServiceContainer::getInstance();
        $this->config    = $this->container->get('config');
    } // end: __construct()

    public function add(array $input) {

        foreach($input as $key => $value) :

            $this->set($key, $value);
        endforeach;
    } // end: add()

    public function push() {

        if(!$this->getData()) :

            echo 'no data to send' . PHP_EOL;
            return false;
        endif;

        $adapter = new ZabbixAdapter();
        $adapter->setServer(            $this->config->get('zabbix.server.host'))
                ->setPort(              $this->config->get('zabbix.server.port'))
                ->setTimeoutConnection( $this->config->get('zabbix.server.timeout.connection'))
                ->setTimeoutStream(     $this->config->get('zabbix.server.timeout.stream'));

        return $adapter->send($this->getData(), $this->config->get('zabbix.host'));
    } // end: push()

    public function getData() {

        return $this->data;
    } // end: getData()

    public function setData(array $data) {

        $this->data = $data;
    } // end: setData()

    /**
     * Add heartbeat data
     *
     * @return void
     */
    public function addHeartbeat() {

        $this->set('logpipe.heartbeat', 1, true);
    } // end: addHeartbeat()

    public function set($key, $value, $strict = false) {

        $data = $this->getData();

        if(isset($data[$key]) && !$strict) :

            $data[$key]['value'] += $value;
            $data[$key]['time']   = time();
        else :

            $data[$key] = array(
                'value' => $value,
                'time'  => time()
            );
        endif;

        $this->data = $data;
    } // end: setData()
} // end: Monitoring
