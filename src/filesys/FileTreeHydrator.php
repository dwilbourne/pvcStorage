<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys;

use pvc\interfaces\storage\filesys\FileEntryInterface;
use pvc\interfaces\struct\tree\dto\factory\TreenodeDTOOrderedFactoryInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\storage\err\FilePathDoesNotExistException;
use RecursiveDirectoryIterator;
use SplFileInfo;

/**
 * Class FileTreeHydrator
 *
 * Hydrates a tree with FileInfo objects
 */
class FileTreeHydrator
{
    /**
     * @var TreenodeDTOOrderedFactoryInterface<SplFileInfo>
     */
    protected TreenodeDTOOrderedFactoryInterface $treenodeDTOFactory;

    /**
     * @var non-negative-int
     */
    protected TreeOrderedInterface $tree;


    public function __construct(
        TreenodeDTOOrderedFactoryInterface $treenodeValueObjectFactory,
        TreeOrderedInterface $tree
    ) {
        $this->setTreenodeDTOFactory($treenodeValueObjectFactory);
        $this->setTree($tree);
    }

    /**
     * @return TreeOrderedInterface
     */
    public function getTree(): TreeOrderedInterface
    {
        return $this->tree;
    }

    /**
     * @param TreeOrderedInterface $tree
     */
    public function setTree(TreeOrderedInterface $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * getSplFileInfoClass
     * @return string
     */
    public function getSplFileInfoClass(): string
    {
        return $this->splFileInfoClass;
    }

    /**
     * setSplFileInfoClass
     * @param string $splFileInfoClass
     */
    public function setSplFileInfoClass(string $splFileInfoClass): void
    {
        $this->splFileInfoClass = $splFileInfoClass;
    }

    /**
     * getTreenodeDTOFactory
     * @return TreenodeDTOOrderedFactoryInterface<FileEntryInterface>
     */
    public function getTreenodeDTOFactory(): TreenodeDTOOrderedFactoryInterface
    {
        return $this->treenodeDTOFactory;
    }

    /**
     * setTreenodeDTOFactory
     * @param TreenodeDTOOrderedFactoryInterface<FileEntryInterface> $treenodeValueObjectFactory
     */
    public function setTreenodeDTOFactory(
        TreenodeDTOOrderedFactoryInterface $treenodeValueObjectFactory
    ): void {
        $this->treenodeDTOFactory = $treenodeValueObjectFactory;
    }

    /**
     * addFileNode
     * @param int $nodeId
     * @param int $parentNodeId
     * @param int $treeId
     * @param int $index
     * @param string $filePath
     * @return TreenodeDTOOrderedInterface
     */
    protected function addFileNode(
        int $nodeId,
        ?int $parentNodeId,
        int $index,
        SplFileInfo $fileinfo
    ): void {
        $dto = $this->treenodeDTOFactory->makeDTO();
        $array = [$nodeId, $parentNodeId, $this->getTree()->getTreeId(), $index, $fileinfo];
        $dto->hydrateFromNumericArray($array);
        $this->getTree()->addNode($dto);
    }

    /**
     * hydrate
     *
     * @param string $filePath
     * @throws FilePathDoesNotExistException
     * @return array<TreenodeDTOOrderedInterface>
     */
    public function hydrate(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new FilePathDoesNotExistException($dir);
        }

        $this->tree->initialize();
        $currentNodeId = 0;
        $parentNodeId = null;
        $fileInfo = new SplFileInfo($dir);
        $this->addFileNode($currentNodeId++, $parentNodeId, 0, $fileInfo);

        $iterator = new RecursiveDirectoryIterator($dir);
        if ($this->getSplFileInfoClass()) {
            $iterator->setInfoClass($this->getSplFileInfoClass());
        }

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $this->addFileNode($currentNodeId++, $parentNodeId, 0, $fileInfo);
            }
        }
    }
}
