<?php

class Token {
    public $type;
    public $value;
    public $line;
    function __toString() {
        return $this->value;
    }
    function __construct($token) {
        if(is_array($token)) {
            list($this->type, $this->value, $this->line) = $token;
        } else {
            $this->value = (string)$token;
        }
    }
}