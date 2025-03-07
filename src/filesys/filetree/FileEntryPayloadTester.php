<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\filesys\filetree;

use pvc\interfaces\struct\payload\PayloadTesterInterface;

/**
 * Class FileEntryPayloadTester
 */
class FileEntryPayloadTester implements PayloadTesterInterface
{
    /**
     * testValue
     * @param mixed $value
     * @return bool
     */
    public function testValue(mixed $value): bool
    {
        return ($value instanceof FileInfo);
    }
}