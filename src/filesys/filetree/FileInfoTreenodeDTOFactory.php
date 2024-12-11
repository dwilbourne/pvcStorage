<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\storage\filesys\FileInfoFactoryInterface;
use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\storage\err\FilePathDoesNotExistException;
use pvc\storage\filesys\FileInfoFactory;
use pvc\struct\tree\search\SearchBreadthFirst;

/**
 * Class FileInfoTreenodeDTOFactory
 */
class FileInfoTreenodeDTOFactory
{
    protected static FileInfoTreenodeDTOFactory $instance;

    protected static FileInfoFactoryInterface $fileInfoFactory;

    protected static SearchInterface $search;

    protected static int $nextNodeId = 0;

    protected static function getNextNodeId(): int
    {
        return self::$nextNodeId++;
    }

    /**
     * make this object a singleton
     */
    protected function __construct() {}

    /**
     * setFileInfofactory
     * @param FileInfoFactoryInterface $fileInfoFactory
     * do not like to do this but because it's a singleton we cannot put this dependency in the constructor.  So this
     * object should be created with setter injection in order to ensure valid state.
     */
    public static function setFileInfofactory(FileInfoFactoryInterface $fileInfoFactory): void
    {
        self::$fileInfoFactory = $fileInfoFactory;
    }

    public static function setSearch(SearchInterface $search): void
    {
        self::$search = $search;
    }

    public static function getInstance(): FileInfoTreenodeDTOFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new FileInfoTreenodeDTOFactory();
        }
        return self::$instance;
    }

    public static function makeFileInfoNode(
        string $pathName,
        ?int $parentId,
    ): FileInfoTreenodeDTO {
        $fileInfoDTO = new FileInfoTreenodeDTO(self::$fileInfoFactory ?? new FileInfoFactory());
        $array = [
            'nodeId' => self::getNextNodeId(),
            'parentId' => $parentId,
            'treeId' => null,
            'payload' => self::$fileInfoFactory->makeFileInfo($pathName),
        ];
        $fileInfoDTO->hydrateFromArray($array);
        return $fileInfoDTO;
    }

    /**
     * findFiles
     * @param string $dir
     * @param SearchInterface<FileInfoTreenodeDTO> $search
     * @return array
     * @throws FilePathDoesNotExistException
     */
    public static function findFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            throw new FilePathDoesNotExistException($dir);
        }

        $fileInfo = self::makeFileInfoNode($dir, null);
        $search = self::$search ?? new SearchBreadthFirst();
        $search->setStartNode($fileInfo);
        return $search->getNodes();
    }
}
