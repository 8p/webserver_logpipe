<?php

namespace MainBundle\Queue;

class Pheanstalk extends AbstractQueue {

    protected $service;

    public function __construct() {

        $this->service = new \Pheanstalk_Pheanstalk('127.0.0.1');
    } // end: __construct()

    /**
     * Add data to message queue container (tube)
     *
     * @param  string $container
     * @param  mixed  $data
     * @return void
     */
    public function add($container, $data) {

        $this->service->useTube($container)
                      ->put(json_encode($data));
    } // end: add()

    public function getLastJob($container) {

        try {

            $statsTube = $this->service->statsTube($container);
            $jobsReady = $statsTube['current-jobs-ready'];
        } catch(\Exception $e) {

            $jobsReady = 0;
        }

        if(!$jobsReady) :

            echo date('H:i:s') . ' :: no jobs ready' . PHP_EOL;
            return;
        endif;

        echo date('H:i:s') . sprintf(' :: found %d jobs', $jobsReady) . PHP_EOL;

        $i = 1;
        while($i < $jobsReady) :

            $job = $this->service->watch($container)
                                 ->ignore('default')
                                 ->reserve();

            echo date('H:i:s') . sprintf(' :: (%d) throw away: ', $i) . $job->getData() . PHP_EOL;

            $this->service->delete($job);
            $i++;
        endwhile;

        $job = $this->service->watch($container)
                    ->ignore('default')
                    ->reserve();

        echo date('H:i:s') . ' :: last: ' . $job->getData() . PHP_EOL;

        $this->service->delete($job);

        return $job;
    } // end: getLastJob()

    public function getLatestData($container) {

        $job = $this->getLastJob($container);

        if($job) :

            return json_decode($job->getData(), true);
        endif;

        return null;
    } // end: getLatestData()
} // end: MessageQueue