<?php

namespace isamarin\Alisa;

use Exception;
use function get_class;

/**
 * Class AlisaException
 * @package isamarin\Alisa
 */
class AlisaException extends Exception
{
    /**
     * AlisaException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message = '', $code = 0)
    {
        if ( ! $message) {
            throw new $this('Unknown ' . get_class($this));
        }
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
            . $this->getTraceAsString();
    }
}