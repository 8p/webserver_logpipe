<?php

namespace MainBundle\Controller;

class BasicController {

    protected function get($service) {

        return \MainBundle\Container\ServiceContainer::getInstance()->get($service);
    } // end: get()
} // end: BasicController
