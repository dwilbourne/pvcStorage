<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys;

use pvc\interfaces\storage\filesys\FileInfoFactoryInterface;

/**
 * Class FileInfoFactory
 */
class FileInfoFactory implements FileInfoFactoryInterface
{
    public function makeFileInfo(string $pathName): FileInfo
    {
        return new FileInfo($pathName);
    }
}
