<?php

namespace LogfileBundle\Parser;

interface ParserInterface {

    public function __construct();

    public function parseLine($line);

    public function getResults();
} // end: ParserInterface
