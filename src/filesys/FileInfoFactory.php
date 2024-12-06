<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\filesys;

use _PHPStan_28dfac80b\Symfony\Component\Finder\SplFileInfo;

/**
 * Class FileInfoFactory
 */
class FileInfoFactory
{
    protected static FileInfoFactory $instance;
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

    public static function getInstance(): FileInfoFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new FileInfoFactory();
        }
        return self::$instance;
    }

    public static function makeFileInfo(string $pathName): FileInfo
    {
        $fileInfo = new FileInfo(self::getInstance());
        $fileInfo->hydrate(self::getNextNodeId(), $pathName);
        return $fileInfo;
    }
}
