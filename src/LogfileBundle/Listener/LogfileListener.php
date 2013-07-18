<?php

namespace LogfileBundle\Listener;

class LogfileListener extends \MainBundle\Listener\BasicListener {

    private $format;
    private $logfileNameCurrent = null;
    private $logfileHandle      = null;
    private $symlink            = null;
    private $notifiers          = array();
    private $precicseRotation   = false;

    public function __construct() {

        $config = $this->get('config');

        // start timestamp for today
        $this->format  = $config->get('logfile.file');
        $this->symlink = $config->get('logfile.symlink');

        $this->last_monitor_flush = time();

        # Initial open
        $this->maintainLogfile();
    } // end: __construct()

    public function input(\Symfony\Component\EventDispatcher\Event $event) {

        $input = $event->getInput();

        fwrite($this->logfileHandle, $input);

        $parser = $this->get('logfile.parser');
        $parser->parseLine($input);

        if('precise' == $this->get('config')->get('logfile.rotation')) :

            $this->maintainLogfile();
        endif;
    } // end: input()

    /*public function addMonitorNotifier($monitor){

        array_push($this->notifiers,$monitor);
    }*/

    /**
     * Set precise rotation
     *
     * @param  boolean $value
     * @return void
     */
    public function setPrecicseRotation($value){

        $this->precicseRotation = $value;
    } // end: setPrecicseRotation()

    private function maintainSymlink($logfile_name){

        if($this->symlink === NULL) :

            return;
        endif;

        if(file_exists($this->symlink) && is_link($this->symlink)) :

            unlink($this->symlink);
        endif;

        symlink($logfile_name, $this->symlink);
    } // end: maintainSymlink()

    private function maintainLogfile(){

        $tmpLogfileName = strftime($this->format);

        if($this->logfileHandle && $this->logfileNameCurrent != $tmpLogfileName) :

            $this->get('logger')->add(sprintf('CHANGE %s => %s', $this->logfileNameCurrent, $tmpLogfileName));

            # Close old filehandle
            fclose($this->logfileHandle);
        endif;

        $this->get('logger')->add(sprintf('OPEN %s', $tmpLogfileName));

        # Open new filehandle
        $this->logfileNameCurrent = $tmpLogfileName;
        $this->logfileHandle      = fopen($tmpLogfileName, 'a');

        $this->maintainSymlink($tmpLogfileName);

        return $this->logfileHandle;
    } // end: maintainLogfile()
} // end: LogfileListener