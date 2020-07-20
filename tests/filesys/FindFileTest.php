<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */
namespace tests\filesys;

use PHPUnit\Framework\TestCase;
use pvc\err\throwable\exception\stock_rebrands\InvalidArgumentException;
use pvc\filesys\err\FileSystemException;
use pvc\filesys\FindFile;
use pvc\regex\Regex;
use tests\filesys\fixture\MockFilesysFixture;

class FindFileTest extends TestCase
{
    protected MockFilesysFixture $mockFilesys;
    protected FindFile $findFile;
    protected string $mockRoot;

    public function setUp() : void
    {
        $this->mockFilesys = new MockFilesysFixture();
        $this->mockRoot = $this->mockFilesys->getVfsRoot();
        $this->findFile = new FindFile();
    }

    public function testFindFilesSearchDirException1() : void
    {
        // is not a directory
        $searchDir = 'foo';
        self::expectException(InvalidArgumentException::class);
        $this->findFile->findFiles($searchDir);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFindFilesSearchDirException2() : void
    {
        $searchDir = __DIR__;
        uopz_set_return('is_readable', false);
        self::expectException(InvalidArgumentException::class);
        $this->findFile->findFiles($searchDir);
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
        $this->findFile->findFiles($searchDir);
        uopz_unset_return('open_dir');
    }

    public function testSetGetCallback() : void
    {
        $callback = function () {
        };
        $this->findFile->setCallback($callback);
        self::assertEquals($callback, $this->findFile->getCallback());
    }

    public function testSetGetRecurse() : void
    {
        self::assertEquals(true, $this->findFile->getRecurse());
        $this->findFile->setRecurse(false);
        self::assertFalse($this->findFile->getRecurse());
    }


    public function testFindFileAll() : void
    {
        $expectedResult = $this->mockFilesys->getAllFilesFixture();
        $actualResult = $this->findFile->findFiles($this->mockRoot);
        static::assertSame($expectedResult, $actualResult);
    }

    public function testFindFilePhp() : void
    {
        $expectedResult = $this->mockFilesys->getPhpFilesFixture();
        $regex = new Regex();
        $regex->setPattern('/\.php$/');
        $this->findFile->setCallback([$regex, 'match']);
        $actualResult = $this->findFile->findFiles($this->mockRoot);
        static::assertSame($expectedResult, $actualResult);
    }

    public function testDeleteFile() : void
    {

        $countAllFiles = count($this->mockFilesys->getAllFilesFixture());
        $countJsFiles = count($this->mockFilesys->getJsFilesFixture());
        $expectedResult = $countAllFiles - $countJsFiles;

        $regex = new Regex();
        $regex->setPattern('/\.js$/');
        $this->findFile->setCallback([$regex, 'match']);
        $this->findFile->deleteFiles($this->mockRoot);

        $this->findFile->setCallback(null);
        $remainingFiles = $this->findFile->findFiles($this->mockRoot);
        $actualResult = count($remainingFiles);

        static::assertSame($expectedResult, $actualResult);
    }

    public function testDeleteFilesNoMatches() : void
    {
        $regex = new Regex();
        // no files with the file extension zzz
        $regex->setPattern('/\.zzz$/');
        $this->findFile->setCallback([$regex, 'match']);
        self::assertFalse($this->findFile->deleteFiles($this->mockRoot));
    }

    public function testInspectFileContents() : void
    {
        $expectedResult = $this->mockFilesys->getFilesContainingTheWordThisFixture();

        $closure = function ($file) {
            $string = (file_get_contents($file) ?: '');
            // returns true if the string 'this' (case insensitive) appears somewhere in the file.
            return preg_match('/this/i', $string);
        };

        $this->findFile->setCallback($closure);
        $actualResult = $this->findFile->findFiles($this->mockRoot);

        static::assertEquals($expectedResult, $actualResult);
    }
}
