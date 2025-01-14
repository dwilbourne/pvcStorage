<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\storage\filesys\FileInfoInterface;
use SplFileInfo;

/**
 * Class FileInfo
 */
class FileInfo implements FileInfoInterface
{
    protected SplFileInfo $splFileInfo;

    public function __construct(string $pathName)
    {
        $this->splFileInfo = new SplFileInfo($pathName);
    }

    public function getFilePath(): string
    {
        return $this->splFileInfo->getPathname();
    }
}
