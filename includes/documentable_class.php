<?php

class DocumentableClass extends Documentable {
    var $type = 'class';
    var $line;
    var $file;
    var $extends;
    var $properties;
    var $methods;
    var $details = array('extends', 'properties', 'methods');
}
