<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\TestCase;
use pvc\storage\filesys\FileInfoNode;
use pvc\storage\filesys\FileInfoTree;
use pvc\struct\tree\search\SearchBreadthFirst;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

class FileInfoTreeTest extends TestCase
{
    protected MockFilesysFixture $fixture;

    protected FileInfoTree $fileTree;

    public function setUp(): void
    {
        $this->fixture = new MockFilesysFixture();
        $this->fileTree = new FileInfoTree();
    }

    public function testFindFilesBreadthFirst(): void
    {
        $expectedResult = $this->fixture->getAllFilesAndDirectoriesBreadthFirst();
        $array = $this->fileTree->findFiles($this->fixture->getVfsRoot(), new SearchBreadthFirst());
        $callback = function (FileInfoNode $fileInfo) {
            return $fileInfo->getPathName();
        };
        $actualResult = array_map($callback, $array);
        self::assertEquals($expectedResult, $actualResult);
    }

    public function testHydrateTree(): void
    {
        $array = $this->fileTree->findFiles($this->fixture->getVfsRoot(), new SearchBreadthFirst());
        $this->fileTree->hydrate($array);
        self::assertEquals(count($this->fixture->getAllFilesAndDirectories()), $this->fileTree->nodeCount());
    }
}
