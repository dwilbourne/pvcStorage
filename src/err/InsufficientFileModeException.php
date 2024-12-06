<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\err;

use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class InsufficientFileModeException
 */
class InsufficientFileModeException extends RuntimeException
{
    public function __construct(string $mode, Throwable $prev = null)
    {
        parent::__construct($mode, $prev);
    }
}
