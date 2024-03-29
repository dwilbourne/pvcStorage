<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\filesys;

use pvc\filesys\messages\FileAccessMsg;

/** @runTestsInSeparateProcesses  */
class FileAccessWithUopzTest extends FileAccessTestCase
{
    public function testGetDirectoryContentsWhenDirectoryReadFails(): void
    {
        uopz_set_return('scandir', false);
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectory);
        self::assertNull($directoryContents);
        self::assertTrue($this->fileAccess->getFileAccessMsg() instanceof FileAccessMsg);
        uopz_unset_return('scandir');
    }

    public function testOpenFileReturnsFalseWhenFOpenReturnsFalse(): void
    {
        uopz_set_return('fopen', false);
        $mode = 'w';
        self::assertFalse($this->fileAccess->openFile($this->fixtureFile, $mode));
        uopz_unset_return('fopen');
    }

    public function testReadFileFailsWhenFreadFails(): void
    {
        uopz_set_return('fread', false);
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->readFile());
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
        uopz_unset_return('fread');
    }

    public function testWriteFileFailsWhenFwriteFails(): void
    {
        uopz_set_return('fwrite', false);
        $mode = 'w';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->writeFile('some text'));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
        uopz_unset_return('fwrite');
    }

    public function testGetFileContentsWhenFilesizeReturnsFalse(): void
    {
        // buffersize (length) should be set to PHP_INT_MAX
        uopz_set_return('filesize', false);
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->getFileContents($this->fixtureFile));
        uopz_unset_return('filesize');
    }

    public function testPutFileContentsFailsWhenFWriteFails(): void
    {
        uopz_set_return('fwrite', false);
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
        uopz_unset_return('fwrite');
    }

    public function testGetLineFailsWhenFgetsFails(): void
    {
        uopz_set_return('fgets', false);
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->fileGetLine());
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
        uopz_unset_return('fgets');
    }
}
