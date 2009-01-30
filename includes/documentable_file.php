<?php

class DocumentableFile extends Documentable {
    var $type = 'file';
    var $includes;
    var $classes;
    var $functions;
    var $details = array('classes', 'functions');
}
