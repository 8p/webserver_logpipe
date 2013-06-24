<?php

namespace ZabbixBundle\Monitoring;

class Monitoring {

    protected $data = array();
    protected $start;
    protected $wait = 0; // seconds, sum input for transmitting

    public function __construct() {

        $this->start = 0;
    }

    public function add($input) {

        //@todo error? warning?

        if(preg_match("/[.*] PHP (Fatal)/", $input)) {

            $type = 'fatal';
        } else {

            $type = 'warning';
        }

        $counter = 0;

        if(isset($this->data[$type])) :

            $counter = $this->data[$type]['value'];
        endif;

        $this->data[$type] =  array(
            'key'   => sprintf('error.%s.counter', $type),
            'value' => ++$counter,
            'time'  => time()
        );

        // check if it is time to send some cool data ;)
        if($this->start >= $this->wait) :

            $this->push($this->getData(), $this->getHost());
            $this->start = 0;
        endif;
    }

    protected function push($data, $host) {
print_r($data);
        $adapter = new ZabbixAdapter();
        print_r($adapter->send($data, $host));
    }

    public function getData() {

        return $this->data;
    }

    public function getHost() {

        return 'api-myhb-v01.dmc-dev-vm-v3.intra.dmc.de';
    }
}