<?php

namespace MainBundle\Event;

use       Symfony\Component\EventDispatcher\EventDispatcher,
          Symfony\Component\Yaml\Yaml;

class Handler {

    protected $dispatcher;

    public function __construct() {

        $this->dispatcher = new EventDispatcher();
    }

    public function registerListeners() {

        $listeners = Yaml::parse(__DIR__ . '/../../../app/config/listeners.yml');

        foreach($listeners as $name => $listener) :

            $classObject = new $listener['class']();

            foreach($listener['tags'] as $tag) :

                $priority = isset($tag['priority']) ? $tag['priority'] : null;

                $this->dispatcher->addListener($tag['event'], array($classObject, $tag['method']), $priority);
            endforeach;
        endforeach;

        return true;
    }

    public function dispatch($name, $event = null) {

        return $this->dispatcher->dispatch($name, $event);
    }
}
