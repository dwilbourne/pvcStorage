<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */
namespace tests\filesys;

use PHPUnit\Framework\TestCase;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\storage\filesys\err\FileSystemException;
use pvc\storage\filesys\FindFiles;
use tests\filesys\fixture\MockFilesysFixture;

/**
 * @covers \pvc\storage\filesys\FindFiles
 */

class FindFilesTest extends TestCase
{
    protected MockFilesysFixture $mockFilesys;
    protected FindFiles $findFiles;
    protected string $mockRoot;

    public function setUp() : void
    {
        $this->mockFilesys = new MockFilesysFixture();
        $this->mockRoot = $this->mockFilesys->getVfsRoot();
        $this->findFiles = new FindFiles();
    }

    public function testFindFilesSearchDirException1() : void
    {
        // is not a directory
        $searchDir = 'foo';
        self::expectException(InvalidArgumentException::class);
        $this->findFiles->findFiles($searchDir);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFindFilesSearchDirException2() : void
    {
        $searchDir = __DIR__;
        uopz_set_return('is_readable', false);
        self::expectException(InvalidArgumentException::class);
        $this->findFiles->findFiles($searchDir);
        uopz_unset_return('is_readable');
    }

    /**
     * @runInSeparateProcess
     */
    public function testFindFilesSearchDirException3() : void
    {
        $searchDir = __DIR__;
        uopz_set_return('opendir', false);
        self::expectException(FileSystemException::class);
        $this->findFiles->findFiles($searchDir);
        uopz_unset_return('open_dir');
    }

    public function testSetGetCallback() : void
    {
        $callback = function () {
        };
        $this->findFiles->setFileFilter($callback);
        self::assertEquals($callback, $this->findFiles->getFileFilter());
    }

    public function testSetGetRecurse() : void
    {
        self::assertEquals(true, $this->findFiles->getRecurse());
        $this->findFiles->setRecurse(false);
        self::assertFalse($this->findFiles->getRecurse());
    }


    public function testFindFileAll() : void
    {
        $expectedResult = $this->mockFilesys->getAllFilesFixture();
        $actualResult = $this->findFiles->findFiles($this->mockRoot);
        static::assertSame($expectedResult, $actualResult);
    }

    public function testFindFilePhp() : void
    {
        $expectedResult = $this->mockFilesys->getPhpFilesFixture();
		$callback = function(string $filename) {
			$pattern = '/\.php$/';
			return preg_match($pattern, $filename, $matches);
		};
		$this->findFiles->setFileFilter($callback);
        $actualResult = $this->findFiles->findFiles($this->mockRoot);
        static::assertSame($expectedResult, $actualResult);
    }

    public function testDeleteFile() : void
    {

        $countAllFiles = count($this->mockFilesys->getAllFilesFixture());
        $countJsFiles = count($this->mockFilesys->getJsFilesFixture());
        $expectedResult = $countAllFiles - $countJsFiles;

		$callback = function(string $filename) {
			$pattern = '/\.js$/';
			return preg_match($pattern, $filename, $matches);
		};
	    $this->findFiles->setFileFilter($callback);

        $this->findFiles->deleteFiles($this->mockRoot);

        $this->findFiles->setFileFilter(null);
        $remainingFiles = $this->findFiles->findFiles($this->mockRoot);
        $actualResult = count($remainingFiles);

        static::assertSame($expectedResult, $actualResult);
    }

    public function testDeleteFilesNoMatches() : void
    {
	    $callback = function(string $filename) {
		    $pattern = '/\.zzz$/';
		    return preg_match($pattern, $filename, $matches);
	    };
	    $this->findFiles->setFileFilter($callback);
        self::assertFalse($this->findFiles->deleteFiles($this->mockRoot));
    }

    public function testInspectFileContents() : void
    {
        $expectedResult = $this->mockFilesys->getFilesContainingTheWordThisFixture();

        $closure = function ($file) {
            $string = (file_get_contents($file) ?: '');
            // returns true if the string 'this' (case insensitive) appears somewhere in the file.
            return preg_match('/this/i', $string);
        };

        $this->findFiles->setFileFilter($closure);
        $actualResult = $this->findFiles->findFiles($this->mockRoot);

        static::assertEquals($expectedResult, $actualResult);
    }
}
