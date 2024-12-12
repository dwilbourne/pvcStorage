<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\storage\filesys\FileInfoFactoryInterface;
use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\storage\err\FilePathDoesNotExistException;
use pvc\storage\filesys\FileInfo;
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
     * make this object a singleton becausse it requires configuration and it would be easy to forget to configure it
     * with multiple instances.  The default payload is the FileInfo object in this library (which is a facade for
     * SplFileInfo), but your project might need some other kind of file-based payload.
     * require
     */
    protected function __construct()
    {
    }

    /**
     * setFileInfoFactory
     * @param FileInfoFactoryInterface $fileInfoFactory
     * do not like to do this but because it's a singleton we cannot put this dependency in the constructor.  So this
     * object should be created with setter injection in order to ensure valid state.
     */
    public static function setFileInfoFactory(FileInfoFactoryInterface $fileInfoFactory): void
    {
        self::$fileInfoFactory = $fileInfoFactory;
    }

    /**
     * setSearch
     * @param SearchInterface<FileInfo> $search
     */
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

    public static function makeFileInfoTreenodeDTO(
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
     * @return array<FileInfoTreenodeDTO>
     * @throws FilePathDoesNotExistException
     */
    public static function findFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            throw new FilePathDoesNotExistException($dir);
        }

        $fileInfo = self::makeFileInfoTreenodeDTO($dir, null);
        $search = self::$search ?? new SearchBreadthFirst();
        $search->setStartNode($fileInfo);
        return $search->getNodes();
    }
}
