<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\filesys\err;

use pvc\err\throwable\ErrorExceptionConstants as ec;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\msg\ErrorExceptionMsg;
use Throwable;

/**
 * Class FilesysBadSearchDirException
 */
class FilesysBadSearchDirException extends InvalidArgumentException
{
    public function __construct(ErrorExceptionMsg $msg, Throwable $previous = null)
    {
        $code = ec::INVALID_SEARCH_DIR_EXCEPTION;
        parent::__construct($msg, $code, $previous);
    }
}
