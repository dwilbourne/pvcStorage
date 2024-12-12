<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use Override;
use pvc\interfaces\struct\payload\PayloadTesterInterface;
use pvc\storage\filesys\FileInfo;

/**
 * Class FileEntryPayloadTester
 */
class FileEntryPayloadTester implements PayloadTesterInterface
{

    #[Override] public function testValue(mixed $value): bool
    {
        return ($value instanceof FileInfo);
    }
}