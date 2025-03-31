<?php

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\RuntimeException;
use Throwable;

class FileNotReadableException extends RuntimeException
{
    public function __construct(string $filePath, ?Throwable $prev = null)
    {
        parent::__construct($filePath, $prev);
    }
}
