#!/usr/bin/php
<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
require_once('../includes/options.php');
require_once('options-tests.php');

class TestOfShortOptionSyntax extends TestOfOptions {
    var $options;       //SUT
    var $description = array(
        'paths=.                 Paths to search (colon separated)',
        'match=*.php             Filename pattern to match',
        '#recursive              Search in subdirectories',
        '#follow-includes        Search in included and required files'
    );
    var $parse_tests = array(
        'short option with no whitespace' => array(
            '-pasdfg',
            'paths', 
            'asdfg'),
        'short option with whitespace' => array(
            array('-p', 'asdfg'),
            'paths', 
            'asdfg'),
        'short option with equals' => array(
            '-p=asdfg',
            'paths', 
            'asdfg'),
        'short option with equals and whitespace 1' => array(
            array('-p', '=asdfg'),
            'paths', 
            'asdfg'),
        'short option with equals and whitespace 2' => array(
            array('-p=', 'asdfg'),
            'paths', 
            'asdfg'),
        'short option with equals and whitespace 3' => array(
            array('-p', '=', 'asdfg'),
            'paths', 
            'asdfg'),
        'short option no whitespace with one arg testing option' => array(
            array('-pasdfg', 'arg 1'),
            'paths', 
            'asdfg'),
        'short option no whitespace with one arg testing arg' => array(
            array('-pasdfg', 'arg 1'),
            'args[0]', 
            'arg 1'),
        'short option whitespace with one arg testing option' => array(
            array('-p', 'asdfg', 'arg 1'),
            'paths', 
            'asdfg'),
        'short option whitespace with one arg testing arg' => array(
            array('-p', 'asdfg', 'arg 1'),
            'args[0]', 
            'arg 1'),
        'short option equals with one arg testing option' => array(
            array('-p', '=asdfg', 'arg 1'),
            'paths', 
            'asdfg'),
        'short option equals with one arg testing arg' => array(
            array('-p', '=', 'asdfg', 'arg 1'),
            'args[0]', 
            'arg 1'),
    );
}
if(__FILE__ == realpath($_SERVER['SCRIPT_NAME'])) {
    $test = new TestOfShortOptionSyntax;
    $test->run(new TextReporter);
}
?>