<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\filesys\err;

use pvc\err\throwable\exception\stock_rebrands\ErrorException;
use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\msg\ErrorExceptionMsg;
use Throwable;

/**
 * Class FileAccessException
 * @package pvc\filesys\err
 */
class FileAccessException extends ErrorException
{
    public function __construct(ErrorExceptionMsg $msg, Throwable $previous = null)
    {
        $code = ec::FILE_ACCESS_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
