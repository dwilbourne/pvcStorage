<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare (strict_types=1);

namespace pvc\storage\filesys\err;

use pvc\err\XDataAbstract;

/**
 * Class _StorageXData
 */
class _StorageXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            FileAccessException::class => 1001,
            FileGetContentsException::class => 1002,
            FileHandleException::class => 1003,
            FilePathDoesNotExistException::class => 1004,
            FilePermissionsException::class => 1005,
            FilePutContentsException::class => 1006,
            InvalidReadLengthException::class => 1007,
            InvalidSortOrderException::class => 1008,
            OpenFileException::class => 1009,
            ScandirException::class => 1010,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            FileAccessException::class => 'an error occurred trying to access ${fileName}',
            FileGetContentsException::class => 'unknown filesystem error getting the contents of ${fileName}',
            FileHandleException::class => 'File handle exception - the file has not been opened.',
            FilePathDoesNotExistException::class => '${filePath} is not a valid file or directory name',
            FilePermissionsException::class => 'unable to open ${fileName} in mode ${mode} due to insuffucient file permissions.',
            FilePutContentsException::class => 'unknown filesystem error writing data to file ${fileName}',
            InvalidReadLengthException::class => 'invalid read length parameter - must be an integer greater than 0.',
            InvalidSortOrderException::class => 'Invalid sort order specified for returning files in a directory.',
            OpenFileException::class => 'unable to open file ${filePath}  in mode ${mode}. Warning msg was: ${warning}',
            ScandirException::class => 'scandir error trying to get directory contents of ${filePath} - warning was ${warning}.',
        ];
    }
}