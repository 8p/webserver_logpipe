<?php

namespace MainBundle\Queue;

abstract class AbstractQueue {

    abstract function add($container, $data);
} // end: AbstractQueue