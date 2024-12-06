<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\filesys;

use pvc\storage\err\FileAccessException;
use pvc\storage\filesys\err\FileAccessExceptionMsg;

/** @runTestsInSeparateProcesses  */
class FileAccessWithUopzTest extends FileAccessTestCase
{
    public function testOpenFileReturnsFalseWhenFOpenReturnsFalse() : void
    {
        uopz_set_return('fopen', false);
        $mode = 'w';
        self::expectException(FileAccessException::class);
        $this->fileAccess->openFile($this->fixtureFile, $mode);
        uopz_unset_return('fopen');
    }

    public function testReadFileFailsWhenFreadFails() : void
    {
        uopz_set_return('fread', false);
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->readFile());
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
        uopz_unset_return('fread');
    }

    public function testWriteFileFailsWhenFwriteFails() : void
    {
        uopz_set_return('fwrite', false);
        $mode = 'w';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->writeFile('some text'));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
        uopz_unset_return('fwrite');
    }

    public function testPutFileContentsFailsWhenFWriteFails() : void
    {
        uopz_set_return('fwrite', false);
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
        uopz_unset_return('fwrite');
    }

    public function testGetLineFailsWhenFgetsFails() : void
    {
        uopz_set_return('fgets', false);
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertFalse($this->fileAccess->fileGetLine());
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
        uopz_unset_return('fgets');
    }
}
