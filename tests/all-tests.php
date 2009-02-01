#!/usr/bin/php
<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
ob_start();
$test = &new GroupTest('All tests');
$test->addTestFile('documentable-parse-tests.php');
$test->addTestFile('options-tests.php');
$test->addTestFile('options-tests-with-defaults.php');
$test->addTestFile('long-options-syntax-tests.php');
$test->addTestFile('short-options-syntax-tests.php');
ob_end_clean();
$test->run(new TextReporter);
