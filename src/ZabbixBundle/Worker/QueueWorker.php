<?php

namespace ZabbixBundle\Worker;

use       \MainBundle\Container\ServiceContainer;

class QueueWorker {

    protected $sleep = 3;
    protected $queueService;
    protected $container;
    protected $heartbeat = false;

    /**
     * Constructor
     */
    public function __construct() {

        $this->container    = ServiceContainer::getInstance();
        $this->queueService = $this->container->get('queue');
    } // end: __construct()

    /**
     * Set sleep time in seconds
     *
     * @param integer $value
     */
    public function setSleep($value) {

        $this->sleep = $value;
    } // end: setSleep()

    /**
     * Get sleep time in seconds
     *
     * @return integer $sleep
     */
    public function getSleep() {

        return $this->sleep;
    } // end: getSleep()

    /**
     * Set heartbeat (true|false)
     *
     * @param boolean $value
     */
    public function setHeartbeat($value) {

        $this->heartbeat = $value;
    } // end: setHeartbeat()

    /**
     * Is heartbeat on?
     *
     * @return boolean
     */
    public function isHeartbeat() {

        return $this->heartbeat;
    } // end: isHeartbeat()

    /**
     * Add heartbeat to given data
     *
     * @param  array $data
     * @return array
     */
    public function addHeartbeat(array $data = array()) {

        $data['logpipe.heartbeat'] = array(
            'value' => 1,
            'time' => time()
        );

        return $data;
    } // end: addHeartbeat()

    /**
     * Run worker, run
     *
     * @return void
     */
    public function run() {

        while(true) :

            sleep($this->getSleep());

            $data = $this->queueService->getLatestData('zabbix');

            if($this->isHeartbeat()) :

                $data = $this->addHeartbeat($data);
            endif;

            if($data) :

                $monitoring = new \ZabbixBundle\Monitoring\Monitoring();
                $monitoring->setData($data);

                $result = $monitoring->push();

                print_r($result);
            endif;
        endwhile;
    } // end: run()
} // end: QueueWorker