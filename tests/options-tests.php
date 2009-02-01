#!/usr/bin/php
<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
require_once('../includes/options.php');

class TestOfOptions extends UnitTestCase {
    var $options;       //SUT
    var $description = array(
        'in/infile   File to read from',
        'outfile     File to write to',
        '#quiet      Suppress output',
        '#verbose    Output debug info');
    var $parse_tests = array(
        'single arg' => array(
            'first arg',
            'args[0]', 
            'first arg'),
        'two args testing first arg' => array(
            array('first arg', 'second arg'),
            'args[0]', 
            'first arg'),
        'two args testing second arg' => array(
            array('first arg', 'second arg'),
            'args[1]', 
            'second arg'),
        'short flag' => array(
            '-q',
            'quiet'),
        'short flag and one arg testing flag' => array(
            array('-q', 'first arg'),
            'quiet'),
        'short flag and one arg testing arg' => array(
            array('-q', 'first arg'),
            'args[0]', 
            'first arg'),
        'two short flags testing first flag' => array(
            '-qv',
            'quiet'),
        'two short flags testing second flag' => array(
            '-qv',
            'verbose'),
        'long flag' => array(
            '--verbose',
            'verbose'),
        'two long flags testing first flag' => array(
            array('--quiet', '--verbose'),
            'quiet'),
        'two long flags testing second flag' => array(
            array('--quiet', '--verbose'),
            'verbose'),
        'short option' => array(
            '-inmyinfile.txt',
            'infile', 
            'myinfile.txt'),
        'short option expanded' => array(
            array('-in', 'myinfile.txt'),     
            'infile', 
            'myinfile.txt'),
        'long option' => array(
            '--infile=myinfile.txt',
            'infile', 
            'myinfile.txt'),
        'long option expanded' => array(
            array('--infile', 'myinfile.txt'),  
            'infile', 
            'myinfile.txt'),
        'short flag and long option testing flag' => array(
            array('-q', '--infile=myinfile.txt'), 
            'quiet'),
        'short flag and long option testing option' => array(
            array('-q', '--infile=myinfile.txt'), 
            'infile', 
            'myinfile.txt'),
        'short flag and long option expanded testing flag' => array(
            array('-q', '--infile', 'myinfile.txt'), 
            'quiet'),
        'short flag and long option expanded testing option' => array(
            array('-q', '--infile', 'myinfile.txt'), 
            'infile', 
            'myinfile.txt'),
        'short flag and short option testing flag' => array(
            '-qooutfile.txt',
            'quiet'),
        'short flag and short option testing option' => array(
            '-qooutfile.txt',
            'outfile',
            'outfile.txt'),
        'short flag and short option expanded testing flag' => array(
            array('-qo', 'outfile.txt'),
            'quiet'),
        'short flag and short option expanded testing option' => array(
            array('-qo', 'outfile.txt'),
            'outfile',
            'outfile.txt'),
    );
    function setup() {
        $this->options = new Options($this->description);
    }

    function testOfParse() {
        array_map(array($this, '_run_parse_test'), $this->parse_tests);
    }

    private function _run_parse_test($test) {
        $this->setup();
        list($arg, $full_property_name, $value) = $test;
        if(is_null($value)) $value = true;
        $this->options->parse((array)$arg);
        preg_match('/^([^\[]*)([\[]?([^\]]*)\]*)/', $full_property_name, $matches);
        list(,$property_name,, $index) = $matches;
        $property = is_numeric($index) ?
            $this->options->{$property_name}[$index] :
            $this->options->{$property_name};
        $this->assertTrue(
            $property == $value,
            $this->_parse_test_error($test, $property));
        $this->teardown();
    }

    private function _parse_test_error($test, $got) {
        return sprintf("\t\t%s\r\nargs\t\t%s\r\nexpected\t%s\r\ngot\t\t%s\r\n", 
            array_search($test, $this->parse_tests),
            implode(' ', (array)$test[0]), 
            sprintf('%s=%s', $test[1], $test[2]), 
            sprintf('%s=%s', $test[1], $got));
    }
}
if(__FILE__ == realpath($_SERVER['SCRIPT_NAME'])) {
    $test = new TestOfOptions;
    $test->run(new TextReporter);
}
?>