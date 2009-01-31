#!/usr/bin/php
<?php

/**
 * @file
 * dynamic searchable php source docs
 */

require 'includes/documentables.php';
require '../argv/argv.php';

class CliScript {
    function __construct() {
        $args = $_SERVER['argv'];
        array_shift($args);
        $options = new Options($this->description, $args);
        $this->main($options);
        echo "{$this}\r\n";
    }
}

class DocSearch extends CliScript{
    public $search;
    public $documentables = array();
    private $_parsed_files = array();
    protected $description = array(
        'paths=.                 Paths to search (colon separated)',
        'match=*.php             Filename pattern to match',
        '#recursive              Search in subdirectories',
        '#i/follow_includes      Search in included and required files'
    );

    function main($options) {
        $this->search(
            $options->args[0], 
            $options->paths, 
            $options->recursive, 
            $options->match, 
            $options->follow_includes);
    }
    /**
     * @todo support filtering results on type
     */
    function search($search, $paths = '.', $recursive = false, $match = '*.php', $follow_includes = false) {
        $this->search = $search;
        $paths = explode(':', $paths);          //not windows :(
        foreach ($paths as $path) {
            if(is_file($path)) {
                $this->do_file($path, $follow_includes);
            } else {
                $this->do_path($path, $recursive, $match, $follow_includes);
            }
        }
    }
    function __toString() {
        $out = array();
        foreach($this->documentables as $documentable) {
            if($documentable->match($this->search)) {
                $out[] = $documentable;
            }
        }
        if(method_exists($out[0], 'detail_view')) {
            $out[0] = $out[0]->detail_view();
        }
        return implode("\r\n", array_filter(array_map(trim, $out)));
    }

    private function do_path($path, $recursive, $match, $follow_includes) {
        $files = scandir($path);                //not testable
        foreach ($files as $file) {
            if($file == '.' || $file == '..') continue;
            $file = realpath($path . DIRECTORY_SEPARATOR . $file);
            if(is_dir($file)) {                 //not testable
                if($recursive) {                //not testable
                    $this->do_path($file, $recursive, $match, $follow_includes);
                }
            } elseif(fnmatch("*{$match}*", $file)) {
                $this->do_file($file, $follow_includes);
            }
        }
    }

    private function do_file($filename, $follow_includes) {
        if($this->_parsed_files[$filename]) return;
        $parsed = (array)$this->parse_file($filename);
        $includes = $parsed[0]->includes;
        $this->documentables = array_merge($this->documentables, $parsed);      //filter here
        if ($follow_includes && $includes) {
            $paths = $this->_localised_include_path($filename);
            foreach($includes as $include) {
                foreach ($paths as $path) {
                    $include_name = $path . preg_replace('/["\(\)\'\s]*/', '', $include->name);
                    if(file_exists($include_name)) {    //not testable
                        $this->do_file($include_name, $follow_includes);
                        continue(2);
                    }
                }
            }
        }
        $this->_parsed_files[$filename] = true;
    }

    private function _localised_include_path($filename) {
        $paths = explode(':', ini_get('include_path'));
        $filepath = dirname($filename) . DIRECTORY_SEPARATOR;
        foreach($paths as $i => $path) {
            $paths[$i] = rtrim(strpos($path, '/') === 0 ?
                $path :
                realpath($filepath . $path), '/') . '/';
        }
        return $paths;
    }
    private function parse_file($file) {
        return Documentable::from_file($file);
    }
}
new DocSearch;