<?php

namespace Dallgoot\Yaml\tests;

class Parsing_tests extends BaseTest
{
    public function __construct()
    {
        parent::init(__DIR__.'./definitions/parsing_tests.yml');
    }

    public function test_Loadertest1()
    {
        $this->test_expectParsing("loadertest1.yml", $this->source->{"loadertest1"});
    }

    public function test_Loadertest2()
    {
        $this->test_expectParsing("loadertest2.yml", $this->source->{"loadertest2"});
    }

    public function test_Loadertest13()
    {
        $this->test_expectParsing("loadertest13.yml", $this->source->{"loadertest13"});
    }

}