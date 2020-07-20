<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\filesys\err;

use pvc\msg\ErrorExceptionMsg;

/**
 * Class FilesysBadSearchDirMsg
 */
class FilesysBadSearchDirMsg extends ErrorExceptionMsg
{
    public function __construct()
    {
        $msgText = 'Invalid directory specified in constructor - directory does not exist.';
        parent::__construct([], $msgText);
    }
}
