<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\storage\filesys\file_access;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\msg\MsgInterface;
use pvc\storage\filesys\err\FileAccessException;
use pvc\storage\filesys\err\FileHandleException;
use pvc\storage\filesys\err\InvalidFileModeException;
use pvc\storage\filesys\err\InvalidReadLengthException;
use pvc\storage\filesys\err\OpenFileException;
use pvc\storage\filesys\file_access\FileAccess;
use pvcTests\storage\filesys\file_access\fixture\MockFilesysFixture;
use pvcTests\storage\filesys\FileAccessExceptionMsg;

/**
 * @covers FileAccess
 */
class FileAccessTest extends TestCase
{
    protected MsgInterface|MockObject $msg;
    protected FileAccess $fileAccess;
    
    protected MockFilesysFixture $fixture;
    
    public function setUp(): void
    {
        $this->msg = $this->createMock(MsgInterface::class);
        $this->fileAccess = new FileAccess($this->msg);
        $this->fixture = new MockFilesysFixture();
    }

    /**
     * testFileEntryExistsWithNonExistentFile
     * @covers \pvc\storage\filesys\FileAccess::fileEntryExists
     * @covers \pvc\storage\filesys\FileAccess::fileOperation
     */
    public function testFileEntryExistsWithNonExistentFile(): void
    {
        /**
         * test the call to clearContent just once to establish that it will be called every time that
         * the getFileInfo method is called.
         */
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->fileEntryExists($this->fixture->getUrlFileNonExistent()));
    }

    /**
     * testFileEntryExistsWithExistingFile
     * @covers \pvc\storage\filesys\FileAccess::fileEntryExists
     * @covers \pvc\storage\filesys\FileAccess::fileOperation
     */
    public function testFileEntryExistsWithExistingFile(): void
    {
        self::assertTrue($this->fileAccess->fileEntryExists($this->fixture->getUrlFile()));
    }

    /**
     * testFileExistsReturnsFalseWithDir
     * @covers \pvc\storage\filesys\FileAccess::fileExists
     */
    public function testFileExistsReturnsFalseWithDir(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->fileExists($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testFileExistsReturnsTrueWithExistingFile
     * @covers \pvc\storage\filesys\FileAccess::fileEntryExists
     * @covers \pvc\storage\filesys\FileAccess::fileExists
     */
    public function testFileExistsReturnsTrueWithExistingFile(): void
    {
        self::assertTrue($this->fileAccess->fileExists($this->fixture->getUrlFile()));
    }

    /**
     * testFilePermissions
     * @coversNothing
     */
    public function testFilePermissions(): void
    {
        /**
         * default permissions in the mock filesystem for files are set to null,
         * which is equivalent to 0666 (read / write for owner / group / world)
         */
        $expectedPermissions = 0666;
        $vfsFile = $this->fixture->getVfsFile();
        self::assertEquals($expectedPermissions, $vfsFile->getPermissions());
        $newPermissions = 0444;
        $vfsFile->chmod($newPermissions);
        self::assertEquals($newPermissions, $vfsFile->getPermissions());
    }

    /**
     * testFileIsNotReadableWhenDoesNotExist
     * @covers \pvc\storage\filesys\FileAccess::fileIsReadable
     */
    public function testFileIsNotReadableWhenDoesNotExist(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixture->getUrlFileNonExistent()));
    }

    /**
     * testFileIsReadableSucceeds
     * @covers \pvc\storage\filesys\FileAccess::fileIsReadable
     */
    public function testFileIsReadableSucceeds(): void
    {
        self::assertTrue($this->fileAccess->fileIsReadable($this->fixture->getUrlFile()));
    }

    /**
     * testFileIsReadableFails
     * @covers \pvc\storage\filesys\FileAccess::fileIsReadable
     */
    public function testFileIsReadableFails(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsFile()->chmod(0000);
        self::assertFalse($this->fileAccess->fileIsReadable($this->fixture->getUrlFile()));
    }

    /**
     * testFileIsNotWriteableWhenDoesNotExist
     * @covers \pvc\storage\filesys\FileAccess::fileIsWriteable
     */
    public function testFileIsNotWriteableWhenDoesNotExist(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixture->getUrlFileNonExistent()));
    }

    /**
     * testFileIsWriteableSucceeds
     * @covers \pvc\storage\filesys\FileAccess::fileIsWriteable
     */
    public function testFileIsWriteableSucceeds(): void
    {
        self::assertTrue($this->fileAccess->fileIsWriteable($this->fixture->getUrlFile()));
    }

    /**
     * testFileIsWriteableFails
     * @covers \pvc\storage\filesys\FileAccess::fileIsWriteable
     */
    public function testFileIsWriteableFails(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsFile()->chmod(0444);
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixture->getUrlFile()));
    }

    /**
     * testFileIsWriteableFailsWhenIsADirectory
     * @covers \pvc\storage\filesys\FileAccess::fileIsWriteable
     */
    public function testFileIsWriteableFailsWhenIsADirectory(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->fileIsWriteable($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryExists
     * @covers \pvc\storage\filesys\FileAccess::directoryExists
     */
    public function testDirectoryExists(): void
    {
        self::assertTrue($this->fileAccess->directoryExists($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryExistsFailsOnBadDirectory
     * @covers \pvc\storage\filesys\FileAccess::directoryExists
     */
    public function testDirectoryExistsFailsOnBadDirectory(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryExists($this->fixture->getUrlDirectoryNonExistent()));
    }

    /**
     * testDirectoryExistsFailsWhenGivenAFile
     * @covers \pvc\storage\filesys\FileAccess::directoryExists
     */
    public function testDirectoryExistsFailsWhenGivenAFile(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryExists($this->fixture->getUrlFile()));
    }

    /**
     * testDirectoryIsReadableSucceeds
     * @covers \pvc\storage\filesys\FileAccess::directoryIsReadable
     */
    public function testDirectoryIsReadableSucceeds(): void
    {
        self::assertTrue($this->fileAccess->directoryIsReadable($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryIsReadableFailsForLackOfPermissions
     * @covers \pvc\storage\filesys\FileAccess::directoryIsReadable
     */
    public function testDirectoryIsReadableFailsForLackOfPermissions(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsDirectory()->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryIsReadableFailsWhenIsAFile
     * @covers \pvc\storage\filesys\FileAccess::directoryIsReadable
     */
    public function testDirectoryIsReadableFailsWhenIsAFile(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixture->getUrlFile()));
    }

    /**
     * testDirectoryIsReadableFailsWhenDoesNotExist
     * @covers \pvc\storage\filesys\FileAccess::directoryIsReadable
     */
    public function testDirectoryIsReadableFailsWhenDoesNotExist(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryIsReadable($this->fixture->getUrlDirectoryNonExistent()));
    }

    /**
     * testDirectoryIsWriteableSucceeds
     * @covers \pvc\storage\filesys\FileAccess::directoryIsWriteable
     */
    public function testDirectoryIsWriteableSucceeds(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        self::assertTrue($this->fileAccess->directoryIsWriteable($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryIsWriteableFailsForLackOfPermissions
     * @covers \pvc\storage\filesys\FileAccess::directoryIsWriteable
     */
    public function testDirectoryIsWriteableFailsForLackOfPermissions(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsDirectory()->chmod(0000);
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixture->getUrlDirectoryWithFiles()));
    }

    /**
     * testDirectoryIsWriteableFailsWhenIsAFile
     * @covers \pvc\storage\filesys\FileAccess::directoryIsWriteable
     */
    public function testDirectoryIsWriteableFailsWhenIsAFile(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixture->getUrlFile()));
    }

    /**
     * testDirectoryIsWriteableFailsWhenDoesNotExist
     * @covers \pvc\storage\filesys\FileAccess::directoryIsWriteable
     */
    public function testDirectoryIsWriteableFailsWhenDoesNotExist(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryIsWriteable($this->fixture->getUrlDirectoryNonExistent()));
    }

    /**
     * testGetDirectoryContentsWhenDirectoryIsNotReadable
     * @covers \pvc\storage\filesys\FileAccess::directoryGetContents
     */
    public function testDirectoryGetContentsWhenDirectoryIsNotReadable(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        self::assertFalse($this->fileAccess->directoryGetContents($this->fixture->getUrlDirectoryNonExistent()));
    }

    /**
     * testGetDirectoryContentsReturnsFileNames
     * @covers \pvc\storage\filesys\FileAccess::directoryGetContents
     */
    public function testGetDirectoryContentsReturnsFileNames(): void
    {
        $directoryContents = $this->fileAccess->directoryGetContents($this->fixture->getUrlDirectoryWithFiles());
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory */
        self::assertEquals($this->fixture->getExpectedNumberOfDirectoryEntriesWithoutDots(), count($directoryContents));
    }

    /**
     * testGetDirectoryContentsReturnsEmptyArrayForEmptyDirectory
     * @throws FileAccessException
     */
    public function testGetDirectoryContentsReturnsEmptyArrayForEmptyDirectory(): void
    {
        $directoryContents = $this->fileAccess->directoryGetContents($this->fixture->getUrlDirectoryEmpty());
        self::assertIsArray($directoryContents);
        /* "." and ".." are not returned as part of listing the contents of the directory */
        self::assertEquals(0, count($directoryContents));
    }

    /**
     * testFileGetContentsSucceeds
     * @covers \pvc\storage\filesys\FileAccess::fileGetContents
     */
    public function testFileGetContentsSucceeds(): void
    {
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->fileGetContents($this->fixture->getUrlFile()));
    }

    /**
     * testGetFileContentsFailsWithInsufficientPermissions
     * @covers \pvc\storage\filesys\FileAccess::fileGetContents
     */
    public function testGetFileContentsFailsWithInsufficientPermissions(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsFile()->chmod(0000);
        self::assertFalse($this->fileAccess->fileGetContents($this->fixture->getUrlFile()));
    }

    /**
     * testFilePutContentsFileGetContentsSucceeds
     * @covers \pvc\storage\filesys\FileAccess::filePutContents
     */
    public function testFilePutContentsFileGetContentsSucceeds(): void
    {
        $contents = 'this is some string.';
        self::assertEquals(
            strlen($contents),
            $this->fileAccess->filePutContents($this->fixture->getUrlFile(), $contents)
        );
    }

    /**
     * testPutFileContentsFailsWithInsufficientPermissions
     * @covers \pvc\storage\filesys\FileAccess::filePutContents
     */
    public function testPutFileContentsFailsWithInsufficientPermissions(): void
    {
        $this->msg->expects($this->once())->method('setContent');
        $this->fixture->getVfsFile()->chmod(0000);
        $contents = 'this is some string.';
        self::assertFalse($this->fileAccess->filePutContents($this->fixture->getUrlFile(), $contents));
    }

    /**
     * testOpenFileThrowsExceptionWithBadFileMode
     * @covers \pvc\storage\filesys\FileAccess::openFile
     */
    public function testOpenFileThrowsExceptionWithBadFileMode(): void
    {
        $badMode = 'g-';
        self::expectException(InvalidFileModeException::class);
        $this->fileAccess->openFile($this->fixture->getUrlFile(), $badMode);
    }

    /**
     * testOpenFileFailsWhenPermissionsAreInsufficient
     * @throws OpenFileException
     * @covers \pvc\storage\filesys\FileAccess::openFile
     *
     * fopen raises a warning even for something simple like not having the right permissions to open the file
     */
    public function testOpenFileFailsWhenPermissionsAreInsufficient(): void
    {
        $this->fixture->getVfsFile()->chmod(0000);
        $mode = 'r';
        self::assertFalse($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertIsString($this->fileAccess->getErrorMsgText());
    }

    /**
     * testOpenFileSucceedsAndThenCloseFileSucceeds(): void
     * @covers \pvc\storage\filesys\FileAccess::openFile
     * @covers \pvc\storage\filesys\FileAccess::closeFile
     */
    public function testOpenFileSucceedsAndThenCloseFileSucceeds(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertTrue($this->fileAccess->closeFile());
    }

    /**
     * testCloseFileFailsBecauseFileIsNotOpen
     * @covers \pvc\storage\filesys\FileAccess::closeFile
     */
    public function testCloseFileFailsBecauseFileIsNotOpen(): void
    {
        self::expectException(FileAccessException::class);
        $this->fileAccess->closeFile();
    }

    /**
     * testReadFileFailsWhenFileWasNotOpened
     * @throws FileHandleException
     * @throws InvalidReadLengthException
     * @covers \pvc\storage\filesys\FileAccess::readFile
     */
    public function testReadFileFailsWhenFileWasNotOpened(): void
    {
        self::expectException(FileHandleException::class);
        // default length is 8096 and we will not specify something different
        self::assertFalse($this->fileAccess->readFile());
    }

    /**
     * testReadFileInvalidLength
     * @throws InvalidReadLengthException
     * @covers \pvc\storage\filesys\FileAccess::readFile
     */
    public function testReadFileFailsWithInvalidLengthParameter(): void
    {
        $mode = 'r';
        $this->fileAccess->openFile($this->fixture->getUrlFile(), $mode);

        // length must be positive
        $badLength = 0;
        self::expectException(InvalidReadLengthException::class);
        self::assertFalse($this->fileAccess->readFile($badLength));
    }


    /**
     * testReadFileSucceedsAndEOFIsTrue
     * @throws FileAccessException
     * @throws FileHandleException
     * @throws InvalidReadLengthException
     * @throws OpenFileException
     * @covers \pvc\storage\filesys\FileAccess::readFile
     */
    public function testReadFileSucceedsAndEOFIsTrue(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        $expectedResult = 'some php content';
        self::assertEquals($expectedResult, $this->fileAccess->readFile());
        self::assertTrue($this->fileAccess->eof());
        $this->fileAccess->closeFile();
    }

    /**
     * testReadFileSucceedsWithMultipleReads
     * @throws FileAccessException
     * @throws FileHandleException
     * @throws InvalidReadLengthException
     * @throws OpenFileException
     * @covers \pvc\storage\filesys\FileAccess::readFile
     */
    public function testReadFileSucceedsWithMultipleReads(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        $expectedResult = 'some php content';
        $length = 2;
        $actualResult = '';
        while (!$this->fileAccess->eof()) {
            $actualResult .= $this->fileAccess->readFile($length);
        }
        $this->fileAccess->closeFile();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * testEofFailsWhenFileIsNotOpen
     * @throws FileAccessException
     * @covers \pvc\storage\filesys\FileAccess::eof
     */
    public function testEofFailsWhenFileIsNotOpen(): void
    {
        self::expectException(FileHandleException::class);
        self::assertFalse($this->fileAccess->eof());
    }

    /**
     * testWriteFileFailsOnFileWhichWasNotOpened
     * @covers \pvc\storage\filesys\FileAccess::writeFile
     */
    public function testWriteFileFailsOnFileWhichWasNotOpened(): void
    {
        self::expectException(FileHandleException::class);
        self::assertFalse($this->fileAccess->writeFile('some text'));
    }

    /**
     * testWriteFileFailsWhenFileIsNotWriteable
     * @throws FileHandleException
     * @throws OpenFileException
     * @covers \pvc\storage\filesys\FileAccess::writeFile
     */
    public function testWriteFileFailsWhenFileIsNotWriteable(): void
    {
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertFalse($this->fileAccess->writeFile('some text'));
    }

    public function testWriteFileSucceeds(): void
    {
        $mode = 'w';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertTrue($this->fileAccess->writeFile('some text'));
        self::assertTrue($this->fileAccess->closeFile());
    }

    public function testWriteReadFileSucceeds(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');

        $testData = 'some text';

        $mode = 'w';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertTrue($this->fileAccess->writeFile($testData));
        $this->fileAccess->closeFile();

        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertEquals($testData, $this->fileAccess->readFile());
        $this->fileAccess->closeFile();
    }



    public function testFileGetLineFailsWhenFileIsNotOpen(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');

        self::assertFalse($this->fileAccess->fileGetLine());
        self::assertInstanceOf(FileAccessExceptionMsg::class, $this->fileAccess->getFileAccessErrmsg());
    }

    public function testFileGetLineSucceeds(): void
    {
        $this->msg->expects($this->once())->method('clearContent');
        $this->msg->expects($this->once())->method('setContent');

        $expectedResult = 'some php content';
        $mode = 'r';
        self::assertTrue($this->fileAccess->openFile($this->fixture->getUrlFile(), $mode));
        self::assertEquals($expectedResult, $this->fileAccess->fileGetLine());
        $this->fileAccess->closeFile();
    }
}
