<?php

namespace pvcTests\storage\filesys;

use PHPUnit\Framework\TestCase;
use pvc\storage\filesys\FileMode;

class FileModeTest extends TestCase
{
    /**
     * @return void
     * @covers \pvc\storage\filesys\FileMode::isDefined
     */
    public function testIsDefinedReturnsTrueWithValidFileMode(): void
    {
        self::assertTrue(FileMode::isDefined(FileMode::APPEND));
    }

    /**
     * @return void
     * @covers \pvc\storage\filesys\FileMode::isDefined
     */
    public function testIsDefinedReturnsFalseWithinvalidFileMode(): void
    {
        self::assertFalse(FileMode::isDefined('u'));
    }

}
