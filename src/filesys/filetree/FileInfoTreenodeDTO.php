<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\storage\filesys\FileInfoFactoryInterface;
use pvc\interfaces\storage\filesys\FileInfoInterface;
use pvc\interfaces\struct\collection\CollectionUnorderedInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDTOUnorderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node\TreenodeUnorderedInterface;
use pvc\interfaces\struct\tree\search\NodeSearchableInterface;
use pvc\interfaces\struct\tree\tree\TreeUnorderedInterface;
use pvc\storage\dto\DTOTrait;

/**
 * Class FileInfoTreenodeDTO
 */
readonly class FileInfoTreenodeDTO implements TreenodeDTOUnorderedInterface, NodeSearchableInterface
{
    use DTOTrait;

    public int $nodeId;
    public ?int $parentId;
    /**
     * @var int|null
     * dto is allowed to have a null treeId.  If null, the node hydration method will use the treeId supplied from
     * the tree to which the node belongs.
     */
    public ?int $treeId;

    /**
     * @var FileInfoInterface
     */
    public mixed $payload;

    /**
     * @var FileInfoFactoryInterface
     * used to generate the children array in order to implement NodeSearchable interface
     */
    protected FileInfoFactoryInterface $fileInfoFactory;

    public function __construct($fileInfoFactory)
    {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * hydrateFromNode
     * @phpcs:ignore-next-line
     * @param TreenodeAbstractInterface<FileInfoInterface, TreenodeUnorderedInterface, TreeUnorderedInterface, CollectionUnorderedInterface, TreenodeDTOUnorderedInterface> $node
     */
    public function hydrateFromNode(TreenodeAbstractInterface $node): void
    {
        $this->nodeId = $node->getNodeId();
        $this->parentId = $node->getParentId();
        $this->treeId = $node->getTreeId();
        $this->payload = $node->getPayload();
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
                $result[] = FileInfoTreenodeDTOFactory::makeFileInfoTreenodeDTO($filePath, $this->nodeId);
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
        return $this->payload->getFilePath();
    }
}
