<?php

namespace SensioLabs\TodoBundle\Persistence;

class DatabaseException extends \RuntimeException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}