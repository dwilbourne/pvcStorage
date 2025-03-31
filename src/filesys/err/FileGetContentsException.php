<?php

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\RuntimeException;
use Throwable;

class FileGetContentsException extends RuntimeException
{
    public function __construct(string $filePath, ?Throwable $previous = null)
    {
        parent::__construct($filePath, $previous);
    }
}
