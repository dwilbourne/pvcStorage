<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\storage\filesys\file_access;

use org\bovigo\vfs\vfsStreamContent;
use PHPUnit\Framework\TestCase;
use pvcTests\storage\filesys\fixture\MockFilesysFixture;

/**
 * Class FileAccessTestCase
 */
class FileAccessTestCase extends TestCase
{
    protected MockFilesysFixture $mockFilesysFixture;
    protected vfsStreamContent $vfsFile;
    protected vfsStreamContent $vfsDirectory;
    protected string $fixtureFile;
    protected string $fixtureFileAdditional;
    protected string $fixtureFileNonExistent;
    protected string $fixtureDirectoryWithFiles;
    protected int $expectedNumberOfDirectoryEntriesWithoutDots;
    protected string $fixtureDirectoryEmpty;
    protected string $fixtureDirectoryNonExistent;

    public function setUp(): void
    {
        $this->mockFilesysFixture = new MockFilesysFixture();

        $filesys = $this->mockFilesysFixture->getVfsFileSys();

        $dir = $filesys->getChild('Subdir_1');
        $this->vfsDirectory = $dir;

        $file = $filesys->getChild('Subdir_1/somecode.php');
        $this->vfsFile = $file;

        $this->fixtureDirectoryWithFiles = $this->vfsDirectory->url();
        $this->expectedNumberOfDirectoryEntriesWithoutDots = 4;
        $dir = $filesys->getChild('Subdir_1/AnEmptyFolder');
        $this->fixtureDirectoryEmpty = $dir->url();
        $this->fixtureDirectoryNonExistent = 'bar';

        $this->fixtureFile = $this->vfsFile->url();

        $fileAdditional = $filesys->getChild('Subdir_1/somejavascript.js');
        $this->fixtureFileAdditional = $fileAdditional->url();

        $this->fixtureFileNonExistent = 'foo';
    }
}
