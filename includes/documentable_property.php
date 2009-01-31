<?php

class DocumentableProperty extends Documentable {
    var $type = 'property';
    var $line;
    var $file;
    var $classname;
    var $default_value;
    var $details = 'default_value';
    function description() {
        return sprintf('%s::%s%s',
            $this->classname,
            $this->name,
            $this->default_value ? " = {$this->default_value}" : '');
    }
    function post_process() {
        $this->default_value = ltrim($this->default_value);
        parent::post_process();
    }
}
