<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\storage\filesys\err;

use pvc\err\XDataTestMaster;
use pvc\storage\filesys\err\_FileSysXData;

/**
 * Class _RegexXDataTest
 */
class _FileSysXDataTest extends XDataTestMaster
{
    /**
     * @function testPvcRegexExceptionLibrary
     * @covers \pvc\storage\filesys\err\_FileSysXData::getXMessageTemplates
     * @covers \pvc\storage\filesys\err\_FileSysXData::getLocalXCodes
     * @covers \pvc\storage\filesys\err\FileDoesNotExistException
     * @covers \pvc\storage\filesys\err\FileNotReadableException
     * @covers \pvc\storage\filesys\err\FileOpenException
     * @covers \pvc\storage\filesys\err\InvalidFileHandleException
     * @covers \pvc\storage\filesys\err\InvalidFileModeException
     * @covers \pvc\storage\filesys\err\InvalidFileNameException
     */
    public function testPvcRegexExceptionLibrary(): void
    {
        $xData = new _FileSysXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
