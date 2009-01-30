<?php

class DocumentableMethod extends DocumentableFunction {
    var $type = 'method';
    var $classname;
    function description() {
        return sprintf('%s::%s',
            $this->classname,
            parent::description());
    }
}
