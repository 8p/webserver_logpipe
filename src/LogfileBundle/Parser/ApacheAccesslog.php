<?php

namespace LogfileBundle\Parser;

class ApacheAccesslog implements iParser {

    private $hitsUnmatched  = 0;
    private $hitsTotal      = 0;
    private $hitsStatic     = 0;
    private $hitsTimeTotal  = 0;
    private $hitsTimeStatic = 0;

    private $lineRegEx       = '/.*\[(.+)] "(HEAD|GET|POST|PUT|PROPFIND|OPTIONS|DELETE) (?P<url>.+?(\?.*)?) HTTP.+" (?P<code>\d\d\d).* (?P<exectime>\d+)?$/';
    private $lineRegExStatic = '/.*\.(gif|jpg|jpeg|png|ico|js|css|txt|flv|swf)$/';

    public function __construct() {

    }

    public function parseLine($line) {

        if(preg_match($this->lineRegEx, $line, $matched) != 1) :

            print "FAILED to PARSE LINE: $line";

            $this->hitsUnmatched++;
            return;
        endif;

        $this->hitsTotal++;

        if(isset($matched["exectime"])) :

            $this->hitsTimeTotal += $matched["exectime"];
        endif;

        //print_r($matched);

        if(preg_match($this->lineRegExStatic,$matched["url"]) == 1) :

            $this->hitsStatic++;

            if(isset($matched["exectime"])) :

                $this->hitsTimeStatic += $matched["exectime"];
            endif;
        endif;
    } // end: parseLine()

    public function getResults(){

        return array(
            'apache.time.total'     => $this->hitsTimeTotal,
            'apache.time.static'    => $this->hitsTimeStatic,
            'apache.time.dynamic'   => ($this->hitsTimeTotal - $this->hitsTimeStatic),
            'apache.hits.unmatched' => $this->hitsUnmatched,
            'apache.hits.total'     => $this->hitsTotal,
            'apache.hits.static'    => $this->hitsStatic,
            'apache.hits.dynamic'   => ($this->hitsTotal - $this->hitsStatic)
        );
    } // end: getResults()
}
