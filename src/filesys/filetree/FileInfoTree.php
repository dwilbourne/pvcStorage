<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\storage\err\FilePathDoesNotExistException;
use pvc\struct\collection\factory\CollectionUnorderedFactory;
use pvc\struct\tree\node\factory\TreenodeUnorderedFactory;
use pvc\struct\tree\tree\TreeUnordered;

/**
 * Class FileInfoTree
 * @extends TreeUnordered<FileInfoNode>
 */
class FileInfoTree extends TreeUnordered
{
    public function __construct()
    {
        $collectionFactory = new CollectionUnorderedFactory();
        $nodeFactory = new TreenodeUnorderedFactory($collectionFactory);
        $treeId = 1;
        parent::__construct($treeId, $nodeFactory);
    }

    public function findFiles(string $dir, SearchInterface $search): array
    {
        if (!is_dir($dir)) {
            throw new FilePathDoesNotExistException($dir);
        }

        $fileInfo = FileInfoNodeFactory::makeFileInfoNode($dir, null);
        $search->setStartNode($fileInfo);
        return $search->getNodes();
    }
}
