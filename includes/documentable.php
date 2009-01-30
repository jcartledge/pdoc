<?php

/**
 * @file
 * documentable superclasses
 */

include 'token.php';

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

    static function from_file($filename) {
        return self::from_string(file_get_contents($filename), $filename);
    }
    static function from_string($data, $name) {
        $tokens = token_get_all($data);
        $realpath = str_replace($_ENV['PWD'] . DIRECTORY_SEPARATOR, '', realpath($name));
        $this_file = new DocumentableFile;
        $this_file->name = $realpath;
        $documentables = array($this_file);
        foreach ($tokens as $token) {
            $token = new Token($token);
            if ($token->type == T_OPEN_TAG) {
                $in_php = true;
            } 
            elseif ($token->type == T_CLOSE_TAG) {
                $in_php = false;
            }

            elseif ($in_php && !$in_function && $token->type == T_DOC_COMMENT) {
                if(!$this_file->doc_comment) {
                    $this_file->doc_comment = $token->value;
                } else {
                    $doc_comment = $token->value;
                }
            }

            elseif ($in_php && array_search($token->type, array(T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE)) !== false) {
                $include = new DocumentableInclude;
                $include->type = strtolower(str_replace('T_', '', token_name($token->type)));
                $include->file = $realpath;
                $include->line = $token->line;
                $this_file->includes[] = $include;
                $documentables[] = $include;
            }
            elseif ($include && $token == ';') {
                $include = null;
            }
            elseif ($in_php && $token->type == T_ABSTRACT) {
                $abstract = true;
            }
            elseif ($in_class && $token->type == T_STATIC) {
                $static = true;
            }
            elseif ($in_class && $token->type == T_PUBLIC) {
                $public = true;
            }
            elseif ($in_class && $token->type == T_PROTECTED) {
                $protected = true;
            }
            elseif ($in_class && $token->type == T_PRIVATE) {
                $private = true;
            }

            elseif ($in_php && $token->type == T_FUNCTION) {
                if($in_class) {
                    $function = new DocumentableMethod;
                    $function->classname = $class->name;
                    $class->methods[] = $function;
                } else {
                    $function = new DocumentableFunction;
                    $this_file->functions[] = $function;
                }
                if($doc_comment) {
                    $function->doc_comment = $doc_comment;
                    $doc_comment = null;
                }
                $function->file = $realpath;
                $function->line = $token->line;
                $in_function_header = true;
                foreach(explode(',','public,private,protected,static,abstract') as $note) {
                    if($$note) {
                        $function->flags[$note] = $note;
                        $$note = false;
                    }
                }
            }
            elseif ($in_function_header && $token == '(') {
                $in_function_params = true;
            } 
            elseif ($in_function_params && $token == ')') {
                $in_function_params = false;
            } 
            elseif ($in_function_header && $token == '{') {
                $in_function_header = false;
                $in_function = true;
                $blocks = 0;
            }
            elseif ($in_function && ($token == '{' || $token->type == T_CURLY_OPEN)) {
                $blocks++;
                //$function->source .= '{';
            } 
            elseif ($in_function && $token == '}') {
                if(--$blocks < 0) {
                    $in_function = false;
                    $documentables[] = $function;
                    $function->post_process();
                    $function = null;
                } else {
                    $function->source .= '}';
                }
            }

            elseif ($in_php && $token->type == T_CLASS) {
                $class = new DocumentableClass;
                $class->file = $realpath;
                $class->line = $token->line;
                if($doc_comment) {
                    $class->doc_comment = $doc_comment;
                    $doc_comment = null;
                }
                $in_class_header = true;
                if($abstract) {
                    $class->flags['abstract'] = 'abstract';
                    $abstract = false;
                }
            }
            elseif ($in_class_header && $token->type == T_EXTENDS) {
                $in_class_extends = true;
            } 
            elseif ($in_class_header && $token == '{') {
                $in_class_header = false;
                $in_class_extends = false;
                $in_class = true;
            } 
            elseif ($in_class && $token == '}') {
                $in_class = false;
                $this_file->classes[] = $class;
                $documentables[] = $class;
                $class->post_process();
                $class = null;
            } 

            elseif (($in_class && $token->type == T_VAR)
            || (($public || $private || $protected || $static) && $token->type == T_VARIABLE)) {
                $property = new DocumentableProperty;
                $property->file = $realpath;
                $property->line = $token->line;
                $property->classname = $class->name;
                if($doc_comment) {
                    $property->doc_comment = $doc_comment;
                    $doc_comment = null;
                }
                $in_property = true;
                foreach(explode(',','public,private,protected,static') as $note) {
                    if($$note) {
                        $property->flags[$note] = $note;
                        $$note = false;
                    }
                }
                if($token->type == T_VARIABLE) {
                    $property->name = $token->value;
                }
            } 
            //default value?
            elseif ($in_property && $token == '=') {
                $in_property_default = true;
            }
            elseif ($in_property && $token == ';') {
                $in_property = false;
                $in_property_default = false;
                $property->name = trim($property->name);
                $class->properties[] = $property;
                $documentables[] = $property;
                $property->post_process();
                $property = null;
            } 

            elseif ($in_property_default) {
                $property->default_value .= is_array($token) ? $token->value : $token; 
            } 
            elseif ($in_property) {
                $property->name .= is_array($token) ? $token->value : $token; 
            } 
            elseif ($in_class_extends ) {
                $class->extends .= trim(is_array($token) ? $token->value : $token); 
            } 
            elseif ($in_class_header ) {
                $class->name .= trim(is_array($token) ? $token->value : $token); 
            } 
            elseif ($in_function_params) {
                $function->params .= is_array($token) ? $token->value : $token; 
            } 
            elseif ($in_function_header) {
                $function->name .= trim(is_array($token) ? $token->value : $token); 
            } 
            elseif ($in_function) {
                //$function->source .= is_array($token) ? $token->value : $token; 
            }
            elseif ($include) {
                $include->name .= trim(is_array($token) ? $token->value : $token); 
            } 
        }
        $this_file->post_process();
        return $documentables;
    }
}