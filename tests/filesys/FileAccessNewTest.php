<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\err\ErrorHandler;
use pvc\interfaces\msg\MsgInterface;
use pvc\storage\err\InvalidFileModeException;
use pvc\storage\err\OpenFileException;
use pvc\storage\filesys\FileAccessNew;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

class FileAccessNewTest extends TestCase
{
    protected MsgInterface|MockObject $msg;

    protected ErrorHandler $errorHandlerClass;

    protected FileAccessNew $fileAccessNew;

    protected MockFilesysFixture $fixture;

    public function setUp(): void
    {
        $this->msg = $this->createMock(MsgInterface::class);
        $this->errorHandlerClass = new ErrorHandler();
        $this->fileAccessNew = new FileAccessNew($this->msg, $this->errorHandlerClass);
        $this->fixture = new MockFilesysFixture();
    }

    public function testOpenFileFailsWithInvalidMode(): void
    {
        $mode = 'g-';
        $fileName = $this->fixture->getUrlFileNonExistent();
        self::expectException(InvalidFileModeException::class);
        $this->fileAccessNew->openFile($fileName, $mode);
    }

    public function testOpenFileFilesWithNonExistentFileInReadMode(): void
    {
        $mode = 'r';
        $fileName = $this->fixture->getUrlFileNonExistent();
        self::assertFalse($this->fileAccessNew->openFile($fileName, $mode));
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
        $fileName = $this->fixture->getUrlFile();
        self::assertFalse($this->fileAccessNew->openFile($fileName, $mode));
    }

}
