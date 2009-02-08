<?php

class Options {
    public $args = array();
    public $opts = array();
    private $current_opt;
    private $parsed_equals;
    function __construct($description, $args = null) {
        if(is_string($description)) {
            $description = explode(',', $description);
        } else {
            $description = (array)$description;
        }
        foreach($description as $name => $desc) {
            $desc = trim($desc);
            $opt = array('description' => $desc);
            if(is_numeric($name)) {
                list($name, $opt['description']) = preg_split('/[\s]+/', $desc, 2);
            }
            $name = trim($name);
            if($name{0} == '#') {
                $opt['flag'] = true;
                $name = substr($name, 1);
            }
            list($name, $default_value) = array_map(trim, explode('=', $name, 2));
            list($name, $opt['short']) = explode('/', $name, 2);
            if($opt['short']) {
                list($name, $opt['short']) = array($opt['short'], $name);
            }
            $this->opts[$name] = $opt;
            if($default_value) $this->$name = $default_value;
        }
        $this->set_short_names();
        if($args) {
            $this->parse($args);
        }
    }

    function parse($args) {
        foreach($args as $arg) {
            $this->parse_long_opt($arg) or
            $this->parse_short_opt($arg) or
            $this->parse_value($arg);
        }
        return $this;
    }

    protected function set_short_names() {
        $names = array_keys($this->opts);
        sort($names);
        foreach($names as $name) {
            if(isset($this->opts[$name]['short'])) continue;
            $this->opts[$name]['short'] = $this->unique_short_name($name);
        }
    }

    private function parse_value($arg) {
        if($this->current_opt) {
            $orig_arg = $arg;
            $arg = $this->parsed_equals ? $arg : preg_replace('/^(=)/', '', $arg);
            $this->parsed_equals |= $arg != $orig_arg;
            if($arg) {
                $this->{$this->current_opt} = $arg;
                unset($this->current_opt);
            }
        } else {
            $this->args[] = $arg;
        }
    }

    private function parse_short_opt($str) {
        $shopt = $this->short_opt_name($str);
        if(!$shopt) return;
        $lopt = $this->long_opt_name($shopt);
        //strip leading equals if provided
        $remainder = substr($str, strlen($shopt) + 1);
        if($this->is_flag($lopt)){
            $this->{$lopt} = true;
            if($remainder) {
                $this->parse_short_opt('-' . $remainder);
            }
        } else {
            $this->parsed_equals = $remainder{0} == '=';
            $remainder = preg_replace('/^(=)/', '', $remainder);
            if($remainder) {
                $this->{$lopt} = $remainder;
            } else {
                $this->current_opt = $lopt;
            }
        }
        return true;
    }

    private function parse_long_opt($str) {
        $lopt = $this->long_opt_name($str);
        if(!$lopt) return;
        $this->parsed_equals = strpos($str, '=') !== false; 
        list(,$value) = explode('=', $str, 2);
        $this->{$lopt} = $this->is_flag($lopt) ?
            true :
            $value;
        $this->current_opt = strlen($value) || $this->is_flag($lopt) ?
            null :
            $lopt;
        return true;
    }

    private function short_opt_name($str) {
        // make sure it's an argv short opt
        if(strpos($str, '-') !== 0) return;
        foreach($this->opts as $name => $desc) {
            if(strpos($str, $desc['short']) === 1) return $desc['short'];
        }
        return false;
    }

    /**
     * accepts string
     * either from argv array
     * or a short opt name
     */
    private function long_opt_name($str) {
        // handle short option names
        foreach($this->opts as $opt_name => $opt_def) {
            if($opt_def['short'] == $str) return $opt_name;
        }
        // make sure it's an argv long opt
        if(strpos($str, '--') !== 0) return;
        // split on equals and lookup
        $str = substr(array_shift(explode('=', $str, 2)), 2);
        return array_search($str, array_keys($this->opts)) !== false ?
            $str :
            false;
    }

    private function is_arg($str) {
        return strpos($str, '--') !== 0 && strpos($str, '-') !== 0;
    }

    private function is_flag($opt) {
        return $this->opts[$opt]['flag'];
    }

    private function unique_short_name($name) {
        while(!$this->is_unique_short_name($short)) $short .= $name{$i++};
        return $short;
    }

    private function is_unique_short_name($name) {
        if(!$name) return false;
        foreach($this->opts as $name => $desc) {
            if($name == $desc['short']) return false;
        }
        return true;
    }
}