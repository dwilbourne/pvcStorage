<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\storage\filesys\FileEntryFactoryInterface;
use pvc\interfaces\struct\tree\node_value_object\factory\TreenodeValueObjectOrderedFactoryInterface;
use pvc\storage\filesys\FileTreeDataSrc;

class FileTreeDataSrcUnitTest extends TestCase
{
    protected FileTreeDataSrc $dataSrc;

    protected FileEntryFactoryInterface|MockObject $fileEntryFactory;
    protected TreenodeValueObjectOrderedFactoryInterface|MockObject $valueObjectFactory;


    public function setUp(): void
    {
        $this->valueObjectFactory = $this->createMock(TreenodeValueObjectOrderedFactoryInterface::class);
        $this->fileEntryFactory = $this->createMock(FileEntryFactoryInterface::class);
        $this->dataSrc = new FileTreeHydrator($this->fileEntryFactory, $this->valueObjectFactory);
    }

    /**
     * testConstruct
     * @covers \pvc\storage\filesys\FileTreeDataSrc::__construct
     * @covers \pvc\storage\filesys\FileTreeDataSrc::setSplFileInfoClass
     * @covers \pvc\storage\filesys\FileTreeDataSrc::getSplFileInfoClass
     * @covers \pvc\storage\filesys\FileTreeDataSrc::setTreenodeValueObjectFactory
     * @covers \pvc\storage\filesys\FileTreeDataSrc::getTreenodeValueObjectFactory
     */
    public function testConstruct(): void
    {
        self::assertInstanceOf(FileTreeHydrator::class, $this->dataSrc);
        self::assertEquals($this->fileEntryFactory, $this->dataSrc->getFileEntryFactory());
        self::assertEquals($this->valueObjectFactory, $this->dataSrc->getTreenodeValueObjectFactory());
    }

    /**
     * testSetGetTreeId
     * @covers \pvc\storage\filesys\FileTreeDataSrc::setTreeId
     * @covers \pvc\storage\filesys\FileTreeDataSrc::getTreeId
     */
    public function testSetGetTreeId(): void
    {
        $treeId = 5;
        $this->dataSrc->setTreeId($treeId);
        self::assertEquals($treeId, $this->dataSrc->getTreeId());
    }
}
