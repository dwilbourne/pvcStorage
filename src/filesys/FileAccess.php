<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys;

use Error;
use pvc\interfaces\msg\MsgInterface;
use pvc\interfaces\storage\filesys\FileAccessInterface;
use pvc\storage\err\FileAccessException;
use pvc\storage\err\FileGetContentsException;
use pvc\storage\err\FileHandleException;
use pvc\storage\err\InvalidFileModeException;
use pvc\storage\err\InvalidReadLengthException;
use pvc\storage\err\OpenFileException;

/**
 * Class FileAccess
 *
 * wrapper for standard file operations.
 *
 * The class creates language neutral messages indicating the nature of
 * the problem in the event an operation fails (messages are suitable for returning back through the user interface).
 * For example, if file_exists returns false, this class generates a language neutral message indicating that the
 * file does not exist.  It also tries to be more precise with the message than the php verb might otherwise
 * indicate.  For example, is_readable returns false if the file does not exist and if you do not have permissions.
 * This class will tell you in the message if it does not exist or whether it exists but is not readable.
 *
 * Php file i/o functions can throw warnings (a type of error) if they fail. This can be confusing because it's not
 * obvious without further examination whether the problem is simple or potentially catastrophic. It's simple
 * if you can't read a file because of a permissions problem.  It is catastrophic if you have a corrupted disk.
 * The solution is to trap the error and set an attribute to the message text of the error object so if you want to
 * get at it, you can.  For example, if file_get_contents fails on a file where the file is readable, this class will
 * generate a message that the operation failed, set the errorMsgText and the operation will return false.

 * The class will only throw an exception for a logic problem.  For example, if you do something silly like try to
 * close a file without having opened it, you'll get an exception.  Or if you try to write to a file using a bad
 * "mode" parameter.
 *
 * Many php file operations can be executed in a stream context.  However, this class (at the moment)
 * is purely oriented towards a local file system.  There are no parameters in any of the methods that allow
 * you to pass in a stream context.
 */
class FileAccess implements FileAccessInterface
{
    /**
     * @var string
     */
    protected string $fileName;

    /**
     * @var resource|false
     */
    protected $handle = false;

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
     * @var MsgInterface
     */
    protected MsgInterface $msg;

    /**
     * @var Error
     */
    protected string $errorMsgText;

    protected string $msgDomain = 'filesys';

    public function __construct(MsgInterface $msg)
    {
        $this->setMsg($msg);
    }

    /**
     * @return MsgInterface
     */
    public function getMsg(): MsgInterface
    {
        return $this->msg;
    }

    /**
     * @param MsgInterface $msg
     */
    public function setMsg(MsgInterface $msg): void
    {
        $this->msg = $msg;
    }

    protected function isValidFileMode(string $mode): bool
    {
        return in_array($mode, $this->modes);
    }

    /**
     * getHandle
     * @return mixed
     * file handles are resources and cannot be precisely type hinted
     */
    protected function getHandle(): mixed
    {
        return $this->handle;
    }

    /**
     * setHandle
     * @param mixed $handle
     * file handles are resources and cannot be precisely type hinted
     */
    protected function setHandle(mixed $handle): void
    {
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName ?? '';
    }

    /**
     * @param string $fileName
     */
    protected function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * getErrorMsgText
     * @return string
     */
    public function getErrorMsgText(): string
    {
        return $this->errorMsgText ?? '';
    }

    /**
     * @param Error $error
     */
    protected function setErrorMsgText(string $errorMsgText): void
    {
        $this->errorMsgText = $errorMsgText;
    }

    protected function clearErrorMsgText()
    {
        unset($this->errorMsgText);
    }

    protected function fileAccessErrorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $this->setErrorMsgText($errstr);
        return true;
    }

    /**
     * getFileInfo
     * @param array<array<callable, array<mixed $params>>> $preChecks
     * @param callable $closure
     * @param array<mixed> $params
     * @return mixed
     */
    protected function fileOperation(array $preChecks, string $msgId, callable $closure, array $params): mixed
    {
        $this->getMsg()->clearContent();
        $this->clearErrorMsgText();

        foreach ($preChecks as $preCheck) {
            $callable = $preCheck[0];
            $paramArray = $preCheck[1];
            if (!call_user_func_array($callable, $paramArray)) {
                return false;
            }
        }

        $previousErrorReportingLevel = error_reporting();
        error_reporting(E_ALL);
        set_error_handler([$this, 'fileAccessErrorHandler']);

        /**
         * if the failure is severe it is possible that $result will not be set
         */
        if (!$result = call_user_func_array($closure, $params) ?? false) {
            $this->getMsg()->setContent($this->msgDomain, $msgId, $params);
        }

        error_reporting($previousErrorReportingLevel);
        restore_error_handler();
        return $result;
    }

    /**
     * fileEntryExists
     * @param string $fileEntryName
     * @return bool
     * file entry is either a directory or a file.
     */
    public function fileEntryExists(string $fileEntryName): bool
    {
        $preChecks = [];
        $msgId = 'file_entry_not_exist';
        $closure = 'file_exists';
        $parameters = [$fileEntryName];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * returns true if the file exists, false if it does not exist or if the entry is a directory.
     *
     * @function fileExists
     * @param string $fileName
     * @return bool
     */
    public function fileExists(string $fileName): bool
    {
        $parameters = [$fileName];
        $preCheck0 = [[$this, 'fileEntryExists'], $parameters];
        $preChecks = [$preCheck0];

        $msgId = 'file_entry_cannot_be_dir';
        $closure = 'is_file';
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * returns true if the file exists and is readable, false otherwise.
     *
     * @function fileIsReadable
     * @param string $fileName
     * @return bool
     */
    public function fileIsReadable(string $fileName): bool
    {
        $parameters = [$fileName];
        $preCheck0 = [[$this, 'fileExists'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'file_entry_not_readable';
        $closure = 'is_readable';
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * fileExistingIsWriteable
     * @param string $fileName
     * @return bool
     */
    public function fileIsWriteable(string $fileName): bool
    {
        $parameters = [$fileName];
        $preCheck0 = [[$this, 'fileExists'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'file_entry_not_writeable';
        $closure = 'is_writeable';
        $parameters = [$fileName];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * directoryExists
     * @param string $dirName
     * @return bool
     */
    public function directoryExists(string $dirName): bool
    {
        $parameters = [$dirName];
        $preCheck0 = [[$this, 'fileEntryExists'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'directory_entry_cannot_be_file';
        $closure = 'is_dir';
        $parameters = [$dirName];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * directoryIsReadable
     * @param string $dirName
     * @return bool
     */
    public function directoryIsReadable(string $dirName): bool
    {
        $parameters = [$dirName];
        $preCheck0 = [[$this, 'directoryExists'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'directory_entry_not_readable';
        $closure = 'is_readable';
        $parameters = [$dirName];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * directoryIsWriteable
     * @param string $dirName
     * @return bool
     */
    public function directoryIsWriteable(string $dirName): bool
    {
        $parameters = [$dirName];
        $preCheck0 = [[$this, 'directoryExists'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'directory_entry_not_writeable';
        $closure = 'is_writeable';
        $parameters = [$dirName];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * getDirectoryContents
     * @param string $dirName
     * @param bool $withDots
     * @return array|false
     * @throws \pvc\storage\err\FileAccessException
     */
    public function directoryGetContents(string $dirName, bool $withDots = false, int $sortOrder =
    SCANDIR_SORT_ASCENDING): array|false
    {
        $parameters = [$dirName];
        $preCheck0 = [[$this, 'directoryIsReadable'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'directory_contents_cannot_be_listed';
        $closure = function($dirName) use($withDots, $sortOrder) {
            $result = scandir($dirName, $sortOrder);
            $diff = (!$withDots) ? ['.', '..'] : [];
            return array_diff($result, $diff);
        };
        $parameters = [$dirName, $sortOrder];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * fileGetContents
     * @param string $fileName
     * @return string|false
     * @throws \pvc\storage\err\FileGetContentsException
     */
    public function fileGetContents(string $fileName): string|false
    {
        $parameters = [$fileName];
        $preCheck0 = [[$this, 'fileIsReadable'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'file_contents_could_not_read';
        $closure = fn(string $fileName) => file_get_contents($fileName);
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * filePutContents
     * @param string $fileName
     * @param string $data
     * @return bool
     * @throws \pvc\storage\err\FileGetContentsException
     */
    public function filePutContents(string $fileName, string $data): int|false
    {
        $parameters = [$fileName];
        $preCheck0 = [[$this, 'fileIsWriteable'], $parameters];
        $preChecks = [$preCheck0];
        $msgId = 'file_contents_could_not_write';
        $closure = fn(string $fileName) => file_put_contents($fileName, $data);
        $parameters = [$fileName, $data];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * openFile
     * @param string $fileName
     * @param string $mode
     * @return bool
     * @throws OpenFileException
     */
    public function openFile(string $fileName, string $mode): bool
    {
        if (!$this->isValidFileMode($mode)) {
            throw new InvalidFileModeException($mode);
        }

        $preChecks = [];
        $msgId = 'file_could_not_open';
        $closure = fn(string $fileName) => fopen($fileName, $mode);
        $parameters = [$fileName, $mode];
        if ($handle = $this->fileOperation($preChecks, $msgId, $closure, $parameters)) {
            $this->setHandle($handle);
            $this->setFileName($fileName);
            return true;
        }
        return false;
    }

    /**
     * closeFile
     */
    public function closeFile(): bool
    {
        if (!$handle = $this->getHandle()) {
            throw new FileHandleException();
        }
        $preChecks = [];
        $msgId = '';
        $closure = fn($handle) => fclose($handle);
        $parameters = [$handle];
        if (!$result = $this->fileOperation($preChecks, $msgId, $closure, $parameters)) {
            $this->setHandle(false);
        }
        return $result;
    }

    /**
     * readFile
     * @param int $length
     * @return string
     */
    public function readFile(int $length = 8096): string
    {
        if (!$handle = $this->getHandle()) {
            throw new FileHandleException();
        }
        if ($length < 1) {
            throw new InvalidReadLengthException();
        }
        $preChecks = [];
        $msgId = '';
        $closure = function ($handle, $length) {
            return fread($handle, $length);
        };
        $parameters = [$handle, $length];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * eof
     * @return bool
     * @throws \pvc\storage\err\FileAccessException
     */
    public function eof(): bool
    {
        if (!$handle = $this->getHandle()) {
            throw new FileHandleException();
        }
        $preChecks = [];
        $msgId = '';
        $closure = function ($handle) {
            return feof($handle);
        };
        $parameters = [$handle];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * writeFile
     * @param string $data
     * @return bool
     */
    public function writeFile(string $data): bool
    {
        if (!$handle = $this->getHandle()) {
            throw new FileHandleException();
        }
        $preCheck0 = [[$this, 'fileIsWriteable'], [$this->getFileName()]];
        $preChecks = [$preCheck0];
        $msgId = '';
        $closure = function ($handle, string $data) {
            return fwrite($handle, $data);
        };
        $parameters = [$handle, $data];
        return $this->fileOperation($preChecks, $msgId, $closure, $parameters);
    }

    /**
     * fileGetLine
     * @return string
     */
    public function fileGetLine(): string
    {
        $closure = function($handle) { return fgets($handle); };
        return $this->fileOperation($closure, $this->getHandle());
    }

    public function filePutLine(string $data): bool
    {
        $closure = function($handle, string $data) { return fputs($handle, $data); };
        return $this->fileOperation($closure, $this->getHandle(), $data);
    }
}
