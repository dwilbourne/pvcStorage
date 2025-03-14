<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare (strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\XDataAbstract;

/**
 * Class _FileSysXData
 */
class _FileSysXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            FileNotReadableException::class=> 1001,
            FilePathDoesNotExistException::class => 1002,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            FileNotReadableException::class => '${filePath} is not readable',
            FilePathDoesNotExistException::class => '${filePath} is not a valid file or directory name',
        ];
    }
}