<?php

declare(strict_types=1);

namespace pvcTests\storage\filesys;

use Exception;
use PHPUnit\Framework\TestCase;
use pvc\storage\filesys\err\FileDoesNotExistException;
use pvc\storage\filesys\err\FileGetContentsException;
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

    protected function setUp(): void
    {
        $this->fixture = new MockFilesysFixture();
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @covers \pvc\storage\filesys\File::mustExist
     */
    public function testMustExistFailsWhenFileDoesNotExist(): void
    {
        $nonExistentFile = 'someBadFile.txt';
        $this->expectException(FileDoesNotExistException::class);
        File::mustExist($nonExistentFile);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @covers \pvc\storage\filesys\File::mustExist
     */
    public function testMustExistSucceeds(): void
    {
        $testFile = $this->fixture->getUrlFile();
        self::assertTrue(File::mustExist($testFile));
    }

    /**
     * @return void
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::mustBeReadable
     */
    public function testFileMustBeReadableFailsWhenFileIsNotReadable(): void
    {
        $testFile = $this->fixture->getUrlFile();
        /**
         * allow only write permissions to the file
         */
        chmod($testFile, 0222);
        self::expectException(FileNotReadableException::class);
        File::mustBeReadable($testFile);
    }

    /**
     * @return void
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::mustBeReadable
     */
    public function testFileMustBeReadableSucceeds(): void
    {
        $testFile = $this->fixture->getUrlFile();
        self::assertTrue(File::mustBeReadable($testFile));
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
        chmod($testFile, 0111);
        self::expectException(FileOpenException::class);
        $handle = File::open($testFile, FileMode::WRITE);
        unset($handle);
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::open
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
    public function testOpenReadonlySucceeds(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $handle = File::openReadOnly($testFile);
        self::assertTrue(is_resource($handle));
        self::assertTrue(get_resource_type($handle) === 'stream');
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileNotReadableException
     * @throws FileGetContentsException
     * @covers \pvc\storage\filesys\File::getContents
     */
    public function testGetContentsSucceeds(): void
    {
        $testFile = $this->fixture->getUrlFile();
        $contents = File::getContents($testFile);
        self::assertTrue(is_string($contents));
    }

    /**
     * @return void
     * @covers \pvc\storage\filesys\File::getContents
     * @runInSeparateProcess
     */
    public function testGetContentsFailsIfVerbReturnsFalse(): void
    {
        $testFile = $this->fixture->getUrlFile();
        /**
         * exception processing requires is_readable and file_get_contents to work normally, so need a closure to
         * restrict the result to the test case
         */
        $callback = function (string $filePath) use ($testFile) {
            if ($filePath === $testFile) {
                return false;
            } else {
                return file_get_contents($filePath);
            }
        };
        uopz_set_return('file_get_contents', $callback, true);
        self::expectException(FileGetContentsException::class);
        $contents = File::getContents($testFile);
        unset($contents);
        uopz_unset_return('file_get_contents');
    }

    /**
     * @return void
     * @throws FileDoesNotExistException
     * @throws FileGetContentsException
     * @throws FileNotReadableException
     * @covers \pvc\storage\filesys\File::getContents
     */
    public function testGetContentsFailsIfVerbErrors(): void
    {
        $testFile = $this->fixture->getUrlFile();
        /**
         * exception processing requires is_readable and file_get_contents to work normally, so need a closure to
         * restrict the result to the test case
         */
        $callback = function (string $filePath) use ($testFile) {
            if ($filePath === $testFile) {
                throw new Exception('runtime exception');
            } else {
                return file_get_contents($filePath);
            }
        };
        uopz_set_return('file_get_contents', $callback, true);
        self::expectException(FileGetContentsException::class);
        $contents = File::getContents($testFile);
        unset($contents);
        uopz_unset_return('file_get_contents');
    }
}
