<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\struct\tree\search\NodeSearchableInterface;
use pvc\storage\filesys\FileInfo;
use pvc\struct\tree\dto\TreenodeDTOUnordered;

/**
 * Class FileInfoNode
 */
readonly class FileInfoNode extends TreenodeDTOUnordered implements NodeSearchableInterface
{
    public function __construct(int $nodeId, ?int $parentId, FileInfo $fileInfo)
    {
        $array = [
            'nodeId' => $nodeId,
            'parentId' => $parentId,
            'treeId' => null,
            'payload' => $fileInfo,
        ];
        $this->hydrateFromArray($array);
    }

    /**
     * getNodeId
     * @return int
     * this method is required by NodeSearchableInterface
     */
    public function getNodeId(): int
    {
        return $this->nodeId;
    }

    public function getChildrenAsArray(): array
    {
        $result = [];
        if (is_dir($this->getPathName())) {
            $fileNames = array_diff(scandir($this->getPathName(), SCANDIR_SORT_NONE), ['.', '..']);
            $filePaths = array_map([$this, 'makeFullPath'], $fileNames);
            foreach ($filePaths as $filePath) {
                $result[] = FileInfoNodeFactory::makeFileInfoNode($filePath, $this->nodeId);
            }
        }
        return $result;
    }

    private function makeFullPath(string $fileName): string
    {
        return $this->getPathName() . '/' . $fileName;
    }

    public function getPathName(): string
    {
        return $this->payload->getPathname();
    }
}
