<?php
namespace tests\filesys;

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

use bovigo\vfs\vfsStreamContent;
use PHPUnit\Framework\TestCase;
use pvc\filesys\FileAccess;
use tests\filesys\fixture\MockFilesysFixture;
use Exception;

/**
 * Class FileAccessTestCase
 */
class FileAccessTestCase extends TestCase
{
    protected FileAccess $fileAccess;
    protected MockFilesysFixture $mockFilesysFixture;
    protected vfsStreamContent $vfsFile;
    protected vfsStreamContent $vfsDirectory;

    protected string $fixtureFile;
    protected string $fixtureFileAdditional;
    protected string $fixtureFileNonExistent;

    protected string $fixtureDirectory;
    protected int $expectedNumberOfDirectoryEntriesWithoutDotsAndWithoutRecursing;
    protected int $expectedNumberOfDirectoryEntriesWithoutDotsAndWithRecursing;
    protected string $fixtureDirectoryEmpty;
    protected string $fixtureDirectoryNonExistent;


    public function setUp(): void
    {
        $this->fileAccess = new FileAccess();

        $this->mockFilesysFixture = new MockFilesysFixture();

        $filesys = $this->mockFilesysFixture->getVfsFilesys();

        $dir = $filesys->getChild('Subdir_1');
        if (is_null($dir)) {
            throw new Exception($this->makeErrMsg());
        }
        $this->vfsDirectory = $dir;

        $file = $filesys->getChild('Subdir_1/somecode.php');
        if (is_null($file)) {
            throw new Exception($this->makeErrMsg());
        }
        $this->vfsFile = $file;

        $this->fixtureDirectory = $this->vfsDirectory->url();
        $this->expectedNumberOfDirectoryEntriesWithDotsAndWithoutRecursing = 6;
        $this->expectedNumberOfDirectoryEntriesWithoutDotsAndWithRecursing = 8;
        $dir = $filesys->getChild('Subdir_1/AnEmptyFolder');
        $this->fixtureDirectoryEmpty = $dir->url();
        $this->fixtureDirectoryNonExistent = 'bar';

        $this->fixtureFile = $this->vfsFile->url();

        $fileAdditional = $filesys->getChild('Subdir_1/somejavascript.js');
        if (is_null($fileAdditional)) {
            throw new Exception($this->makeErrMsg());
        }

        $this->fixtureFileAdditional = $fileAdditional->url();
        $this->fixtureFileNonExistent = 'foo';
    }

    protected function makeErrMsg() : string
    {
        return 'unable to create mock file system.';
    }
}
