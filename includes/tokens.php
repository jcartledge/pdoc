<?php

include 'documentable.php';

function parse_tokens($file) {
    $tokens = token_get_all(file_get_contents($file));
    $realpath = str_replace($_ENV['PWD'] . DIRECTORY_SEPARATOR, '', realpath($file));
    $this_file = new DocumentableFile;
    $this_file->name = $realpath;
    $documentables = array($this_file);
    foreach ($tokens as $token) {

        if ($token[0] == T_OPEN_TAG) {
            $in_php = true;
        } 
        elseif ($token[0] == T_CLOSE_TAG) {
            $in_php = false;
        }

        elseif ($in_php && !$in_function && $token[0] == T_DOC_COMMENT) {
            if(!$this_file->doc_comment) {
                $this_file->doc_comment = $token[1];
            } else {
                $doc_comment = $token[1];
            }
        }

        elseif ($in_php && array_search($token[0], array(T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE)) !== false) {
            $include = new DocumentableInclude;
            $include->type = strtolower(str_replace('T_', '', token_name($token[0])));
            $include->file = $realpath;
            $include->line = $token[2];
            $this_file->includes[] = $include;
            $documentables[] = $include;
        }
        elseif ($include && $token == ';') {
            $include = null;
        }
        elseif ($in_php && $token[0] == T_ABSTRACT) {
            $abstract = true;
        }
        elseif ($in_class && $token[0] == T_STATIC) {
            $static = true;
        }
        elseif ($in_class && $token[0] == T_PUBLIC) {
            $public = true;
        }
        elseif ($in_class && $token[0] == T_PROTECTED) {
            $protected = true;
        }
        elseif ($in_class && $token[0] == T_PRIVATE) {
            $private = true;
        }

        elseif ($in_php && $token[0] == T_FUNCTION) {
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
            $function->line = $token[2];
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
        elseif ($in_function && ($token == '{' || $token[0] == T_CURLY_OPEN)) {
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

        elseif ($in_php && $token[0] == T_CLASS) {
            $class = new DocumentableClass;
            $class->file = $realpath;
            $class->line = $token[2];
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
        elseif ($in_class_header && $token[0] == T_EXTENDS) {
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

        elseif (($in_class && $token[0] == T_VAR)
        || (($public || $private || $protected || $static) && $token[0] == T_VARIABLE)) {
            $property = new DocumentableProperty;
            $property->file = $realpath;
            $property->line = $token[2];
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
            if($token[0] == T_VARIABLE) {
                $property->name = $token[1];
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
            $property->default_value .= is_array($token) ? $token[1] : $token; 
        } 
        elseif ($in_property) {
            $property->name .= is_array($token) ? $token[1] : $token; 
        } 
        elseif ($in_class_extends ) {
            $class->extends .= trim(is_array($token) ? $token[1] : $token); 
        } 
        elseif ($in_class_header ) {
            $class->name .= trim(is_array($token) ? $token[1] : $token); 
        } 
        elseif ($in_function_params) {
            $function->params .= is_array($token) ? $token[1] : $token; 
        } 
        elseif ($in_function_header) {
            $function->name .= trim(is_array($token) ? $token[1] : $token); 
        } 
        elseif ($in_function) {
            //$function->source .= is_array($token) ? $token[1] : $token; 
        }
        elseif ($include) {
            $include->name .= trim(is_array($token) ? $token[1] : $token); 
        } 
    }
    $this_file->post_process();
    return $documentables;
}