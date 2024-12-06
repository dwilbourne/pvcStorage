<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\err;

use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class FileInfoException
 */
class FileInfoException extends RuntimeException
{
    public function __construct(string $fileName, Throwable $prev = null)
    {
        parent::__construct($fileName, $prev);
    }
}
