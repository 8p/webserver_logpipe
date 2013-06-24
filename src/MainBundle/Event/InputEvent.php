<?php

namespace MainBundle\Event;

class InputEvent extends \Symfony\Component\EventDispatcher\Event {

    protected $input;

    public function setInput($value) {

        $this->input = $value;
    }

    public function getInput() {

        return $this->input;
    }
}