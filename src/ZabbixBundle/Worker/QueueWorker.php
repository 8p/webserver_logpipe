<?php

namespace ZabbixBundle\Worker;

class QueueWorker {

    protected $sleep = 3;
    protected $queueService;

    /**
     * Constructor
     */
    public function __construct() {

        $this->queueService = new \MainBundle\Queue\Pheanstalk(); // @todo loading by config
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
     * Run worker, run
     *
     * @return void
     */
    public function run() {

        while(true) :

            sleep($this->getSleep());

            $data = $this->queueService->getLatestData('zabbix');

            if($data) :

                $monitoring = new \ZabbixBundle\Monitoring\Monitoring();
                $monitoring->setData($data);

                $result = $monitoring->push();

                print_r($result);
            endif;
        endwhile;
    } // end: run()
} // end: QueueWorker