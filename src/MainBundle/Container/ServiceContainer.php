<?php

namespace MainBundle\Container;

use       Symfony\Component\Yaml\Yaml;

class ServiceContainer {

    private static $instance;
    protected $container;

    private function __construct() {

        $this->container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->container->setParameter('root.dir', realpath(__DIR__ . '/../../..'));

        $this->registerServices();
    }

    public static function getInstance() {

        if(!self::$instance) :

            self::$instance = new self();
        endif;

        return self::$instance;
    }

    protected function registerServices() {

        $services = Yaml::parse(__DIR__ . '/../../../app/config/services.yml');

        foreach($services as $name => $service) :

            $registered = $this->container->register($name, $service['class']);

            // register service as a parameter (useful for services.yml, %SERVICENAME%)
            $this->container->setParameter($name, $registered);

            if(isset($service['arguments'])) :

                foreach($service['arguments'] as $argument) :

                    $registered->addArgument($argument);
                endforeach;
            endif;
        endforeach;
    }

    public function get($service) {

        return $this->container->get($service);
    }
}