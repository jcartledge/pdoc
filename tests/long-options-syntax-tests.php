#!/usr/bin/php
<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
require_once('../includes/options.php');
require_once('options-tests.php');

class TestOfLongOptionSyntax extends TestOfOptions {
    var $options;       //SUT
    var $description = array(
        'paths=.                 Paths to search (colon separated)',
        'match=*.php             Filename pattern to match',
        '#recursive              Search in subdirectories',
        '#follow_includes        Search in included and required files'
    );
    var $parse_tests = array(
        'long option with no whitespace' => array(
            '--paths=asdfg',
            'paths', 
            'asdfg'),
        'long option with whitespace no equals' => array(
            array('--paths', 'asdfg'),
            'paths', 
            'asdfg'),
        'long option with whitespace equals 1' => array(
            array('--paths=', 'asdfg'),
            'paths', 
            'asdfg'),
        'long option with whitespace equals 2' => array(
            array('--paths', '=asdfg'),
            'paths', 
            'asdfg'),
        'long option with whitespace equals 3' => array(
            array('--paths', '=', 'asdfg'),
            'paths', 
            'asdfg'),
    );
}
if(__FILE__ == realpath($_SERVER['SCRIPT_NAME'])) {
    $test = new TestOfLongOptionSyntax;
    $test->run(new TextReporter);
}
?>