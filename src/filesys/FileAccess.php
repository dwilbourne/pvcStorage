<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\filesys;

use pvc\filesys\messages\FileAccessMsg;
use Throwable;

class FileAccess
{
    /**
     * @var string
     * holds the filename if this module has a file open
     */
    protected string $openFileName;

    // cannot type hint a resource.  This is also a bit tricky because if there is no type hint, then the
    // variable is initialized to null, which is different than type hinted properties which have an initial
    // state of 'uninitialized'
    /** @phpstan-ignore-next-line */
    protected $handle;

    /**
     * @var FileAccessMsg
     */
    protected FileAccessMsg $fileAccessMsg;

    /**
     * @var string[]
     */
    protected array $modes = ['r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+', 'e'];

    /**
     * @var string[]. Empty string implies the default mode which is 'b' (binary).  't' does
     * end of line translation for windows. See the php documentation for fopen.
     */
    protected array $modeSuffixes = ['', 'b', 't'];

    /**
     * @var bool
     * controls whether the getDirectoryContents method should return the current and parent directories
     * as part of the listing.  Note that if the function recurses, dot files are NEVER included in the
     * listings of the subdirectories.
     */
    protected $dotFilesIncluded = false;

    /**
     * getFileAccessMsg
     * @return FileAccessMsg
     */
    public function getFileAccessMsg(): ?FileAccessMsg
    {
        return $this->fileAccessMsg ?? null;
    }

    /**
     * fileExists.  Tests for the existence of a file
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename): bool
    {
        if (!file_exists($filename)) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.exist", ['filename' => $filename]);
            return false;
        }
        if (is_dir($filename)) {
            $this->fileAccessMsg = new FileAccessMsg("entry.must.be.file", ['filename' => $filename]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * fileIsReadable
     * @param string $filename
     * @return bool
     */
    public function fileIsReadable(string $filename): bool
    {
        if (!$this->fileExists($filename)) {
            // message set in fileExists method
            return false;
        }

        if (!is_readable($filename)) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.readable", ['filename' => $filename]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * fileIsWriteable
     * @param string $filename
     * @return bool
     */
    public function fileIsWriteable(string $filename): bool
    {
        if (!$this->fileExists($filename)) {
            // message set in fileExists method
            return false;
        }

        if (!is_writeable($filename)) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.writeable", ['filename' => $filename]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * directoryExists
     * @param string $dirname
     * @return bool
     */
    public function directoryExists(string $dirname): bool
    {
        if (!file_exists($dirname)) {
            $this->fileAccessMsg = new FileAccessMsg("directory.not.exist", ['dirname' => $dirname]);
            return false;
        }
        if (!is_dir($dirname)) {
            $this->fileAccessMsg = new FileAccessMsg("entry.must.be.directory", ['dirname' => $dirname]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * directoryIsReadable
     * @param string $dirname
     * @return bool
     */
    public function directoryIsReadable(string $dirname): bool
    {
        if (!$this->directoryExists($dirname)) {
            return false;
        }

        if (!is_readable($dirname)) {
            $this->fileAccessMsg = new FileAccessMsg("directory.not.readable", ['dirname' => $dirname]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * directoryIsWriteable
     * @param string $dirname
     * @return bool
     */
    public function directoryIsWriteable(string $dirname): bool
    {
        if (!$this->directoryExists($dirname)) {
            return false;
        }

        if (!is_writable($dirname)) {
            $this->fileAccessMsg = new FileAccessMsg("directory.not.writeable", ['dirname' => $dirname]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * getDirectoryContents
     * @param string $dirname
     * @param callable|null $callback
     * @param bool $recurseYn
     * @param bool $includeDotFiles
     * @return array<string>|null
     *
     * dirname is the directory to search.  The optional callback gives you the ability to pass logic into the
     * method to determine what kinds of files should be included in the result.  The callback should
     * return true if the file is to be included, false if it is not.
     *
     * includeDotFiles controls whether the getDirectoryContents method should return the current and parent directories
     * as part of the listing.  Note that if the function recurses, dot files are NEVER included in the
     * listings of the subdirectories.
     */
    public function getDirectoryContents(
        string $dirname,
        callable $callback = null,
        bool $recurseYn = true,
        bool $includeDotFiles = false
    ): ?array {
        $result = [];
        $dotArray = [".", ".."];

        if (is_null($callback)) {
            $callback = function (string $filename) {
                return true;
            };
        }

        if (!$this->directoryIsReadable($dirname)) {
            return null;
        }

        if (false === ($fileArray = scandir($dirname))) {
            $this->fileAccessMsg = new FileAccessMsg("directory.read.error", ['dirname' => $dirname]);
            return null;
        }

        /** @phpstan-ignore-next-line */
        foreach ((array)$fileArray as $filename) {
            $filePath = $dirname . DIRECTORY_SEPARATOR . $filename;
            if ($recurseYn && is_dir($filePath) && (!in_array($filename, $dotArray))) {
                // include the directory entry itself
                $result[] = $filePath;
                // We can use the default values for the third and fourth parameters in the recursive call.  Recursively
                // obtained results should not contain dots
                $result = array_merge($result, $this->getDirectoryContents($filePath, $callback));
            } elseif (in_array($filename, $dotArray)) {
                if ($includeDotFiles) {
                    $result[] = $filePath;
                }
            } elseif ($callback($filePath)) {
                $result[] = $filePath;
            }
        }
        unset($this->fileAccessMsg);
        return $result;
    }

    /**
     * getFileContents
     * @param string $filename
     * @return string|false
     */
    public function getFileContents(string $filename)
    {
        if ($this->getHandle()) {
            $this->fileAccessMsg = new FileAccessMsg("file.already.open", ['filename' => $this->openFileName]);
            return false;
        }

        $mode = 'r';
        if (false === $this->openFile($filename, $mode)) {
            // message set in openFile method
            return false;
        }

        // contents will be false if there was a problem reading the file
        $contents = $this->readFile();

        $this->closeFile();
        return $contents;
    }

    /**
     * getHandle.
     * @return mixed
     * returns the handle, or null if it is not set because it is not (cannot be) type-hinted
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * openFile
     * @param string $filename
     * @param string $mode
     * @return bool
     */
    public function openFile(string $filename, string $mode): bool
    {
        $previousErrorLevel = error_reporting();

        error_reporting(E_ALL);

        try {
            $handle = fopen($filename, $mode);
        } catch (Throwable $e) {
            $this->fileAccessMsg = new FileAccessMsg("file.io.error", ['filename' => $filename]);
        }

        error_reporting($previousErrorLevel);

        /**
         * In tests, if the file does not exist, fopen does not return anything, so handle is unset.
         * fopen documentation indicates that it returns false if it fails, so we account for that case.
         * And if all else goes wrong and PHP throws an error, we catch that as well, so all cases should]
         * be covered....
         */
        if ((!isset($handle) || ($handle == false) || $this->getFileAccessMsg())) {
            return false;
        }

        $this->handle = $handle;
        $this->openFileName = $filename;
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * readFile
     * @param int $length
     * @return false|string
     */
    public function readFile(int $length = 8096)
    {
        if ($length < 1) {
            $this->fileAccessMsg = new FileAccessMsg('invalid.buffer.size');
            return false;
        }
        if (is_null($this->handle)) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.opened");
            return false;
        }
        if (false === ($result = fread($this->handle, $length))) {
            $this->fileAccessMsg = new FileAccessMsg('file.io.error', ['filename' => $this->openFileName]);
            return false;
        }
        unset($this->fileAccessMsg);
        return $result;
    }

    /**
     * closeFile
     */
    public function closeFile(): bool
    {
        if (is_null($this->handle)) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.open", ['methodname' => __METHOD__]);
            return false;
        }
        fclose($this->handle);
        unset($this->handle);
        unset($this->openFileName);
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * eof
     * @return bool|null
     * returns null if there is no file currently open
     */
    public function eof()
    {
        if (is_null($this->getHandle())) {
            $this->fileAccessMsg = new FileAccessMsg("file.not.open", ['methodname' => __METHOD__]);
            return null;
        }
        unset($this->fileAccessMsg);
        return feof($this->handle);
    }

    /**
     * filePutContents
     * @param string $filename
     * @param string $data
     * @return bool
     * opens, writes to, and closes a file
     */
    public function filePutContents(string $filename, string $data): bool
    {
        if ($this->getHandle()) {
            $msg = new FileAccessMsg('file.already.open', ['filename' => $this->openFileName]);
            $this->fileAccessMsg = $msg;
            return false;
        }

        // try to create file if it does not already exist.
        $mode = 'w+';
        if (!$this->openFile($filename, $mode)) {
            // message set in the openFile method
            return false;
        }

        if (!$this->writeFile($data)) {
            // message set in the writeFile method
            return false;
        }

        $this->closeFile();
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * writeFile
     * @param string $data
     * @return bool
     * just writes to a file - does not open it or close it
     */
    public function writeFile(string $data): bool
    {
        if (is_null($this->getHandle())) {
            $this->fileAccessMsg = new FileAccessMsg('file.not.open', ['methodname' => __METHOD__]);
            return false;
        }
        if (false === fwrite($this->handle, $data)) {
            $this->fileAccessMsg = new FileAccessMsg('file.io.error', ['filename' => $this->openFileName]);
            return false;
        }
        unset($this->fileAccessMsg);
        return true;
    }

    /**
     * fileGetLine
     * @return string|false
     */
    public function fileGetLine()
    {
        if (is_null($this->handle)) {
            $this->fileAccessMsg = new FileAccessMsg('file.not.open', ['methodname' => __METHOD__]);
            return false;
        }
        if (false === ($line = fgets($this->getHandle()))) {
            $this->fileAccessMsg = new FileAccessMsg('file.io.error', ['filename' => $this->openFileName]);
            return false;
        }
        unset($this->fileAccessMsg);
        return $line;
    }
}
