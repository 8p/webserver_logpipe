<?php

namespace MainBundle\Config;

use       Symfony\Component\Yaml\Yaml;

class Config {

    const MODE_NORMAL = 'normal';
    const MODE_RANDOM = 'random';

    private $config = array();

    public function __construct($file) {

        $this->init($file);
    } // end: __construct()

    /**
     * Init config values
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  array|string $config  config array, value or file path
     * @param  array        $identifiers
     * @return this
     * @throws Exception
     */
    public function init($config, array $identifiers = null) {

        if(is_array($config)) {

            $this->config = $this->merge($config);

            return $this;
        }

        if(file_exists($config)) {

            $configTemp = Yaml::parse($config);

            $this->import($configTemp, realpath(dirname($config)), $identifiers);

            $this->config = $this->merge($configTemp, $identifiers);

            return $this;
        }

        $this->config = $config;
    } // end: init()

    /**
     * Merge configs
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  array $config
     * @return array
     */
    protected function merge(array $config) {

        return array_replace_recursive($this->config, $config);
    } // end: merge()

    protected function import($config, $dir, $identifiers) {

        if(!isset($config['imports']) || !is_array($config['imports'])) {

            return null;
        }

        foreach($config['imports'] as $import) {

            if(isset($import['resource']) && $resource = $import['resource']) {

                $resource = $dir . DIRECTORY_SEPARATOR . $resource;

                $this->init($resource, $identifiers);
            }
        }
    } // end: import()

    /**
     * Set value
     *
     * Use a dot (.) to set values on a deeper level
     *
     * @example Config::set('testdata.salutation', 'value=1');
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  string $key
     * @param  string $value
     */
    public function set($key, $value) {

        $nesting      = explode('.', $key);
        $buffer       = array();
        $bufferOldKey = null;

        // reverse sort
        krsort($nesting);

        foreach($nesting as $key => $nested) {

            // set value on last entry
            if($key == count($nesting) - 1) {

                $buffer[$nested] = $value;
            } else {

                $buffer[$nested] = $buffer;
            }

            if($bufferOldKey) {

                unset($buffer[$bufferOldKey]);
            }

            $bufferOldKey = $nested;
        }

        $this->config = array_replace_recursive($this->config, $buffer);
    }

    public function all() {

        return new $this($this->config);
    }

    /**
     * Getter for settings
     * Levels are linked with "."
     *
     * Example to get value:
     * array(level1 => array(level2 => value))
     * $attr = level1.level2
     *
     * @param  string $attr
     * @param  string $default
     * @param  string $mode
     * @return string $value
     */
    public function get($attr, $default = null, $mode = self::MODE_NORMAL) {

        /*
         * If $attr is directly available return it
         */
        if(isset($this->config[$attr])) :

            if(!is_array($this->config[$attr])) {

                return $this->config[$attr];
            }

            return $this->config[$attr];
        endif;

        /*
         * Seperator "." to array
         */
        $entries     = explode('.', $attr);
        $valueBuffer = $this->config;

        foreach($entries as $entry) {

            if(!isset($valueBuffer[$entry])) {

                $valueBuffer = null;
                break;
            }

            $valueBuffer = $valueBuffer[$entry];
        }

        $value  = $valueBuffer;
        $return = $this->typeCheck($value);

        switch($mode) {

            case self::MODE_NORMAL:

                break;

            case self::MODE_RANDOM:

                $return = $return[array_rand($return)];
                break;
        }

        if(is_string($return)) {

            return $return;
        }

        return ($return !== null) ? $return : $default;
    } // end: get()

    public function __toString() {

        return (string) $this->config;
    }

    public function __toArray() {

        $return = $this->config;

        if(!is_array($return)) {

            $return = array($return);
        }

        return $return;
    }

    public function clear($only = null, $exclude = null) {

        if(!$exclude) {

            $this->config = array();
        }

        if(isset($this->config[$exclude])) {

            $this->config = array($exclude => $this->config[$exclude]);
        }
    } // end: clear()

    /**
     * Type check
     * Check types of entries and convert them
     *
     * @author Florian Preusner <florian.preusner@dmc.de>
     * @param  mixed $check
     * @return mixed $check
     */
    public function typeCheck($check) {

        $isArray = true;
        $uniq    = uniqid();

        if(!is_array($check)) {

            $isArray = false;
            $check   = array($uniq => $check);
        }

        foreach($check as $key => $value) {

            if(is_numeric($value)) :

                $casted     = (int)    $value;
                $castedBack = (string) $casted;

                if($castedBack === $value) {

                    $check[$key] = $casted;
                }
            endif;
        }

        if(!$isArray) {

            return $check[$uniq];
        }

        return $check;
    } // end: typeCheck()
} // end: Config