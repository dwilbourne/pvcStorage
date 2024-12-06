<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class InvalidFileModeException
 */
class InvalidFileModeException extends LogicException
{
    public function __construct(string $fileMode, Throwable $prev = null)
    {
        parent::__construct($fileMode, $prev);
    }
}
