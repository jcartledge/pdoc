#!/usr/bin/php
<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
require_once('../includes/options.php');
require_once('options-tests.php');

class TestOfOptionsWithDefaults extends TestOfOptions {
    var $options;       //SUT
    var $description = array(
        'paths=.                 Paths to search (colon separated)',
        'match=*.php             Filename pattern to match',
        '#recursive              Search in subdirectories',
        '#follow-includes        Search in included and required files'
    );
    var $parse_tests = array(
        'no args testing default paths' => array(
            '',
            'paths', 
            '.'),
        'no args testing default match' => array(
            '',
            'match', 
            '*.php'),
        'test overriding default match' => array(
            array('-m', '*.*'),
            'match', 
            '*.*'),
    );
}
if(__FILE__ == realpath($_SERVER['SCRIPT_NAME'])) {
    $test = new TestOfOptionsWithDefaults;
    $test->run(new TextReporter);
}
?>