<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\storage\filesys\file_tree;

use PHPUnit\Framework\TestCase;
use pvc\storage\filesys\FileInfoFactory;
use pvc\storage\filesys\filetree\FileInfoTreenodeDTO;
use pvc\storage\filesys\filetree\FileInfoTreenodeDTOFactory;
use pvc\struct\tree\search\SearchBreadthFirst;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

class FileInfoTreenodeDTOFactoryTest extends TestCase
{
    protected MockFilesysFixture $fixture;

    public function setUp(): void
    {
        $this->fixture = new MockFilesysFixture();
    }

    public function testFindFilesBreadthFirst(): void
    {
        $expectedResult = $this->fixture->getAllFilesAndDirectoriesBreadthFirst();

        /**
         * clumsy, should be done in a service container
         */
        $fileInfoFactory = new FileInfoFactory();
        FileInfoTreenodeDTOFactory::setFileInfofactory($fileInfoFactory);

        $array = FileInfoTreenodeDTOFactory::findFiles($this->fixture->getVfsRoot(), new SearchBreadthFirst());
        $callback = function (FileInfoTreenodeDTO $fileInfo) {
            return $fileInfo->getPathName();
        };
        $actualResult = array_map($callback, $array);
        self::assertEquals($expectedResult, $actualResult);
    }
}
