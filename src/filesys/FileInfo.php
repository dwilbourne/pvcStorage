<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys;

use Exception;
use pvc\interfaces\struct\tree\search\NodeSearchableInterface;
use pvc\storage\err\FileInfoException;
use pvc\storage\err\FilePathDoesNotExistException;
use SplFileInfo;

/**
 * Class FileInfo
 */
class FileInfo implements NodeSearchableInterface
{
    /**
     * @var FileInfoFactory
     */
    protected FileInfoFactory $fileInfoFactory;

    /**
     * @var non-negative-int
     */
    protected int $nodeId;

    /**
     * @var SplFileInfo
     */
    protected SplFileInfo $splFileInfo;

    public function __construct(FileInfoFactory $fileInfoFactory)
    {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * @param non-negative-int $nodeId
     * @param string $filePath
     * @throws FilePathDoesNotExistException
     */
    public function hydrate(int $nodeId, string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new FilePathDoesNotExistException($filePath);
        }
        $this->nodeId = $nodeId;
        $this->splFileInfo = new SplFileInfo($filePath);
    }

    /**
     * getSplFileInfo
     * @return SplFileInfo
     */
    protected function getSplFileInfo(): ?SplFileInfo
    {
        return $this->splFileInfo;
    }

    /**
     * getNodeId
     * @return int
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }

    /**
     * getChildrenAsArray
     * @return array
     */
    public function getChildrenAsArray(): array
    {
        if ($this->splFileInfo->isDir()) {
            return array_diff(scandir($this->splFileInfo->getPathname()), array('..', '.'));
        } else {
            return [];
        }
    }
}
