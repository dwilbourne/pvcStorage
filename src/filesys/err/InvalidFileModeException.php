<?php

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\LogicException;
use Throwable;

class InvalidFileModeException extends LogicException
{
    public function __construct(string $badFileMode, ?Throwable $previous = null)
    {
        parent::__construct($badFileMode, $previous);
    }
}
