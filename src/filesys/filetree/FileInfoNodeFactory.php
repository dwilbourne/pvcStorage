<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\storage\filesys\FileInfo;

/**
 * Class FileInfoNodeFactory
 */
class FileInfoNodeFactory
{
    protected static FileInfoNodeFactory $instance;
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

    public static function getInstance(): FileInfoNodeFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new FileInfoNodeFactory();
        }
        return self::$instance;
    }

    public static function makeFileInfoNode(string $pathName, ?int $parentId): FileInfoNode
    {
        return new FileInfoNode(self::getNextNodeId(), $parentId, new FileInfo($pathName));
    }
}
