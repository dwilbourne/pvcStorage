<?php

/**
 * @package pvcErr
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\XDataAbstract;
use pvc\interfaces\err\XDataInterface;

/**
 * Class _PvcExceptionLibrary
 * @package pvcErr
 */
class _FileSysXData extends XDataAbstract implements XDataInterface
{
    /**
     * @function getLocalXCodes
     * @return array<class-string, int>
     */
    public function getLocalXCodes(): array
    {
        return [
            FileDoesNotExistException::class => 1000,
            FileGetContentsException::class => 1001,
            FileNotReadableException::class => 1002,
            FileOpenException::class => 1003,
            InvalidFileHandleException::class => 1004,
            InvalidFileModeException::class => 1005,
            InvalidFileNameException::class => 1006,
        ];
    }

    /**
     * @function getLocalXMessages
     * @return array<class-string, string>
     */
    public function getXMessageTemplates(): array
    {
        return [
            FileDoesNotExistException::class => 'File ${filePath} does not exist.',
            FileGetContentsException::class => 'File ${filePath} exists and is readable but get_file_contents failed.',
            FileNotReadableException::class => 'File ${filePath} is not readable.',
            FileOpenException::class => 'File ${filePath} exists cannot be opened in mode ${mode}.',
            InvalidFileHandleException::class => 'Resource is not a handle to a stream resource.',
            InvalidFileModeException::class => '${badFileMode} is not a valid mode for opening a file.',
            InvalidFileNameException::class => 'filename ${badFileName} is not a valid file name.',
        ];
    }
}
