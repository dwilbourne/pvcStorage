<?php

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * InvalidFileNameException should be thrown when someone tries to specify illegal characters in a filename.
 */
class InvalidFileNameException extends LogicException
{
    public function __construct(string $badFileName, ?Throwable $previous = null)
    {
        parent::__construct($badFileName, $previous);
    }
}
