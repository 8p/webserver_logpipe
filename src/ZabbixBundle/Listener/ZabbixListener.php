<?php

namespace ZabbixBundle\Listener;

class ZabbixListener extends \MainBundle\Listener\BasicListener {

    /**
     * Handling input
     *
     * @param  \Symfony\Component\EventDispatcher\Event $event
     * @return void
     */
    public function input(\Symfony\Component\EventDispatcher\Event $event) {

        $parser  = $this->get('logfile.parser');
        $monitor = $this->get('monitoring');
        $monitor->add($parser->getResults());
    } // end: input()

    /**
     * Send data to zabbix server
     *
     * @param  \Symfony\Component\EventDispatcher\Event $event
     * @return void
     */
    public function send(\Symfony\Component\EventDispatcher\Event $event) {

        $config  = $this->get('config');
        $monitor = $this->get('monitoring');

        if($config->get('zabbix.heartbeat')) :

            $monitor->addHeartbeat();
        endif;

        $monitor->push();
    } // end: send()
} // end: ZabbixListener
