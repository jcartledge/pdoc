<?php

require 'options.php';

class CliScript {
    function __construct() {
        $args = $_SERVER['argv'];
        array_shift($args);
        $description = array_merge(array('#help' => 'Display this message'), $this->description);
        $options = new Options($description, $args);
        $this->main($options);
    }
    function main($options) {
        if ($options->help) $this->help($options);
    }
    function help($options) {
        foreach($options->opts as $long_name => $option) {
            echo sprintf("  -%s, --%s%s\r\t\t\t\t%s\r\n",
                $option['short'],
                $long_name,
                $option['flag'] ? "\t" : '=' . strtoupper($long_name),
                $option['description']);
        };
        exit;
    }
}