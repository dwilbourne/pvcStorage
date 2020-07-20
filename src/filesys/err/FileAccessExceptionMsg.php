<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\filesys\err;


use pvc\msg\ErrorExceptionMsg;

/**
 * Class FileAccessExceptionMsg
 * @package pvc\filesys\err
 */
class FileAccessExceptionMsg extends ErrorExceptionMsg
{

    public function __construct(array $msgVars, string $msgText)
    {
        parent::__construct($msgVars, $msgText);
    }

}