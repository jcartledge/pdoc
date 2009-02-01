<?php

include 'options.php';

class CliScript {
    function __construct() {
        $args = $_SERVER['argv'];
        array_shift($args);
        $description = array_merge(array('#help' => 'Display this message'), $this->description);
        $options = new Options($description, $args);
        if($options->help) {
            $this->help();
        } else {
            $this->main($options);
        }
    }
    function help() {
        echo "FUCK OFF";
    }
}
