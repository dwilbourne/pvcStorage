<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\err;

use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class FilePathRecurseException
 */
class FilePathRecurseException extends RuntimeException
{
    public function __construct(string $filePath, string $warning, Throwable $prev = null)
    {
        parent::__construct($filePath, $warning, $prev);
    }
}
