<?php

declare(strict_types=1);

namespace pvc\storage\filesys;

use ReflectionClass;

class FileMode
{
    /**
     * Open for reading only; place the file pointer at the beginning of the file.
     */
    const READ = 'r';

    /**
     * Open for reading and writing; place the file pointer at the beginning of the file.
     */
    const READ_WRITE = 'r+';

    /**
     * Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length.
     * If the file does not exist, attempt to create it.
     */
    const WRITE = 'w';

    /**
     * Open for reading and writing; otherwise it has the same behavior as 'w'.
     */
    const WRITE_WRITE = 'w+';

    /**
     * Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to
     * create it. In this mode, fseek() has no effect, writes are always appended.
     */
    const APPEND = 'a';

    /**
     * Open for reading and writing; place the file pointer at the end of the file. If the file does not exist,
     * attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.
     */
    const APPEND_READ = 'a+';

    /**
     * Create and open for writing only; place the file pointer at the beginning of the file. If the file already
     * exists, the fopen() call will fail by returning false and generating an error of level E_WARNING. If the file
     * does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying
     * open(2) system call.
     */
    const CREATE_WRITE_NO_OVERWRITE = 'x';

    /**
     * Create and open for reading and writing; otherwise it has the same behavior as 'x'.
     */
    const CREATE_READ_WRITE_NO_OVERWRITE = 'x+';

    /**
     * Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither
     * truncated (as opposed to 'w'), nor the call to this function fails (as is the const with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful if it's desired to get an advisory
     * lock (see flock()) before attempting to modify the file, as using 'w' could truncate the file before the
     * lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
     */
    const CREATE_WRITE_NO_TRUNCATE = 'c';

    /**
     * Open the file for reading and writing; otherwise it has the same behavior as 'c'.
     */
    const CREATE_READ_WRITE_NO_TRUNCATE = 'c+';

    /**
     * Set close-on-exec flag on the opened file descriptor. Only available in PHP compiled on POSIX.1-2008
     * conforming systems.
     */
    const CLOSE_ON_EXECUTE = 'e';

    public static function isDefined(string $mode): bool
    {
        $reflection = new ReflectionClass(__CLASS__);
        $constants = $reflection->getConstants();
        return in_array($mode, $constants);
    }
}
