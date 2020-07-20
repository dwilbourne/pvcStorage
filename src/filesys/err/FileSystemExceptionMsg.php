<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\filesys\err;

/**
 * Class FileSystemExceptionMsg
 */
class FileSystemExceptionMsg extends \pvc\msg\ErrorExceptionMsg
{
    public function __construct(string $filename)
    {
        $msgText = 'file system error: Unable to access file = %s';
        $msgVars = [$filename];
        parent::__construct($msgVars, $msgText);
    }
}
