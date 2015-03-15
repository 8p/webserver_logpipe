<?php

namespace MainBundle\Log;

class Logger {

    protected $config;

    public function __construct($config) {

        $this->config = $config;
    }

    public function add($message) {

        if($this->config->get('debug')) :

            fwrite(STDERR, $message);

        endif;
    }
}
