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

        $queue = $this->container->get('queue');
        $queue->add('zabbix', $this->getData());
    } // end: add()

    public function push() {

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

    public function set($key, $value) {

        $data = $this->getData();

        if(isset($data[$key])) :

            $data[$key]['value'] += $value;
        else :

            $data[$key] = array(
                'value' => $value,
                'time'  => time()
            );
        endif;

        $this->data = $data;
    } // end: setData()
} // end: Monitoring