<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\html\err;

use pvc\err\stock\LogicException;
use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class DTOMissingPropertyException
 */
class DTOMissingPropertyException extends RuntimeException
{
    public function __construct(string $missingPropertyNames, string $className, Throwable $prev = null)
    {
        parent::__construct($missingPropertyNames, $className, $prev);
    }
}
