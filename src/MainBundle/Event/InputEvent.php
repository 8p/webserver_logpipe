<?php

namespace MainBundle\Event;

class InputEvent extends \Symfony\Component\EventDispatcher\Event {

    protected $input;

    public function setInput($value) {

        $this->input = $value;
    } // end: setInput()

    public function getInput() {

        return $this->input;
    } // end: getInput()
} // end: InputEvent