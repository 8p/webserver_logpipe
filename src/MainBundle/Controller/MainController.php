<?php

namespace MainBundle\Controller;

class MainController extends BasicController {

    protected $cycle;
    protected $next;

    public function __construct() {

        $config = $this->get('config');

        $this->cycle = $config->get('cycle');
        $this->next  = time() + $this->cycle;

        // register listeners
        $eventHandler = $this->get('event.handler');
        $eventHandler->registerListeners();
    } // end: __construct()

    /**
     * Handle input
     *
     * @param  string $input
     * @return void
     */
    public function handleAction($input) {

        if(!$input) :

            return;
        endif;

        $event = $this->get('event.input_event');
        $event->setInput($input);

        try {

            $eventHandler = $this->get('event.handler');
            $eventHandler->dispatch('input', $event);
        } catch(\Exception $e) {

            $this->handleError($e);
        }
    } // end: handleAction()

    /**
     * Handle cycle, fire event
     *
     * @return void
     */
    public function cycleAction() {

        if(time() >= $this->next) :

            echo sprintf('(%s) TIME TO SEND', date('Y-m-d H:i:s')) . PHP_EOL;//@todo

            try {

                $eventHandler = $this->get('event.handler');
                $eventHandler->dispatch('cycle');
            } catch(\Exception $e) {

                $this->handleError($e);
            }

            $this->next = time() + $this->cycle;
        endif;
    } // end: cycleAction()

    private function handleError(\Exception $e) {

        $handle  = fopen('php://stderr', 'w');
        $message = sprintf('Apache Logpipe Error (#%s) in %s line %d: %s', $e->getFile(), $e->getLine(), $e->getCode(), $e->getMessage());

        fwrite($handle, $message . PHP_EOL);
        fclose($handle);
    } // end: handleError()
} // end: MainController
