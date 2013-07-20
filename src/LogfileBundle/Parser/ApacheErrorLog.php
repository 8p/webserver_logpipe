<?php

namespace LogfileBundle\Parser;

class ApacheAccesslog implements iParser {

    private $hits_unmatched = 0;
    private $hits_total = 0;


    public function __construct(){

    }

    public function parseLine($line){

#       if(preg_match($this->line_regex, $line, $matched) != 1){
#         print "FAILED to PARSE LINE: $line";
#         $this->hits_unmatched++;
#         return;
#       }
#
       $this->hits_total++; 
    }


    public function getResults(){
      return array(
      );
    }
}
