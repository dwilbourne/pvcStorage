<?php

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\RuntimeException;
use Throwable;

class FileOpenException extends RuntimeException
{
    public function __construct(string $filePath, string $mode, ?Throwable $previous = null)
    {
        parent::__construct($filePath, $mode, $previous);
    }
}
