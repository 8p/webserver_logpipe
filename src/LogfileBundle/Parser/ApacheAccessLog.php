<?php

namespace LogfileBundle\Parser;

class ApacheAccessLog implements iParser {

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

            $this->hitsUnmatched++;

            return;
        endif;

        $this->hitsTotal++;

        if(isset($matched["exectime"])) :

            $this->hitsTimeTotal += $matched["exectime"];
        endif;

        if(preg_match($this->lineRegExStatic, $matched["url"]) == 1) :

            $this->hitsStatic++;

            if(isset($matched["exectime"])) :

                $this->hitsTimeStatic += $matched["exectime"];
            endif;
        endif;
    } // end: parseLine()

    public function getHitsTimeTotal() {

        return $this->hitsTimeTotal;
    } // end: getHitsTimeTotal()

    public function getHitsTimeStatic() {

        return $this->hitsTimeStatic;
    } // end: getHitsTimeStatic()

    public function getHitsUnmatched() {

        return $this->hitsUnmatched;
    } // end: getHitsUnmatched()

    public function getHitsTotal() {

        return $this->hitsTotal;
    } // end: getHitsTotal()

    public function getHitsStatic() {

        return $this->hitsStatic;
    } // end: getHitsStatic()

    public function getResults() {

        return array(
            'apache.time.total'     => $this->getHitsTimeTotal(),
            'apache.time.static'    => $this->getHitsTimeStatic(),
            'apache.time.dynamic'   => ($this->getHitsTimeTotal() - $this->getHitsTimeStatic()),
            'apache.hits.unmatched' => $this->getHitsUnmatched(),
            'apache.hits.total'     => $this->getHitsTotal(),
            'apache.hits.static'    => $this->getHitsStatic(),
            'apache.hits.dynamic'   => ($this->getHitsTotal() - $this->getHitsStatic())
        );
    } // end: getResults()
} // end: ApacheAccessLog
