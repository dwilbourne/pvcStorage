<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\filesys;

use Error;
use pvc\filesys\err\FileAccessException;
use pvc\filesys\err\FileAccessExceptionMsg;

class FileAccessTest extends FileAccessTestCase
{
    public function testConstruction() : void
    {
        // verify that untyped property is initialized to null
        self::assertNull($this->fileAccess->getHandle());
    }

    public function testNonExistentFileDoesNotExist() : void
    {
        self::assertFalse($this->fileAccess->fileExists($this->fixtureFileNonExistent));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testFileWhichDoesExistInMockFilesystem() : void
    {
        self::assertTrue($this->fileAccess->fileExists($this->fixtureFile));
    }

    public function testFileExistsFailsWhenGivenADirectory() : void
    {
        self::assertFalse($this->fileAccess->fileExists($this->fixtureDirectoryWithFiles));
    }

    public function testDirectoryExists() : void
    {
        self::assertTrue($this->fileAccess->directoryExists($this->fixtureDirectoryWithFiles));
    }

    public function testDirectoryExistsFailsOnBadDirectory() : void
    {
        self::assertFalse($this->fileAccess->directoryExists($this->fixtureDirectoryNonExistent));
    }

    public function testDirectoryExistsFailsWhenGivenAFile() : void
    {
        self::assertFalse($this->fileAccess->directoryExists($this->fixtureFile));
    }

    public function testFilePermissions() : void
    {
        // default permissions in the mock filesystem for files are set to null,
        // which is equivalent to 0666 (read / write for owner / group / world)
        $expectedPermissions = 0666;
        self::assertEquals($expectedPermissions, $this->vfsFile->getPermissions());
        $newPermissions = 0444;
        $this->vfsFile->chmod($newPermissions);
        self::assertEquals($newPermissions, $this->vfsFile->getPermissions());
    }


    public function testFileIsNotReadableWhenDoesNotExist() : void
    {
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixtureFileNonExistent));
    }

    public function testFileIsReadable() : void
    {
        self::assertTrue($this->fileAccess->fileIsReadable($this->fixtureFile));
        // now change permissions to so no one can do anything
        $this->vfsFile->chmod(0000);
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixtureFile));
    }

    public function testFileIsNotWriteableWhenDoesNotExist() : void
    {
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureFileNonExistent));
    }

    public function testFileIsWriteable() : void
    {
        self::assertTrue($this->fileAccess->fileIsWriteable($this->fixtureFile));
        // now change permissions to so file is read only for everyone
        $this->vfsFile->chmod(0444);
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureFile));
    }

    public function testFileIsWriteableFailsWhenIsADirectory() : void
    {
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureDirectoryWithFiles));
    }

    public function testFileProspectiveIsWriteableWhenFileExistsAndIsWriteable() : void
    {
        self::assertTrue($this->fileAccess->fileProspectiveIsWriteable($this->fixtureFile));
    }

    public function testFileProspectiveIsWriteableWhenFileExistsAndIsNotWriteable() : void
    {
        $this->vfsFile->chmod(0444);
        self::assertFalse($this->fileAccess->fileProspectiveIsWriteable($this->fixtureFile));
    }

    public function testFileProspectiveIsWriteableWhenFileDoesNotExist() : void
    {
        $prospectiveFile = $this->fixtureDirectoryWithFiles . DIRECTORY_SEPARATOR . 'foo.php';
        self::assertTrue($this->fileAccess->fileProspectiveIsWriteable($prospectiveFile));
    }

    public function testFileProspectiveIsNotWriteableWhenFileDoesNotExist() : void
    {
        $prospectiveFile = $this->fixtureDirectoryWithFiles . DIRECTORY_SEPARATOR . 'foo.php';
        $this->vfsDirectory->chmod(0444);
        self::assertFalse($this->fileAccess->fileProspectiveIsWriteable($prospectiveFile));
    }

    public function testFileProspectiveIsWriteableFailsWhenIsADirectory() : void
    {
        self::assertFalse($this->fileAccess->fileProspectiveIsWriteable($this->fixtureDirectoryWithFiles));
    }


    public function testDirectoryIsReadable() : void
    {
        self::assertTrue($this->fileAccess->directoryIsReadable($this->fixtureDirectoryWithFiles));
        $this->vfsDirectory->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureDirectoryWithFiles));
    }

    public function testDirectoryIsReadableFailsWhenIsAFile() : void
    {
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureFile));
    }

    public function testDirectoryIsReadableFailsWhenDoesNotExist() : void
    {
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureDirectoryNonExistent));
    }

    public function testGetDirectoryContentsWhenDirectoryIsNotReadable() : void
    {
        self::assertNull($this->fileAccess->getDirectoryContents($this->fixtureDirectoryNonExistent));
    }

    public function testGetDirectoryContentsReturnsFileNames() : void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectoryWithFiles);
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory */
        self::assertEquals($this->expectedNumberOfDirectoryEntriesWithoutDots, count($directoryContents));
    }

    public function testGetDirectoryContentsReturnsEmptyArrayForEmptyDirectory() : void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectoryEmpty);
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory */
        self::assertEquals(0, count($directoryContents));
    }

    public function testDirectoryIsWriteable() : void
    {
        self::assertTrue($this->fileAccess->directoryIsWriteable($this->fixtureDirectoryWithFiles));
        $this->vfsDirectory->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureDirectoryWithFiles));
    }

    public function testDirectoryIsWriteableFailsWhenIsAFile() : void
    {
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureFile));
    }

    public function testDirectoryIsWriteableFailsWhenDoesNotExist() : void
    {
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureDirectoryNonExistent));
    }



    /**
     * testOpenFileFails
     */
    public function testOpenFileFailsAndThereforeCloseFileFailsBecauseFileIsNotOpen() : void
    {
        $mode = 'r';
        self::assertFalse($this->fileAccess->openFile($this->fixtureFileNonExistent, $mode));
        $openFileErrmsg = $this->fileAccess->getFileAccessErrmsg();
        self::assertInstanceOf(FileAccessExceptionMsg::class, $openFileErrmsg);

        self::assertNull($this->fileAccess->getHandle());

        self::assertFalse($this->fileAccess->closeFile());
        $closeFileErrmsg = $this->fileAccess->getFileAccessErrmsg();
        self::assertInstanceOf(FileAccessExceptionMsg::class, $closeFileErrmsg);
        self::assertNotEquals($openFileErrmsg, $closeFileErrmsg);
    }

    public function testOpenFileFailsWhenPermissionsAreInsufficient() : void
    {
        // remove all permissions
        $this->vfsFile->chmod(0000);
        $mode = 'r';
        self::assertFalse($this->fileAccess->openFile($this->fixtureFile, $mode));
        $openFileErrmsg = $this->fileAccess->getFileAccessErrmsg();
        self::assertInstanceOf(FileAccessExceptionMsg::class, $openFileErrmsg);
    }

    /**
     * testOpenFileSucceedsCanGetHandleAndCloseFileSucceeds
     */
    public function testOpenFileSucceedsCanGetHandleAndCloseFileSucceeds() : void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertNotNull($this->fileAccess->getHandle());
        /* trying to access unset property should produce an error */
        self::expectException(Error::class);
        $msg = $this->fileAccess->getFileAccessErrmsg();
        $this->fileAccess->closeFile();
    }

    public function testWriteFileFailsOnFileWhichWasNotOpened() : void
    {
        self::assertFalse($this->fileAccess->writeFile('some text'));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testReadFileInvalidLength() : void
    {
        // length must be positive
        $badLength = 0;
        self::assertFalse($this->fileAccess->readFile($badLength));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testReadFileFailsWhenFileWasNotOpened() : void
    {
        // default length is 8096 and we will not specify something different
        self::assertFalse($this->fileAccess->readFile());
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testReadFileSucceedsAndEOFIsTrue() : void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->readFile());
        self::assertTrue($this->fileAccess->eof());
        $this->fileAccess->closeFile();
    }

    public function testReadFileSucceedsWithMultipleReads() : void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        $expectedResult = 'some php content';
        $length = 2;
        $actualResult = '';
        while (!$this->fileAccess->eof()) {
            $actualResult .= $this->fileAccess->readFile($length);
        }
        $this->fileAccess->closeFile();
        self::assertEquals($expectedResult, $actualResult);
    }

    public function testEofThrowsException() : void
    {
        self::expectException(FileAccessException::class);
        $result = $this->fileAccess->eof();
    }

    public function testWriteReadFileSucceeds() : void
    {
        $testData = 'some text';

        $mode = 'w';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertTrue($this->fileAccess->writeFile($testData));
        $this->fileAccess->closeFile();

        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertEquals($testData, $this->fileAccess->readFile());
        $this->fileAccess->closeFile();
    }

    public function testFileGetContentsSucceeds() : void
    {
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->fileGetContents($this->fixtureFile));
    }

    public function testFileGetContentsFailsWhenHandleAlreadySet() : void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFileAdditional, $mode));
        self::assertFalse($this->fileAccess->fileGetContents($this->fixtureFile));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testGetFileContentsFailsWithInsufficientPermissions() : void
    {
        $this->vfsFile->chmod(0000);
        self::assertFalse($this->fileAccess->fileGetContents($this->fixtureFile));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testFilePutContentsFileGetContentsSucceeds() : void
    {
        $contents = 'this is some string.';
        self::assertTrue($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertEquals($contents, $this->fileAccess->fileGetContents($this->fixtureFile));
    }

    public function testPutFileContentsFailsWhenFileIsAlreadyOpen() : void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testPutFileContentsFailsWithInsufficientPermissions() : void
    {
        $this->vfsFile->chmod(0000);
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testFileGetLineFailsWhenFileIsNotOpen() : void
    {
        self::assertFalse($this->fileAccess->fileGetLine());
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testFileGetLineSucceeds() : void
    {
        $expectedResult = 'some php content';
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertEquals($expectedResult, $this->fileAccess->fileGetLine());
        $this->fileAccess->closeFile();
    }
}
