<?php

namespace MainBundle\Listener;

class BasicListener {

    protected function get($name) {

        return \MainBundle\Container\ServiceContainer::getInstance()->get($name);
    } // end: get()
} // end: BasicListener
