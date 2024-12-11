<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\filesys;

use SplFileInfo;

/**
 * Class FileInfo
 */
class FileInfo
{
    protected SplFileInfo $splFileInfo;

    public function __construct(string $pathName)
    {
        $this->splFileInfo = new SplFileInfo($pathName);
    }

    public function getPathName(): string
    {
        return $this->splFileInfo->getPathname();
    }
}
