<?php

namespace LogfileBundle\Parser;

class ApacheAccesslog implements iParser {

    private $hitsUnmatched = 0;
    private $hitsTotal = 0;


    public function __construct(){

    }

    public function parseLine($line){

       if(preg_match($this->line_regex, $line, $matched) != 1){
         print "FAILED to PARSE LINE: $line";
         $this->hitsUnmatched++;
         return;
       }

       $this->hitsTotal++; 
    }


    public function getResults(){
      return array(
      );
    }
}
