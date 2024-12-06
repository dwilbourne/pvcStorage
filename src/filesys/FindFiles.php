<?php

declare(strict_types=1);

namespace pvc\storage\filesys;

use pvc\interfaces\storage\filesys\FileFilterInterface;
use pvc\storage\err\InvalidSortOrderException;

/**
 * FindFiles iterates over a filesystem looking for matches to the fileFilter provided.
 *
 * In a simple use, the fileFilter could be to look for filenames that contain certain letters or have a certain
 * extension.  A more sophisticated use might involve opening each file and inspecting the contents.
 *
 * You can set a flag to indicate whether scandir should sort ascending / decending / not at all.
 * You can set a flag to search depth-first or breadth-first.
 *
 * Class FindFiles
 */
class FindFiles
{
    /**
     * @var FileFilterInterface
     */
    protected FileFilterInterface $fileFilter;

    /**
     * @var bool
     */
    protected bool $recurse = true;

    /**
     * @var int
     * choices are currently implemented as the stock sort orders available in the scandir function
     */
    protected int $fileSortOrder = SCANDIR_SORT_ASCENDING;

    /**
     * @var array<int>
     */
    private array $sortOrderChoices = [SCANDIR_SORT_ASCENDING, SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE];


    /**
     * FindFiles constructor.
     * @param callable|null $callback
     */
    public function __construct(callable $callback = null)
    {
        $this->setFileFilter($callback);
    }


    /**
     * @function getFileFilter
     * @return callable
     */
    public function getFileFilter(): callable
    {
        return $this->fileFilter;
    }

    /**
     * @function setFileFilter
     * @param callable|null $fileFilter
     */
    public function setFileFilter(callable $fileFilter = null): void
    {
        if (is_null($fileFilter)) {
            $fileFilter = function () {
                return true;
            };
        }
        $this->fileFilter = $fileFilter;
    }

    /**
     * @function getRecurse
     * @return bool
     */
    public function getRecurse(): bool
    {
        return $this->recurse;
    }

    /**
     * @function setRecurse
     * @param bool $value
     */
    public function setRecurse(bool $value): void
    {
        $this->recurse = $value;
    }

    public function setFileSortOrder(int $sortOrder): void
    {
        if (!in_array($sortOrder, $this->sortOrderChoices)) {
            throw new InvalidSortOrderException();
        }
        $this->fileSortOrder = $sortOrder;
    }

    public function getFileSortOrder(): int
    {
        return $this->fileSortOrder;
    }


    /**
     * @function findFiles
     * @param string $dir
     * @return array
     */

    public function findFiles(string $dir): array
    {

        if (!is_dir($dir) || !is_readable($dir)) {
            $msg = new FilesysBadSearchDirMsg();
            throw new DirectoryDoesNotExistException($msg);
        }

        if (false === ($handle = @opendir($dir))) {
            $msg = new FileSystemExceptionMsg($dir);
            throw new FileSystemException($msg);
        }

        $matchedfiles = [];

        while ($file = readdir($handle)) {
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath) and $file <> ".." and $file <> "." and $this->getRecurse()) {
                $subdir_matches = $this->findFiles($filePath);
                $matchedfiles = array_merge($matchedfiles, $subdir_matches);
                unset($file);
            } elseif (!is_dir($filePath)) {
                if (call_user_func($this->fileFilter, $filePath)) {
                    array_push($matchedfiles, $filePath);
                }
            }
        }
        closedir($handle);
        return $matchedfiles;
    }

    /**
     * @function deleteFiles
     * @param string
     * @return bool|array.  returns true or an array of the file names which could not be deleted.
     * returns false if no files were found to delete.
     */
    /** @phpstan-ignore-next-line */
    public function deleteFiles(string $dir)
    {
        if (empty($files = $this->findFiles($dir))) {
            return false;
        }

        // array keys are the file names, unlink returns true or false for the array values
        // need to save and restore error level because unlink throws a warning if it fails
        $currentLevel = error_reporting();
        error_reporting(0);
        $unlinkResults = array_combine($files, array_map('unlink', $files)) ?: [];
        error_reporting($currentLevel);

        // $notDeleted is all filenames where the result of unlink was false
        $notDeleted = array_keys(array_filter($unlinkResults, 'assert'));

        return empty($notDeleted) ? true : $notDeleted;
    }
}
