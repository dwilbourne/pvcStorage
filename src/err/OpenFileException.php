<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\err;

use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class OpenFileException
 */
class OpenFileException extends RuntimeException
{
    public function __construct(string $fileName, string $mode, string $warningMsg, Throwable $prev = null)
    {
        parent::__construct($fileName, $mode, $warningMsg, $prev);
    }
}
