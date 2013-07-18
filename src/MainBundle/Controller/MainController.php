<?php

namespace MainBundle\Controller;

class MainController extends BasicController {

    protected $start;

    public function __construct() {

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

        $eventHandler = $this->get('event.handler');
        $eventHandler->dispatch('input', $event);
    } // end: handleAction()
} // end: MainController
