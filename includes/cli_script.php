<?php

include 'options.php';

class CliScript {
    function __construct() {
        $args = $_SERVER['argv'];
        array_shift($args);
        $options = new Options($this->description, $args);
        $this->main($options);
        echo "{$this}\r\n";
    }
}
