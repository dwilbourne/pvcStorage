<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\filesys;

use pvc\filesys\err\FileAccessExceptionMsg;

/**
 * @runInSeparateProcess
 * include namespaced scandir so it returns false for this test
 */

include("ScanDirReturnsFalse.php");

/**
 * @covers \pvc\filesys\FileAccess
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
