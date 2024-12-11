<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\storage\filesys\FileInfoFactoryInterface;
use pvc\interfaces\struct\tree\search\SearchInterface;
use pvc\storage\err\FilePathDoesNotExistException;

/**
 * Class FileInfoTreenodeDTOFactory
 */
class FileInfoTreenodeDTOFactory
{
    protected static FileInfoTreenodeDTOFactory $instance;

    protected static FileInfoFactoryInterface $fileInfoFactory;

    protected static int $nextNodeId = 0;

    protected static function getNextNodeId(): int
    {
        return self::$nextNodeId++;
    }

    /**
     * make this object a singleton
     */
    protected function __construct()
    {
    }

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
        FileInfoFactoryInterface $fileInfoFactory
    ): FileInfoTreenodeDTO {
        return new FileInfoTreenodeDTO(self::getNextNodeId(), $parentId, $fileInfoFactory->makeFileInfo($pathName));
    }

    /**
     * findFiles
     * @param string $dir
     * @param SearchInterface<FileInfoTreenodeDTO> $search
     * @return array
     * @throws FilePathDoesNotExistException
     */
    public static function findFiles(string $dir, SearchInterface $search): array
    {
        if (!is_dir($dir)) {
            throw new FilePathDoesNotExistException($dir);
        }

        $fileInfo = self::makeFileInfoNode($dir, null, self::$fileInfoFactory);
        $search->setStartNode($fileInfo);
        return $search->getNodes();
    }
}
