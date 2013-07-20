<?php

namespace ZabbixBundle\Listener;

class ZabbixListener extends \MainBundle\Listener\BasicListener {

    /**
     * Constructor
     */
    public function __construct() {

    } // end: __construct()

    /**
     * Handling input
     *
     * @param  \Symfony\Component\EventDispatcher\Event $event
     * @return void
     */
    public function input(\Symfony\Component\EventDispatcher\Event $event) {

        $parser = $this->get('logfile.parser');

        $monitor = new \ZabbixBundle\Monitoring\Monitoring();
        $monitor->add($parser->getResults());
    } // end: input()
} // end: ZabbixListener
