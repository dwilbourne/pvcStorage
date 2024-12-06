<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare (strict_types=1);

namespace pvc\storage\err;

use pvc\err\XDataAbstract;
use pvc\html\err\DTOExtraPropertyException;
use pvc\html\err\DTOInvalidPropertyValueException;
use pvc\html\err\DTOMissingPropertyException;

/**
 * Class _StorageXData
 */
class _StorageXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            FileGetContentsException::class => 1002,
            FilePutContentsException::class => 1003,
            InvalidFileModeException::class => 1004,
            OpenFileException::class => 1005,
            FileHandleException::class => 1006,
            InvalidReadLengthException::class => 1007,
            FileAccessException::class => 1008,
            FilePathDoesNotExistException::class => 1001,
            FilePermissionsException::class => 1001,
            TreeIdNotSetException::class => 1003,
            InvalidSortOrderException::class => 1004,
            FilePathRecurseException::class => 1005,
            InsufficientFileModeException::class => 1001,
            UnsetFileNameException::class => 1001,
            FileInfoException::class => 1001,
            DTOInvalidPropertyValueException::class => 1017,
            DTOMissingPropertyException::class => 1021,
            DTOExtraPropertyException::class => 1025,

        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            FileGetContentsException::class => 'unknown filesystem error getting the contents of ${fileName}',
            FilePutContentsException::class => 'unknown filesystem error writing data to file ${fileName}',
            InvalidFileModeException::class => 'invalid file mode speicifed: ${fileMode}',
            OpenFileException::class => 'unable to open file ${fileName}  in mode ${mode}. Warning msg was: ${warning}',
            FileHandleException::class => 'File handle exception - the file has not been opened.',
            InvalidReadLengthException::class => 'invalid read length parameter - must be an integer greater than 0.',
            FileAccessException::class => 'an error occurred trying to access ${fileName}',
            FilePermissionsException::class => 'unable to open ${fileName} in mode ${mode} due to insuffucient file permissions.',
            FilePathDoesNotExistException::class => '${filePath} is not a valid file or directory name',
            TreeIdNotSetException::class => 'Error trying to create value objects before treeid is set.',
            InvalidSortOrderException::class => 'Invalid sort order specified for returning files in a directory.',
            FilePathRecurseException::class => 'scandir error trying to get directory contents of ${fileName} - warning was ${warning}.',
            InsufficientFileModeException::class => 'file does not exist and mode ${mode} is not sufficcient to create the file.',
            UnsetFileNameException::class => 'file name has not yet been set on this object',
            FileInfoException::class => 'unable to obtain the requested file information for ${$filename}.',
            DTOInvalidPropertyValueException::class => 'DTO ${className} error - cannot assign value ${value} to property ${propertyName}',
            DTOMissingPropertyException::class => 'DTO ${className} constructor is missing the following properties: [${missingPropertyNames}].',
            DTOExtraPropertyException::class => 'DTO ${className} constructor was passed an extra property [${extraPropertyName}]',
        ];
    }
}