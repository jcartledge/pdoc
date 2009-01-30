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
    private function parse_tokens($doc_name) {
        $doc = $this->test_docs[$doc_name];
        $filename = '/tmp/' . md5($doc);
        file_put_contents($filename, $doc);
        return parse_tokens($filename);
    }
}

$test = new TestOfTokens();
$test->run(new TextReporter());
?>