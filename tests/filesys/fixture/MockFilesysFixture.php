<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\filesys\fixture;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use Exception;

/**
 * Class MockFilesysFixture.  This object can be used as a fixture for tests that need a mocked file system.
 */
class MockFilesysFixture
{
    protected vfsStreamDirectory $vfsFilesys;
    protected string $vfsRoot;
    protected array $allFilesFixture;
    protected array $phpFilesFixture;
    protected array $jsFilesFixture;
    protected array $cssCsvFilesFixture;
    protected array $filesContainingTheWordThisFixture;

    /**
     * MockFilesysFixture constructor.
     * @throws Exception
     */
    public function __construct()
    {
        /** @var array[string[]] $arrSrcFiles */
        $arrSrcFiles = [
            'Subdir_1' => [
                'AbstractFactory' => [
                    'test.php' => 'some text content',
                    'other.php' => 'Some more text content',
                    'Invalid.csv' => 'Something else',
                    'valid.css' => 'not real css'
                ],
                'AnEmptyFolder' => [],
                'somecode.php' => 'some php content',
                'somejavascript.js' => 'this is not real javascript - it is just a test'
            ],
            'Subdir_2' => [
                'SmallLibrary' => [
                    'libFile_1.php' => 'This is another php file of some kind',
                    'libFile_2.php' => 'This is the second php file in this library.',
                    'libFile.css' => 'This is the first css file in this library.',
                    'libFile.js' => 'This is the first javascript file in this library.',
                    'libFileDoc.txt' => 'This should be some documentation kind of stuff.',
                    'OtherJSFile.js' => 'more bogus javascript',
                    'libFile_3.php' => 'libFile_3.php content',
                    'libFile_4.php' => 'libFile_4.php content'
                ]
            ],
            'fileInRootOfFixture.ini' => 'Maybe this is some kind of a configuration file... or not'
        ];

        $this->allFilesFixture = [
            'vfs://root/Subdir_1/AbstractFactory/test.php',
            'vfs://root/Subdir_1/AbstractFactory/other.php',
            'vfs://root/Subdir_1/AbstractFactory/Invalid.csv',
            'vfs://root/Subdir_1/AbstractFactory/valid.css',
            'vfs://root/Subdir_1/somecode.php',
            'vfs://root/Subdir_1/somejavascript.js',
            'vfs://root/Subdir_2/SmallLibrary/libFile_1.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_2.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile.css',
            'vfs://root/Subdir_2/SmallLibrary/libFile.js',
            'vfs://root/Subdir_2/SmallLibrary/libFileDoc.txt',
            'vfs://root/Subdir_2/SmallLibrary/OtherJSFile.js',
            'vfs://root/Subdir_2/SmallLibrary/libFile_3.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_4.php',
            'vfs://root/fileInRootOfFixture.ini'
        ];

        $this->phpFilesFixture = [
            'vfs://root/Subdir_1/AbstractFactory/test.php',
            'vfs://root/Subdir_1/AbstractFactory/other.php',
            'vfs://root/Subdir_1/somecode.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_1.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_2.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_3.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_4.php'
        ];

        $this->jsFilesFixture = [
            'vfs://root/Subdir_1/somejavascript.js',
            'vfs://root/Subdir_2/SmallLibrary/libFile.js',
            'vfs://root/Subdir_2/SmallLibrary/OtherJSFile.js'
        ];

        $this->cssCsvFilesFixture = [
            'vfs://root/Subdir_1/AbstractFactory/Invalid.csv',
            'vfs://root/Subdir_1/AbstractFactory/valid.css',
            'vfs://root/Subdir_2/SmallLibrary/libFile.css'
        ];

        $this->filesContainingTheWordThisFixture = [
            'vfs://root/Subdir_1/somejavascript.js',
            'vfs://root/Subdir_2/SmallLibrary/libFile_1.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile_2.php',
            'vfs://root/Subdir_2/SmallLibrary/libFile.css',
            'vfs://root/Subdir_2/SmallLibrary/libFile.js',
            'vfs://root/Subdir_2/SmallLibrary/libFileDoc.txt',
            'vfs://root/fileInRootOfFixture.ini'
        ];

        $filesysRoot = 'root';
        $permissions = null;

        $filesys = vfsStream::setup($filesysRoot, $permissions, $arrSrcFiles);
        $this->vfsFilesys = $filesys;
        $this->vfsRoot = $this->vfsFilesys->url();
    }

    /**
     * @function findVfsFiles
     * @param vfsStreamContent $vfsStreamContent
     * @param string $regex
     * @return array
     */
    public function findVfsFiles(vfsStreamContent $vfsStreamContent, string $regex): array
    {
        $files = [];

        if (($vfsStreamContent->getType() == vfsStreamContent::TYPE_FILE) &&
            (preg_match($regex, $vfsStreamContent->url()))) {
            $files[] = $vfsStreamContent->url();
            return $files;
        }

        if ($vfsStreamContent instanceof vfsStreamDirectory) {
            $childIterator = $vfsStreamContent->getChildren();
            foreach ($childIterator as $file) {
                if (($file instanceof vfsStreamDirectory) && !$file->isDot()) {
                    $files = array_merge($files, $this->findVfsFiles($file, $regex));
                } elseif (preg_match($regex, $file->url())) {
                    $files[] = $file->url();
                }
            }
        }
        return $files;
    }

    /**
     * getVfsFilesys
     * @return vfsStreamDirectory
     */
    public function getVfsFilesys(): vfsStreamDirectory
    {
        return $this->vfsFilesys;
    }

    /**
     * getVfsRoot
     * @return string
     */
    public function getVfsRoot(): string
    {
        return $this->vfsRoot;
    }

    /**
     * changePermissionsOnRootToUnreadable
     */
    public function changePermissionsOnRootToUnreadable(): void
    {
        $this->getVfsFilesys()->chmod(0000);
    }

    /**
     * getAllFilesFixture
     * @return string[]
     */
    public function getAllFilesFixture(): array
    {
        return $this->allFilesFixture;
    }

    /**
     * getPhpFilesFixture
     * @return string[]
     */
    public function getPhpFilesFixture(): array
    {
        return $this->phpFilesFixture;
    }

    /**
     * getJsFilesFixture
     * @return string[]
     */
    public function getJsFilesFixture(): array
    {
        return $this->jsFilesFixture;
    }

    /**
     * getCssCsvFilesFixture
     * @return string[]
     */
    public function getCssCsvFilesFixture(): array
    {
        return $this->cssCsvFilesFixture;
    }

    /**
     * getFilesContainingTheWordThisFixture
     * @return string[]
     */
    public function getFilesContainingTheWordThisFixture(): array
    {
        return $this->filesContainingTheWordThisFixture;
    }
}
