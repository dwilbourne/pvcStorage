<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\storage\resource\err;

use pvc\err\XDataTestMaster;
use pvc\storage\resource\err\_ResourceXData;

/**
 * Class _RegexXDataTest
 */
class _ResourceXDataTest extends XDataTestMaster
{
    /**
     * @function testPvcRegexExceptionLibrary
     * @covers \pvc\storage\filesys\err\_ResourceXData::getXMessageTemplates
     * @covers \pvc\storage\filesys\err\_ResourceXData::getLocalXCodes
     * @covers \pvc\storage\resource\err\InvalidResourceException
     */
    public function testPvcRegexExceptionLibrary(): void
    {
        $xData = new _ResourceXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
