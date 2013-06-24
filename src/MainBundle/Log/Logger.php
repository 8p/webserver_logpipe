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

            /*if($this->logfile_handle != NULL){

                fwrite($this->logfile_handle,"webserver_logpipe: "+$text);
            }*/
        endif;
    }
}