<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\filesys\err;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\err\throwable\exception\stock_rebrands\Exception;
use pvc\err\throwable\Throwable;
use pvc\msg\ErrorExceptionMsg;

/**
 * Class FileSystemException
 */
class FileSystemException extends Exception
{
    public function __construct(ErrorExceptionMsg $msg, int $code = 0, Throwable $previous = null)
    {
        if ($code == 0) {
            $code = ec::FILESYSTEM_EXCEPTION;
        }
        parent::__construct($msg, $code, $previous);
    }
}
