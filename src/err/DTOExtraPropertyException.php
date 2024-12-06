<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\html\err;

use pvc\err\stock\RuntimeException;
use Throwable;

/**
 * Class DTOExtraPropertyException
 */
class DTOExtraPropertyException extends RuntimeException
{
    public function __construct(string $extraPropertyName, string $className, Throwable $prev = null)
    {
        parent::__construct($extraPropertyName, $className, $prev);
    }
}
