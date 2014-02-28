<?php

namespace MainBundle\Stream;

class StdIn {

    protected $cycle = 3; // timeout in seconds

    public function __construct() {

        $this->container = \MainBundle\Container\ServiceContainer::getInstance();
        $this->cycle     = $this->container->get('config')->get('cycle');
    } // end: __construct()

    public function read() {

        $read   = array(STDIN);
        $write  = null;
        $except = null;
        $result = stream_select($read, $write, $except, $this->cycle);

        if($result === false) :

            throw new \Exception('stream_select failed');
        endif;

        if($result === 0) :

            return false;
        endif;

        $data = fgets(STDIN);

        return $data;
    } // end: read()

    public function isEnd() {

        $status = stream_get_meta_data(STDIN);

        return ($status['eof'] == true) ? true : false;
    } // end: isEnd()
} // end: StdIn