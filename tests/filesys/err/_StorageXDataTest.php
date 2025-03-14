<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\storage\filesys\err;

use pvc\err\XDataTestMaster;
use pvc\storage\filesys\err\_StorageXData;

/**
 * Class _RegexXDataTest
 */
class _StorageXDataTest extends XDataTestMaster
{
    /**
     * @function testPvcRegexExceptionLibrary
     * @covers \pvc\storage\filesys\err\_StorageXData::getXMessageTemplates
     * @covers \pvc\storage\filesys\err\_StorageXData::getLocalXCodes
     * @covers \pvc\storage\filesys\err\FileAccessException
     * @covers \pvc\storage\filesys\err\FileGetContentsException
     * @covers \pvc\storage\filesys\err\FileHandleException
     * @covers \pvc\storage\filesys\err\FilePathDoesNotExistException
     * @covers \pvc\storage\filesys\err\FilePermissionsException
     * @covers \pvc\storage\filesys\err\FilePutContentsException
     * @covers \pvc\storage\filesys\err\InvalidReadLengthException
     * @covers \pvc\storage\filesys\err\InvalidSortOrderException
     * @covers \pvc\storage\filesys\err\OpenFileException
     * @covers \pvc\storage\filesys\err\ScandirException
     */
    public function testPvcRegexExceptionLibrary(): void
    {
        $xData = new _StorageXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
