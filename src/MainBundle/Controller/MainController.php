<?php

namespace MainBundle\Controller;

class MainController extends BasicController {

    protected $start;

    public function __construct() {

        // register listeners
        $eventHandler = $this->get('event.handler');
        $eventHandler->registerListeners();
    }

    public function handleAction($input) {

        if(!$input) :

            return;
        endif;

        $event = $this->get('event.input_event');
        $event->setInput($input);

        $eventHandler = $this->get('event.handler');
        $eventHandler->dispatch('input', $event);
    }
}
