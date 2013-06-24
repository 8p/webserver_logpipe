<?php

namespace LogfileBundle\Parser;

class ApacheAccesslog implements iParser {

    private $hits_unmatched = 0;
    private $hits_total = 0;
    private $hits_static = 0;

    private $hits_time_total = 0;
    private $hits_time_static = 0;

    private $line_regex = '/.*\[(.+)] "(HEAD|GET|POST|PUT|PROPFIND|OPTIONS|DELETE) (?P<url>.+?(\?.*)?) HTTP.+" (?P<code>\d\d\d).* (?P<exectime>\d+)?$/';
    private $line_regex_static = '/.*\.(gif|jpg|jpeg|png|ico|js|css|txt|flv|swf)$/';

    public function __construct(){

    }

    public function parseLine($line){

       if(preg_match($this->line_regex, $line, $matched) != 1){
         print "FAILED to PARSE LINE: $line";
         $this->hits_unmatched++;
         return;
       }

       $this->hits_total++; 

       if (array_key_exists($matched["exectime"])){
         $this->hits_time_total = $this->hits_time_total + $matched["exectime"]; 
       }

       //print_r($matched);

       if (preg_match($this->line_regex_static,$matched["url"]) == 1){
         $this->hits_static++;
         if (array_key_exists($matched["exectime"])){
            $this->hits_time_static = $this->hits_time_static + $matched["exectime"];
         }
       }
    }


    public function getResults(){
      return array(
         "apache.time.total" => $this->hits_time_total,
         "apache.time.static" => $this->hits_time_static,
         "apache.time.dynamic" => ($this->hits_time_total - $this->hits_time_static),
         "apache.hits.unmatched" => $this->hits_unmatched,
         "apache.hits.total" => $this->hits_total,
         "apache.hits.static" => $this->hits_static,
         "apache.hits.dynamic" => ($this->hits_total - $this->hits_static)
      );
    }
}
