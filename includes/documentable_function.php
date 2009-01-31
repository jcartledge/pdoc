<?php
class DocumentableFunction extends Documentable {
    var $type = 'function';
    var $line;
    var $file;
    var $params;
    var $source;
    function description() {
        return sprintf('%s%s',
            $this->name,
            $this->params ? "({$this->params})" : '()');
    }
}
