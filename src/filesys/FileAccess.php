<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\filesys;

use pvc\filesys\err\FileAccessException;
use pvc\filesys\err\FileAccessExceptionMsg;
use Throwable;

class FileAccess
{
    /**
     * @var string
     */
    protected string $filename;

    // cannot type hint a resource.  This is also a bit tricky because if there is no type hint, then the
    // variable is initialized to null, which is different than type hinted properties which have an initial
    // state of 'uninitialized'
    /** @phpstan-ignore-next-line */
    protected $handle;

    /**
     * @var FileAccessExceptionMsg
     */
    protected FileAccessExceptionMsg $fileAccessErrmsg;

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
     * getHandle.  This should and will throw an error if it is called and handle is not set.
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * getFileAccessErrmsg
     * @return FileAccessExceptionMsg
     */
    public function getFileAccessErrmsg() : FileAccessExceptionMsg
    {
        return $this->fileAccessErrmsg;
    }

    /**
     * fileEntryExists
     * @param string $filename
     * @return bool
     */
    protected function fileEntryExists(string $filename) : bool
    {
        if (!file_exists($filename)) {
            $msgVars = [$filename];
            $msgText = 'file/directory %s does not exist or you do not have permissions to list the file.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        return true;
    }

    /**
     * modeIsReadOnly
     * @param string $mode
     * @return bool
     */
    protected function modeIsReadOnly(string $mode) : bool
    {
        return ('r' == substr($mode, 0, 1));
    }

    /**
     * fileExists.  Tests for the existence of a file
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename)
    {
        if (!$this->fileEntryExists($filename)) {
            return false;
        }
        if (is_dir($filename)) {
            $msgVars = [$filename];
            $msgText = 'filename cannot be a directory - must be a file.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
        return true;
    }

    /**
     * directoryExists
     * @param string $dirname
     * @return bool
     */
    public function directoryExists(string $dirname) : bool
    {
        if (!$this->fileEntryExists($dirname)) {
            return false;
        }
        if (is_file($dirname)) {
            $msgVars = [$dirname];
            $msgText = 'directory name must be a directory - cannot be a file.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
        return true;
    }

    /**
     * fileIsReadable
     * @param string $filename
     * @return bool
     */
    public function fileIsReadable(string $filename) : bool
    {
        if (!$this->fileExists($filename)) {
            return false;
        }

        if (!is_readable($filename)) {
            $msgVars = [$filename];
            $msgText = 'you do not have sufficient permissions to read file %s.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
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
            return false;
        }

        if (!is_writeable($filename)) {
            $msgVars = [$filename];
            $msgText = 'you do not have sufficient permissions to write to the file %s.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
        return true;
    }

    /**
     * fileProspectiveIsWriteable
     * @param string $filename
     * @return bool
     */
    public function fileProspectiveIsWriteable(string $filename) : bool
    {
        if ($this->fileExists($filename)) {
            return $this->fileIsWriteable($filename);
        }
        if ($this->directoryExists($filename)) {
            $msgVars = [$filename];
            $msgText = 'file to write to (%s) cannot be an existing directory.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        $dir = pathinfo($filename, PATHINFO_DIRNAME);
        return $this->directoryIsWriteable($dir);
    }

    /**
     * directoryIsReadable
     * @param string $dirname
     * @return bool
     */
    public function directoryIsReadable(string $dirname) : bool
    {
        if (!$this->directoryExists($dirname)) {
            return false;
        }

        if (!is_readable($dirname)) {
            $msgVars = [$dirname];
            $msgText = 'you do not have sufficient permissions to read directory %s.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
        return true;
    }

    /**
     * directoryIsWriteable
     * @param string $dirname
     * @return bool
     */
    public function directoryIsWriteable(string $dirname) : bool
    {
        if (!$this->directoryExists($dirname)) {
            return false;
        }

        if (!is_writable($dirname)) {
            $msgVars = [$dirname];
            $msgText = 'you do not have sufficient permissions to write into directory %s.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
            return false;
        }
        unset($this->fileAccessErrmsg);
        return true;
    }

    /**
     * openFile
     * @param string $filename
     * @param string $mode
     * @return bool
     * @throws FileAccessException
     */
    public function openFile(string $filename, string $mode): bool
    {
        $previousErrorReportingLevel = error_reporting();
        error_reporting(E_ALL);

        try {
            $handle = fopen($filename, $mode);
        } catch (Throwable $e) {
            $msgText = $e->getMessage();
            $msgVars = [$filename];
            $this->fileAccessErrmsg = new FileAccessExceptionMsg($msgVars, $msgText);
        }

        error_reporting($previousErrorReportingLevel);

        if (isset($this->fileAccessErrmsg)) {
            return false;
        }

        /* phpstan does not see that handle must be already set */
        /** @phpstan-ignore-next-line */
        if ($handle === false) {
            $msgText = 'internal error - file handle not set and no error condition detected.';
            $msg = new FileAccessExceptionMsg([], $msgText);
            throw new FileAccessException($msg);
        }

        $this->handle = $handle;
        $this->filename = $filename;
        return true;
    }

    /**
     * closeFile
     */
    public function closeFile(): bool
    {
        if (is_null($this->handle)) {
            $msgText = 'Error trying to close file that was not opened.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        fclose($this->handle);
        $this->handle = null;
        return true;
    }

    /**
     * writeFile
     * @param string $data
     * @return bool
     */
    public function writeFile(string $data) : bool
    {
        if (is_null($this->handle)) {
            $msgText = 'Error trying to write to file that was not opened.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        if (false === fwrite($this->handle, $data)) {
            $msgText = 'error trying to write to file %s.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([$this->filename], $msgText);
            return false;
        }
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
            $msgText = 'Length parameter must be greater than 0.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        if (is_null($this->handle)) {
            $msgText = 'Error trying to read from file that was not opened.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        if (false === ($result = fread($this->handle, $length))) {
            $msgText = 'Filesystem error trying to read from file.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        return $result;
    }

    /**
     * fileGetContents
     * @param string $filename
     * @return string|false
     * @throws FileAccessException
     */
    public function fileGetContents(string $filename)
    {
        if (!is_null($this->getHandle())) {
            $msgText = 'error trying to open file.  This object already has another file open.';
            $msg = new FileAccessExceptionMsg([], $msgText);
            $this->fileAccessErrmsg = $msg;
            return false;
        } else {
            // clear the status of the cache or the filesize may be reported incorrectly if
            // this file was just written to prior to being read from....
            clearstatcache();
            /* phpstan wants to make sure that this is an integer and not false */
            $fileLength = filesize($filename);
            if ($fileLength === false) {
                $fileLength = PHP_INT_MAX;
            }
        }

        $mode = 'r';
        if (false === $this->openFile($filename, $mode)) {
            return false;
        }

        // contents will be false if there was a problem reading the file
        $contents = $this->readFile($fileLength);

        $this->closeFile();
        return $contents;
    }

    /**
     * eof
     * @return bool
     * @throws FileAccessException
     */
    public function eof() : bool
    {
        if (is_null($this->getHandle())) {
            $msgText = 'error trying to determine whether eof is true:  no file is currently open.';
            $msgVars = [];
            $msg = new FileAccessExceptionMsg($msgVars, $msgText);
            throw new FileAccessException($msg);
        }

        return feof($this->handle);
    }

    /**
     * filePutContents
     * @param string $filename
     * @param string $data
     * @return bool
     * @throws FileAccessException
     */
    public function filePutContents(string $filename, string $data) : bool
    {
        if (!is_null($this->getHandle())) {
            $msgText = 'error trying to open file.  This object already has another file open.';
            $msg = new FileAccessExceptionMsg([], $msgText);
            $this->fileAccessErrmsg = $msg;
            return false;
        }

        // try to create file if it does not already exist.
        $mode = 'w+';
        if (!$this->openFile($filename, $mode)) {
            return false;
        }

        if (!$this->writeFile($data)) {
            return false;
        }

        $this->closeFile();
        return true;
    }

    /**
     * fileGetLine
     * @return string|false
     */
    public function fileGetLine()
    {
        if (is_null($this->handle)) {
            $msgText = 'Error trying to read from file that was not opened.';
            $this->fileAccessErrmsg = new FileAccessExceptionMsg([], $msgText);
            return false;
        }
        if (false === ($line = fgets($this->getHandle()))) {
            $msgText = 'error trying to get line from file.';
            $msg = new FileAccessExceptionMsg([], $msgText);
            $this->fileAccessErrmsg = $msg;
            return false;
        }
        return $line;
    }
}
