<?php

namespace isamarin\Alisa;

use Exception;

class AlisaException extends Exception
{
    protected $message = 'Unknown exception';
    protected $code = 0;
    protected $file;
    protected $line;

    public function __construct($message = '', $code = 0)
    {
        if ( ! $message) {
            throw new $this('Unknown ' . get_class($this));
        }
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . $this->getTraceAsString();
    }
}