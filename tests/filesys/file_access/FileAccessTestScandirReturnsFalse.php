<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvcTests\storage\filesys\file_access;

use pvc\storage\filesys\err\FileAccessExceptionMsg;
use tests\filesys\FileAccessTestCase;

/**
 * @runInSeparateProcess
 * include namespaced scandir so it returns false for this test
 */

include("ScanDirReturnsFalse.php");

/**
 * @covers \pvc\storage\filesys\file_access\FileAccess
 */
class FileAccessTestScandirReturnsFalse extends FileAccessTestCase
{
    public function testGetDirectoryContentsWhenDirectoryReadFails() : void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectoryWithFiles);
        self::assertNull($directoryContents);
        self::assertTrue($this->fileAccess->getFileAccessErrmsg() instanceof FileAccessExceptionMsg);
    }
}
