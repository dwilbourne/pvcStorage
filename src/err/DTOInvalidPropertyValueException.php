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
 * Class DTOInvalidPropertyValueException
 */
class DTOInvalidPropertyValueException extends RuntimeException
{
    public function __construct(string $propertyName, mixed $value, string $className, Throwable $prev = null)
    {
        parent::__construct($propertyName, $value, $className, $prev);
    }
}
