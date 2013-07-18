<?php

namespace ZabbixBundle\Monitoring;

class Monitoring {

    protected $data = array();
    protected $start;
    protected $wait = 0; // seconds, sum input for transmitting

    public function __construct() {

        $this->start = 0;
    }

    public function add(array $input) {

        foreach($input as $key => $value) :

            $this->setData($key, $value);
        endforeach;

        //@todo add to message queue?
    }

    protected function push($data, $host) {

        $adapter = new ZabbixAdapter();
        print_r($adapter->send($data, $host));
    } // end: push()

    public function getData() {

        return $this->data;
    } // end: getData()

    public function setData($key, $value) {

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

    public function getHost() {

        return 'api-myhb-v01.dmc-dev-vm-v3.intra.dmc.de';
    } // end: getHost()
} // end: Monitoring