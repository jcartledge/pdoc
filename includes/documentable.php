<?php

/**
 * @file
 * documentable superclasses
 */

abstract class Documentable {
    var $type;
    var $name;
    var $flags;
    var $doc_comment;
    function __toString() {
        return sprintf("%s%s %s %s", 
            $this->flags ? implode(' ', array_keys($this->flags)) . ' ' : '',
            $this->type, 
            $this->_truncate($this->description()), 
            ($this->line ? "at line {$this->line} " : '') . 
            ($this->file ? "of {$this->file}" : ''));
    }
    function detail_view() {
        $out .= $this;
        if($this->doc_comment) $out .= "\r\n" . $this->doc_comment;
        if($this->details) foreach((array)$this->details as $name) if($this->$name) {
            if($this->$name) {
                $out .= "\r\n";
                if(is_array($this->$name)) {
                    $out .= "\t" . implode("\r\n\t", (array)$this->$name);
                } else {
                    $out .= sprintf("\t%s: %s\r\n",
                        str_replace('_', ' ', $name),
                        $this->$name);
                }
            }
        }
        return $out;
    }
    function match($search) {
        return fnmatch("*{$search}*", $this->name);
    }
    function description() {
        return $this->name;
    }
    /**
     * do anything programmatic here
     * e.g. line count
     */
    function post_process() {
        $this->_format_doc_comment();
    }
    private function _format_doc_comment() {
        if(!$this->doc_comment) return;
        $this->doc_comment = explode("\n", $this->doc_comment);
        foreach($this->doc_comment as $num => $line) {
            $this->doc_comment[$num] = "\t" . ($num ? ' ' : '') . ltrim($line);
        }
        $this->doc_comment = implode("\n", $this->doc_comment);
    }
    private function _truncate($string) {
        $string = implode(' ', array_map(trim, explode("\n", $string)));
        if(strlen($string) < 80) return $string;
        return substr($string, 0, 55) . ' ... ' . substr($string, -20);
    }
}