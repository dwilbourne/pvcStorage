<?php declare(strict_types = 1);

namespace pvc\filesys;

use pvc\filesys\err\FilesysBadSearchDirException;
use pvc\filesys\err\FilesysBadSearchDirMsg;
use pvc\filesys\err\FileSystemException;
use pvc\filesys\err\FileSystemExceptionMsg;

/**
 * FindFile traverses a local filesystem looking for matches to the callback provided.
 *
 * In a simple use, the callback could be to look for filenames that contain certain letters or have a certain
 * extension.  A more sophisticated use might involve opening each file and inspecting the contents.
 *
 * Class FindFile
 */
class FindFile
{

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected bool $recurse=true;

    /**
     * FindFile constructor.
     * @param callable|null $callback
     */
    public function __construct(callable $callback = null)
    {
        $this->setCallback($callback);
    }


    /**
     * @function getCallback
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @function setCallback
     * @param callable|null $callback
     */
    public function setCallback(callable $callback = null): void
    {
        if (is_null($callback)) {
            $callback = function () {
                return true;
            };
        }
        $this->callback = $callback;
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

    /**
     * @function findFiles
     * @param string $dir
     * @return array
     */

    public function findFiles(string $dir): array
    {

        if (!is_dir($dir) || !is_readable($dir)) {
            $msg = new FilesysBadSearchDirMsg();
            throw new FilesysBadSearchDirException($msg);
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
                if (call_user_func($this->callback, $filePath)) {
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
