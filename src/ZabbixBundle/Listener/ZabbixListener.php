<?php

namespace ZabbixBundle\Listener;

class ZabbixListener extends \MainBundle\Listener\BasicListener {

    private $last_monitor_flush = NULL;
    private $last_monitor_lines = NULL;
    //private $cycle_seconds;

    public function __construct() {

        # Install signal handler
        //$this->cycle_seconds = $cycle_seconds;
        //declare(ticks = 1);
        //pcntl_signal(SIGALRM, array(&$this, 'triggerMonitorNotify'),true);@todo
        //pcntl_alarm($cycle_seconds);@todo
    }

    public function input(\Symfony\Component\EventDispatcher\Event $event) {

        $pheanstalk = new \Pheanstalk_Pheanstalk('127.0.0.1');

        $pheanstalk->useTube('testtube')
                   ->put("job payload goes here\n");

        // @todo implement error parser (warning , error counter), create keys with values
        $parser = $this->get('logfile.parser');

        // zabbix logging
        $monitor = new \ZabbixBundle\Monitoring\Monitoring();
        $monitor->add($parser->getResults());
    } // end: input()

    public function triggerMonitorNotify(){

        $this->get('logger')->add("TIME TO NOTIFY MONITORING SYSTEM\n");

        /* Only call logfile management on monitoring cycle, this reduces overhead */
        $this->maintainLogfile();


        # Get data
        $monitor_values = array();
        foreach ($this->parsers as &$parser) {
            $monitor_values = array_merge($monitor_values,$parser->getResults());
        }
        print_r($monitor_values);

        # Execute notifiers
        foreach ($this->notifiers as &$notifier) {
            $notifier->notify($values);
        }

        # Set time
        $this->last_monitor_flush = time();
        # Flush filehandle
        fflush($this->logfile_handle);

        # Activate signal handler
        //pcntl_alarm($this->cycle_seconds);
    }
} // end: ZabbixListener
