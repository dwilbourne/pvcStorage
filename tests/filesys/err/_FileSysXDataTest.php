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
     * @covers \pvc\storage\filesys\err\FilePathDoesNotExistException
     * @covers \pvc\storage\filesys\err\FileNotReadableException
     */
    public function testPvcRegexExceptionLibrary(): void
    {
        $xData = new _FileSysXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
