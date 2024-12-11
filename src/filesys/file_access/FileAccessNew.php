<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\storage\filesys\file_access;

use pvc\err\ErrorHandler;
use pvc\err\stock\ErrorException;
use pvc\interfaces\msg\MsgInterface;
use pvc\storage\err\FilePermissionsException;
use pvc\storage\err\InsufficientFileModeException;
use pvc\storage\err\InvalidFileModeException;

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
class FileAccessNew
{
    /**
     * @var string[]
     * The php fopen documentation says you can use a 'b' or a 't'  to modify the modes listed below.
     * 'b' is binary and 't' is 'newline translate' (applicable only to php running on Windows is what the
     * documentation says).  Then the documentation adds a note discouraging the use of the 't' modifier for
     * reasons of portability. And 'b' (for binary) is the default.  So let's forget about the 'b' and 't'
     * modifiers entirely.......
     *
     * And while we are at it, there is a valid mode 'e', but it is only valid on POSIX-2008 systems, so let's forget
     * that as well.
     */
    protected array $modes = ['r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];
    protected $readAccessModeArray = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];
    protected $writeAccessModeArray = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];
    protected $modeCanCreateFileArray = ['w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];
    protected $modeCannotCreateFileArray = ['r', 'r+', 'x'];

    /**
     * @var MsgInterface
     */
    protected MsgInterface $msg;

    protected ErrorHandler $handlerClass;

    protected string $msgDomain = 'filesys';

    public function __construct(MsgInterface $msg, ErrorHandler $handlerClass)
    {
        $this->setMsg($msg);
        $this->setHandlerClass($handlerClass);
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

    /**
     * @return ErrorHandler
     */
    public function getHandlerClass(): ErrorHandler
    {
        return $this->handlerClass;
    }

    /**
     * @param ErrorHandler $handlerClass
     */
    public function setHandlerClass(ErrorHandler $handlerClass): void
    {
        $this->handlerClass = $handlerClass;
    }

    protected function isValidFileMode(string $mode): bool
    {
        return in_array($mode, $this->modes);
    }

    protected function modeNeedsReadAccess(string $mode): bool
    {
        return in_array($mode, $this->readAccessModeArray);
    }

    protected function modeNeedsWriteAccess(string $mode): bool
    {
        return in_array($mode, $this->writeAccessModeArray);
    }

    protected function modeCanCreateFile($mode): bool
    {
        return in_array($mode, $this->modeCanCreateFileArray);
    }

    protected function modeCannotCreateFile($mode): bool
    {
        return in_array($mode, $this->modeCannotCreateFileArray);
    }

    protected function fileOperation(callable $closure, array $params): mixed
    {
        $previousErrorReportingLevel = error_reporting();
        error_reporting(E_ALL);
        set_error_handler([$this->getHandlerClass(), 'handler']);
        $result = false;

        try {
            $result = call_user_func_array($closure, $params);
        } catch (ErrorException $e) {
            $severity = $this->getHandlerClass()->getErrorSeverity();
            /**
             * unfortunately, php refers to error constants as 'severity', which is really a misnomer.....
             */
            $errno = $e->getSeverity();

            switch ($severity) {
                case ErrorHandler::NOTICE:
                case ErrorHandler::WARNING:
                case ErrorHandler::PARSE:
                case ErrorHandler::FATAL:
                    $severityText = $this->getHandlerClass()->friendlyErrorSeverity($severity);
                    $errnoText = $this->getHandlerClass()->friendlyErrorType($errno);
                    $msg = $severityText . ' (' . $errnoText . '): ' . $e->getMessage() . PHP_EOL;
                    echo $msg;
                    break;
            }
        }

        error_reporting($previousErrorReportingLevel);
        restore_error_handler();
        return $result;
    }

    protected function modeAndPermissionsAreCompatible(string $fileName, string $mode): bool
    {
        if (file_exists($fileName)) {
            /**
             * if mode requires read permissions, make sure file is readable
             */
            if ($this->modeNeedsReadAccess($mode) && !is_readable($fileName)) {
                return false;
            }
            /**
             * if mode requires write permissions, make sure file is writeable
             */
            if ($this->modeNeedsWriteAccess($mode) && !is_writeable($fileName)) {
                return false;
            }
        } else {
            /**
             * file does not exist, directory must be writeable
             */
            if (!is_writeable(dirname($fileName))) {
                return false;
            }
        }
        return true;
    }

    public function openFile(string $fileName, string $mode): bool
    {
        if (!$this->isValidFileMode($mode)) {
            throw new InvalidFileModeException($mode);
        }

        if (!file_exists($fileName) && $this->modeCannotCreateFile($mode)) {
            throw new InsufficientFileModeException($mode);
        }

        if (!$this->modeAndPermissionsAreCompatible($fileName, $mode)) {
            throw new FilePermissionsException($fileName, $mode);
        }

        $closure = fn(string $fileName) => fopen($fileName, $mode);
        $parameters = [$fileName, $mode];
        return $this->fileOperation($closure, $parameters);
    }

    public function closeFile(mixed $handle): bool
    {
        $closure = fn($handle) => fclose($handle);
        $parameters = [$handle];
        return $this->fileOperation($closure, $parameters);
    }
}
