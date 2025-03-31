<?php

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\TestCase;
use pvc\storage\filesys\err\FileDoesNotExistException;
use pvc\storage\filesys\err\FileNotReadableException;
use pvc\storage\filesys\err\FileOpenException;
use pvc\storage\filesys\err\InvalidFileHandleException;
use pvc\storage\filesys\err\InvalidFileModeException;
use pvc\storage\filesys\File;
use pvc\storage\filesys\FileMode;
use pvc\storage\resource\err\InvalidResourceException;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

class FileTest extends TestCase
{
    protected MockFilesysFixture $fixture;

    /**
     * @return void
     * @covers \pvc\storage\filesys\File::open
     */
    public function testOpenThrowsExceptionOpeningNonExistentFile(): void
    {
        $nonExistentFile = 'someBadFile.txt';
        self::expectException(FileDoesNotExistException::class);
        $handle = File::open($nonExistentFile, FileMode::READ);
        unset($handle);
    }

    /**
     * @return void
     * @covers \pvc\storage\filesys\File::open
     */
    public function testOpenThrowsExceptionOpeningNonReadableFileInWriteMode(): void
    {
        $testFile = $this->fixture->getUrlFile();
        /**
         * allow only read permissions to the file
         */
        chmod($testFile, '0111');
        self::expectException(FileOpenException::class);
        $handle = File::open($testFile, FileMode::WRITE);
        unset($handle);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers File::open
     */
    public function testOpenFailsWithInvalidFileMode(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $badFileMode = 'u';
        $this->expectException(InvalidFileModeException::class);
        $handle = File::open($testFile, $badFileMode);
        unset($handle);
    }

    /**
     * @return void
     * @covers \pvc\storage\filesys\File::open
     * @runInSeparateProcess
     */
    public function testOpenThrowsExceptionWhenFopenReturnsFalse(): void
    {
        uopz_set_return('fopen', false);
        $testFile = $this->fixture->getUrlFile();
        self::expectException(FileOpenException::class);
        $handle = File::open($testFile, FileMode::WRITE);
        uopz_unset_return('fopen');
        unset($handle);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::open
     */
    public function testOpenFileSuccessfully(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $handle = File::open($testFile, FileMode::WRITE);
        self::assertTrue(is_resource($handle));
        self::assertTrue(get_resource_type($handle) === 'stream');
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws InvalidResourceException
     * @throws FileNotReadableException
     * @throws InvalidFileHandleException
     * @covers \pvc\storage\filesys\File::close
     */
    public function testCloseFailsWhenResourceIsAlreadyClosed(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $handle = File::open($testFile, FileMode::WRITE);
        File::close($handle);
        self::expectException(InvalidResourceException::class);
        File::close($handle);
    }

    /**
     * @return void
     * @throws InvalidFileHandleException
     * @throws InvalidResourceException
     * @covers \pvc\storage\filesys\File::close
     * @runInSeparateProcess
     */
    public function testCloseFailsWhenResourceIsNotAStreamResource(): void
    {
        /**
         * wrong kind of resource
         */
        $handle = 'some string';
        uopz_set_return('is_resource', true);
        uopz_set_return('get_resource_type', 'Unknown');
        self::expectException(InvalidFileHandleException::class);
        File::close($handle);
        uopz_unset_return('is_resource');
        uopz_unset_return('get_resource_type');
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::openReadOnly
     */
    public function testOpenReadOnlyFailsIfFileDoesNotExist(): void
    {
        $nonExistentFile = 'someBadFile.txt';
        self::expectException(FileDoesNotExistException::class);
        $handle = File::openReadOnly($nonExistentFile, FileMode::READ);
        unset($handle);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::openReadOnly
     */
    public function testOpenReadonlyFailsIfFileExistsButIsNotReadable(): void
    {
        $testFile = $this->fixture->getUrlFile();
        /**
         * allow only write permissions to the file
         */
        chmod($testFile, '0222');
        self::expectException(FileNotReadableException::class);
        $handle = File::openReadOnly($testFile);
        unset($handle);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::openReadOnly
     */
    public function testOpenReadonlySucceeds(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $handle = File::openReadOnly($testFile);
        self::assertTrue(is_resource($handle));
        self::assertTrue(get_resource_type($handle) === 'stream');
    }

    protected function setUp(): void
    {
        $this->fixture = new MockFilesysFixture();
    }
}
