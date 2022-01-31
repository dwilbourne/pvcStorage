<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace tests\filesys;

use pvc\filesys\messages\FileAccessMsg;

/**
 * @covers \pvc\filesys\FileAccess
 */
class FileAccessTest extends FileAccessTestCase
{
    public function testConstruction(): void
    {
        // verify that untyped property is initialized to null
        self::assertNull($this->fileAccess->getHandle());
    }

    public function testNonExistentFileDoesNotExist(): void
    {
        self::assertFalse($this->fileAccess->fileExists($this->fixtureFileNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileWhichDoesExistInMockFilesystem(): void
    {
        self::assertTrue($this->fileAccess->fileExists($this->fixtureFile));
        self::assertNull(($this->fileAccess->getFileAccessMsg()));
    }

    public function testFileExistsFailsWhenGivenADirectory(): void
    {
        self::assertFalse($this->fileAccess->fileExists($this->fixtureDirectory));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryExists(): void
    {
        self::assertTrue($this->fileAccess->directoryExists($this->fixtureDirectory));
        self::assertNull($this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryExistsFailsOnBadDirectory(): void
    {
        self::assertFalse($this->fileAccess->directoryExists($this->fixtureDirectoryNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryExistsFailsWhenGivenAFile(): void
    {
        self::assertFalse($this->fileAccess->directoryExists($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFilePermissions(): void
    {
        // default permissions in the mock filesystem for files are set to null,
        // which is equivalent to 0666 (read / write for owner / group / world)
        $expectedPermissions = 0666;
        self::assertEquals($expectedPermissions, $this->vfsFile->getPermissions());
        $newPermissions = 0444;
        $this->vfsFile->chmod($newPermissions);
        self::assertEquals($newPermissions, $this->vfsFile->getPermissions());
    }


    public function testFileIsNotReadableWhenDoesNotExist(): void
    {
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixtureFileNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileIsReadable(): void
    {
        self::assertTrue($this->fileAccess->fileIsReadable($this->fixtureFile));
        // now change permissions to so no one can do anything
        $this->vfsFile->chmod(0000);
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileIsNotWriteableWhenDoesNotExist(): void
    {
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureFileNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileIsWriteable(): void
    {
        self::assertTrue($this->fileAccess->fileIsWriteable($this->fixtureFile));
        // now change permissions to so file is read only for everyone
        $this->vfsFile->chmod(0444);
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileIsWriteableFailsWhenIsADirectory(): void
    {
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixtureDirectory));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsReadable(): void
    {
        self::assertTrue($this->fileAccess->directoryIsReadable($this->fixtureDirectory));
        self::assertNull($this->fileAccess->getFileAccessMsg());
        $this->vfsDirectory->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureDirectory));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsReadableFailsWhenIsAFile(): void
    {
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsReadableFailsWhenDoesNotExist(): void
    {
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixtureDirectoryNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testGetDirectoryContentsWhenDirectoryIsNotReadable(): void
    {
        self::assertNull($this->fileAccess->getDirectoryContents($this->fixtureDirectoryNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testGetDirectoryContentsReturnsFileNamesWithRecurse(): void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectory);
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory, recurse defaults to true */
        self::assertEquals(
            $this->expectedNumberOfDirectoryEntriesWithoutDotsAndWithRecursing,
            count($directoryContents)
        );
        self::assertNull($this->fileAccess->getFileAccessMsg());
    }

    public function testGetDirectoryContentsReturnsFileNamesWithDotsWithoutRecursing(): void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectory, null, false, true);
        self::assertIsArray($directoryContents);
        self::assertEquals(
            $this->expectedNumberOfDirectoryEntriesWithDotsAndWithoutRecursing,
            count($directoryContents)
        );
        self::assertNull($this->fileAccess->getFileAccessMsg());
    }

    public function testGetDirectoryContentsReturnsEmptyArrayForEmptyDirectory(): void
    {
        $directoryContents = $this->fileAccess->getDirectoryContents($this->fixtureDirectoryEmpty);
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory */
        self::assertEquals(0, count($directoryContents));
        self::assertNull($this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsWriteable(): void
    {
        self::assertTrue($this->fileAccess->directoryIsWriteable($this->fixtureDirectory));
        self::assertNull($this->fileAccess->getFileAccessMsg());
        $this->vfsDirectory->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureDirectory));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsWriteableFailsWhenIsAFile(): void
    {
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testDirectoryIsWriteableFailsWhenDoesNotExist(): void
    {
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixtureDirectoryNonExistent));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    /**
     * testOpenFileFails
     */
    public function testOpenFileFailsAndThereforeCloseFileFailsBecauseFileIsNotOpen(): void
    {
        $mode = 'r';
        self::assertFalse($this->fileAccess->openFile($this->fixtureFileNonExistent, $mode));
        $openFileErrmsg = $this->fileAccess->getFileAccessMsg();
        self::assertInstanceOf(FileAccessMsg::class, $openFileErrmsg);

        self::assertNull($this->fileAccess->getHandle());

        self::assertFalse($this->fileAccess->closeFile());
        $closeFileErrmsg = $this->fileAccess->getFileAccessMsg();
        self::assertInstanceOf(FileAccessMsg::class, $closeFileErrmsg);
        self::assertNotEquals($openFileErrmsg, $closeFileErrmsg);
    }

    public function testOpenFileFailsWhenPermissionsAreInsufficient(): void
    {
        // remove all permissions
        $this->vfsFile->chmod(0000);
        $mode = 'r';
        self::assertFalse($this->fileAccess->openFile($this->fixtureFile, $mode));
        $openFileErrmsg = $this->fileAccess->getFileAccessMsg();
        self::assertInstanceOf(FileAccessMsg::class, $openFileErrmsg);
    }

    /**
     * testOpenFileSucceedsCanGetHandleAndCloseFileSucceeds
     */
    public function testOpenFileSucceedsCanGetHandleAndCloseFileSucceeds(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertNotNull($this->fileAccess->getHandle());
        self::assertNull($this->fileAccess->getFileAccessMsg());
        $this->fileAccess->closeFile();
    }

    public function testWriteFileFailsOnFileWhichWasNotOpened(): void
    {
        self::assertFalse($this->fileAccess->writeFile('some text'));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testReadFileInvalidLength(): void
    {
        // length must be positive
        $badLength = 0;
        self::assertFalse($this->fileAccess->readFile($badLength));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testReadFileFailsWhenFileWasNotOpened(): void
    {
        // default length is 8096 and we will not specify something different
        self::assertFalse($this->fileAccess->readFile());
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testReadFileSucceedsAndEOFIsTrue(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->readFile());
        self::assertTrue($this->fileAccess->eof());
        $this->fileAccess->closeFile();
    }

    public function testReadFileSucceedsWithMultipleReads(): void
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

    public function testEofIsNullWhenFileIsNotOpen(): void
    {
        self::assertNull($this->fileAccess->eof());
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testWriteReadFileSucceeds(): void
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

    public function testFileGetContentsSucceeds(): void
    {
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->getFileContents($this->fixtureFile));
    }

    public function testFileGetContentsFailsWhenHandleAlreadySet(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFileAdditional, $mode));
        self::assertFalse($this->fileAccess->getFileContents($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testGetFileContentsFailsWithInsufficientPermissions(): void
    {
        $this->vfsFile->chmod(0000);
        self::assertFalse($this->fileAccess->getFileContents($this->fixtureFile));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFilePutContentsFileGetContentsSucceeds(): void
    {
        $contents = 'this is some string.';
        self::assertTrue($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertEquals($contents, $this->fileAccess->getFileContents($this->fixtureFile));
    }

    public function testPutFileContentsFailsWhenFileIsAlreadyOpen(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testPutFileContentsFailsWithInsufficientPermissions(): void
    {
        $this->vfsFile->chmod(0000);
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixtureFile, $contents));
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileGetLineFailsWhenFileIsNotOpen(): void
    {
        self::assertFalse($this->fileAccess->fileGetLine());
        self::assertInstanceOf(FileAccessMsg::class, $this->fileAccess->getFileAccessMsg());
    }

    public function testFileGetLineSucceeds(): void
    {
        $expectedResult = 'some php content';
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixtureFile, $mode));
        self::assertEquals($expectedResult, $this->fileAccess->fileGetLine());
        $this->fileAccess->closeFile();
    }
}
