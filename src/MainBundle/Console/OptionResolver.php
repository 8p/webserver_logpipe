<?php

namespace MainBundle\Console;

class OptionResolver {

    protected $container;

    public function __construct() {

        $this->container = \MainBundle\Container\ServiceContainer::getInstance();
    } // end: __construct()

    public function getOptions() {

        $config = $this->container->get('config');

        return $config->get('console.options');
    } // end: getOptions()

    /**
     * @todo object oriented! creating Option class and GetOpt
     * @throws \Exception
     */
    public function run() {

        $options  = $this->getOptions();
        $short    = '';
        $long     = array();
        $required = array();

        foreach($options as $name => $option) :

            $value = null;

            if(!isset($option['command']) || !isset($option['command'][0])) :

                throw new \Exception(sprintf('command not defined for option %s', $name));
            endif;

            if(isset($option['argument']) && $option['argument']) :

                // ":"  => required value
                // "::" => optional value
                $value = ':';
            endif;

            if(isset($option['required']) && $option['required']) :

                $required[] = $name;
            endif;

            array_push($long, $option['command'][0] . $value);

            if(isset($option['command'][1])) :

                $short .= $option['command'][1];
            endif;
        endforeach;

        $arguments = getopt($short, $long);

        if(isset($arguments['help']) || isset($arguments['h'])) :

            echo $this->help($options);
            
            return false;
        endif;

        foreach($required as $check) :

            $option = $options[$check];

            if(!in_array($check, $option['command'])) :

                throw new \Exception(sprintf('argument %s missing, use "help" for more information', $check));
            endif;
        endforeach;

        foreach($options as $name => $option) :

            $active = false;
            $value  = false;

            if(isset($option['command'][1]) && array_key_exists($option['command'][1], $arguments)) :

                $active   = true;
                $value = $arguments[$option['command'][1]];
            endif;

            if(!array_key_exists($option['command'][0], $arguments) && !$active) :

                continue;
            elseif(!$active) :

                $value = $arguments[$option['command'][0]];
            endif;

            $config = $this->container->get('config');

            if(isset($option['config']) && ($key = $option['config'])) :

                $this->container->get('logger')->add(sprintf('setting config %s => %s', $key, $value));

                $config->set($key, $value);
            endif;
        endforeach;
        
        return true;
    } // end: run()

    /**
     * Help
     *
     * @todo cleaner, a little bit cleaner :D
     * @param  array $options
     * @return string $output
     */
    public function help($options) {

        $output    = array();
        $example   = 'logpipe.phar';
        $arguments = array();

        $output[] = sprintf('Webserver Logpipe v%s', APP_VERSION);
        $output[] = '=======================';

        foreach($options as $option) :

            $example .= ' ';

            if(!isset($option['required']) || !$option['required']) :

                $example .= '[';
            endif;

            if(isset($option['command'][1])) :

                $example .= sprintf('(-%s|', $option['command'][1]);
            endif;

            $example .= sprintf('--%s', $option['command'][0]);

            if(isset($option['command'][1])) :

                $example .= ')';
            endif;

            // type of argument
            if(isset($option['argument']) && $option['argument']) :

                $example .= sprintf(' %s', $option['argument']);
            endif;

            if(!isset($option['required']) || !$option['required']) :

                $example .= ']';
            endif;

            // option
            $prefix = '';

            if(isset($option['command'][1])) :

                $prefix .= sprintf('-%s or ', $option['command'][1]);
            endif;

            $prefix .= sprintf('--%s', $option['command'][0]);

            // type of argument
            if(isset($option['argument']) && $option['argument']) :

                $prefix .= sprintf(' (%s)', $option['argument']);
            endif;

            $prefix .= ':';

            $arguments[] = str_pad($prefix, 30, ' ') . $option['description'];
        endforeach;

        $output[] = 'Usage example:';
        $output[] = $example;
        $output[] = '';

        $output[] = 'Options:';
        $output[] = implode(PHP_EOL, $arguments);
        $output[] = PHP_EOL;

        return implode(PHP_EOL, $output);
    } // end: help()
} // end: OptionResolver