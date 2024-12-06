<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\TestCase;

use pvc\interfaces\storage\filesys\FileEntryFactoryInterface;
use pvc\interfaces\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactoryInterface;
use pvc\storage\filesys\FileTreeDataSrc;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

use function filesys\uopz_set_return;
use function filesys\uopz_unset_return;

class FileTreeDataSrcIntegrationTest extends TestCase
{
    protected MockFilesysFixture $filesysFixture;

    protected FileEntryFactoryInterface $fileEntryFactory;

    protected TreenodeValueObjectOrderedFactoryInterface $valueObjectFactory;

    protected int $treeId;

    protected FileTreeDataSrc $dataSrc;

    public function setUp(): void
    {
        $this->filesysFixture = new MockFilesysFixture();
        $this->fileEntryFactory = new DirectoryEntryFactory();
        $this->valueObjectFactory = new DirectoryEntryValueObjectFactory($this->fileEntryFactory);
        $this->treeId = 1;
        $this->dataSrc = new FileTreeDirectoriesDataSrc($this->valueObjectFactory, $this->treeId);
    }

    /**
     * testCreateValueObjectsWithBadArgThrowsException
     * @throws FilePathDoesNotExistException
     * @throws \pvc\file\err\ScanDirException
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::createValueObjects
     */
    public function testCreateValueObjectsWithBadArgThrowsException(): void
    {
        $badDirName = 'foo';
        self::expectException(FilePathDoesNotExistException::class);
        $this->dataSrc->createValueObjects($badDirName);
    }

    /**
     * @runInSeparateProcess 
     * testCreateValueObjectsThrowsExceptionWhenScandirFails
     * @throws FilePathDoesNotExistException
     * @throws ScanDirException
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::filePathRecurse
     */
    public function testCreateValueObjectsThrowsExceptionWhenScandirFails(): void
    {
        $dirWithNoSubdirs = 'vfs://root/Subdir_2/SmallLibrary';
        $callback = function(string $dir) use ($dirWithNoSubdirs) {
            /**
             * return false if $dir is the test directory, otherwise behave normally.  This special setup is required
             * because the Exception class in pvcErr uses scandir to throw exceptions!
             */
            return ($dir != $dirWithNoSubdirs ? scandir($dir) : false);
        };
        uopz_set_return('scandir', $callback, true);
        self::expectException(ScanDirException::class);
        $this->dataSrc->createValueObjects($dirWithNoSubdirs);
        uopz_unset_return('scandir');
    }

    protected function scandirFailsOnce(): bool
    {

    }

    /**
     * testCreateValueObjectsMethodWithDirThatHasNoSubDirsCreatesArrayOfOneValueObject
     * @throws FilePathDoesNotExistException
     * @throws \pvc\file\err\ScanDirException
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::createValueObjects
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::filePathRecurse
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::getTreenodeValueObjects
     */
    public function testCreateValueObjectsMethodWithDirThatHasNoSubDirsCreatesArrayOfOneValueObject(): void
    {
        $dirWithNoSubdirs = 'vfs://root/Subdir_2/SmallLibrary';
        $this->dataSrc->createValueObjects($dirWithNoSubdirs);
        $valueObjects = $this->dataSrc->getValueObjects();
        self::assertIsArray($valueObjects);
        self::assertEquals(1, count($valueObjects));
        self::assertInstanceOf(DirectoryEntryValueObject::class, $valueObjects[0]);
    }

    /**
     * testCreateValueObjectsMethodCreatesArrayForAllDirsinFixture
     * @throws FilePathDoesNotExistException
     * @throws \pvc\file\err\ScanDirException
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::createValueObjects
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::filePathRecurse
     * @covers \pvc\file\filesys\FileTreeDirectoriesDataSrc::getTreenodeValueObjects
     */
    public function testCreateValueObjectsMethodCreatesArrayForAllDirsinFixture(): void
    {
        $expectedResult = [
            'vfs://root',
            'vfs://root/Subdir_1',
            'vfs://root/Subdir_1/AbstractFactory',
            'vfs://root/Subdir_1/AnEmptyFolder',
            'vfs://root/Subdir_2',
            'vfs://root/Subdir_2/SmallLibrary',
        ];

        $this->dataSrc->createValueObjects($this->filesysFixture->getFilesysRoot());
        foreach($this->dataSrc->getValueObjects() as $valueObject) {
            $actualResult[] = $valueObject->getValue()->getFilePath();
        }

        self::assertEqualsCanonicalizing($expectedResult, $actualResult);
    }
}
