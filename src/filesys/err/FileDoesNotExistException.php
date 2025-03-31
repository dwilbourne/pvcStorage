<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\stock\LogicException;
use Throwable;

/**
 * Class FileDoesNotExistException
 */
class FileDoesNotExistException extends LogicException
{
    public function __construct(string $filePath, ?Throwable $prev = null)
    {
        parent::__construct($filePath, $prev);
    }
}
