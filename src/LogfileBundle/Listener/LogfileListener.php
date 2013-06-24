<?php

namespace LogfileBundle\Listener;

class LogfileListener extends \MainBundle\Listener\BasicListener {

    private $format;
    private $logfile_name_current = NULL;
    private $logfile_handle = NULL;
    private $symlink = NULL;

    private $notifiers = array();


    private $precicseRotation = FALSE;

    public function __construct() {

        $config = $this->get('config');

        // start timestamp for today
        $this->format  = $config->get('logfile.file');
        $this->symlink = $config->get('logfile.symlink');

        $this->last_monitor_flush = time();

        # Initial open
        $this->maintainLogfile();
    }

    public function input(\Symfony\Component\EventDispatcher\Event $event) {

        $input = $event->getInput();

        fwrite($this->logfile_handle, $input);

        $parser = $this->get('logfile.parser');
        $parser->parseLine($input);

        if('precise' == $this->get('config')->get('logfile.rotation')){
die('YEAH!');
            $this->maintainLogfile();
        }
    }

    /*public function addMonitorNotifier($monitor){

        array_push($this->notifiers,$monitor);
    }*/

    public function setPrecicseRotation($setprecicserotation){

        $this->precicseRotation = $setprecicserotation;
    }

    private function maintainSymlink($logfile_name){

        if($this->symlink === NULL){

            return;
        }

        if(file_exists($this->symlink) && is_link($this->symlink)){

            unlink($this->symlink);
        }

        symlink($logfile_name, $this->symlink);
    }

    private function maintainLogfile(){

        $tmp_logfilename = strftime($this->format);

        if($this->logfile_handle === NULL) {

            $this->get('logger')->add(sprintf('OPEN %s', $tmp_logfilename));

            $this->logfile_name_current = $tmp_logfilename;
            $this->logfile_handle = fopen($tmp_logfilename, 'a');

            $this->maintainSymlink($tmp_logfilename);
        } else if ($this->logfile_name_current != $tmp_logfilename) {

            $this->get('logger')->add(sprintf('CHANGE %s => %s', $this->logfile_name_current, $tmp_logfilename));

            # Close old filehandle
            fclose($this->logfile_handle);
            # Open new filehandle
            $this->logfile_name_current = $tmp_logfilename;
            $this->logfile_handle = fopen($tmp_logfilename, 'a');

            $this->maintainSymlink($tmp_logfilename);
        }

        return $this->logfile_handle;
    }
}