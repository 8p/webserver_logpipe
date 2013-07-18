<?php

namespace LogfileBundle\Parser;

interface iParser {

    public function __construct();

    public function parseLine($line);

    public function getResults();
} // end: iParser