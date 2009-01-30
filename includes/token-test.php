<?php
require_once('Pearified/Testing/SimpleTest/unit_tester.php');
require_once('Pearified/Testing/SimpleTest/reporter.php');
require_once('tokens.php');

class TestOfTokens extends UnitTestCase {
    var $test_docs = array(
        'simple class with properties' =>
        '<?php
            class A {
                public $a;
                protected $b;
                private $c;
                static $d;
                var $e;
            }',
        'includes' =>
        '<?php
            include "a.php";
            include_once("b.php");
            require "c.php";
            require_once "d.php";',
    );
    function testOfProperties() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue(count($documentables[0]->classes[0]->properties) == 5);
    }
    function testOfPublicProperty() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue($documentables[0]->classes[0]->properties[0]->flags['public']);
    }
    function testOfProtectedProperty() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue($documentables[0]->classes[0]->properties[1]->flags['protected']);
    }
    function testOfPrivateProperty() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue($documentables[0]->classes[0]->properties[2]->flags['private']);
    }
    function testOfStaticProperty() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue($documentables[0]->classes[0]->properties[3]->flags['static']);
    }
    function testOfPropertyName() {
        $documentables = $this->parse_tokens('simple class with properties');
        $this->assertTrue(end($documentables[0]->classes[0]->properties)->name == '$e');
    }
    function testOfIncludes() {
        $documentables = $this->parse_tokens('includes');
        $this->assertTrue(count($documentables[0]->includes) == 4);
    }
    function testOfSimpleInclude() {
        $documentables = $this->parse_tokens('includes');
        $this->assertTrue($documentables[0]->includes[0]->name == '"a.php"');
    }
    function parse_tokens($file) {
        return parse_tokens($this->test_docs[$file], $file);
    }
}

$test = new TestOfTokens();
$test->run(new TextReporter());
?>