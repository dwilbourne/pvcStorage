<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\storage\err;

use pvc\err\XDataTestMaster;
use pvc\storage\err\_StorageXData;

/**
 * Class _RegexXDataTest
 */
class _StorageXDataTest  extends XDataTestMaster
{
    /**
     * @function testPvcRegexExceptionLibrary
     * @covers \pvc\storage\err\_StorageXData::getXMessageTemplates
     * @covers \pvc\storage\err\_StorageXData::getLocalXCodes
     * @covers \pvc\storage\err\DirectoryContentsException
     * @covers \pvc\storage\err\FileGetContentsException
     * @covers \pvc\storage\err\FileputContentsException
     */
    public function testPvcRegexExceptionLibrary(): void
    {
        $xData = new \pvc\storage\err\_StorageXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
