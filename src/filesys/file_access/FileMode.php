<?php

declare(strict_types=1);

namespace pvc\storage\filesys\file_access;

enum FileMode: string
{
    /**
     * Open for reading only; place the file pointer at the beginning of the file.
     */
    case READ = 'r';

    /**
     * Open for reading and writing; place the file pointer at the beginning of the file.
     */
    case READ_WRITE = 'r+';

    /**
     * Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length.
     * If the file does not exist, attempt to create it.
     */
    case WRITE = 'w';

    /**
     * Open for reading and writing; otherwise it has the same behavior as 'w'.
     */
    case WRITE_WRITE = 'w+';

    /**
     * Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to
     * create it. In this mode, fseek() has no effect, writes are always appended.
     */
    case APPEND = 'a';

    /**
     * Open for reading and writing; place the file pointer at the end of the file. If the file does not exist,
     * attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.
     */
    case APPEND_READ = 'a+';

    /**
     * Create and open for writing only; place the file pointer at the beginning of the file. If the file already
     * exists, the fopen() call will fail by returning false and generating an error of level E_WARNING. If the file
     * does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying
     * open(2) system call.
     */
    case CREATE_WRITE_NO_OVERWRITE = 'x';

    /**
     * Create and open for reading and writing; otherwise it has the same behavior as 'x'.
     */
    case CREATE_READ_WRITE_NO_OVERWRITE = 'x+';

    /**
     * Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither
     * truncated (as opposed to 'w'), nor the call to this function fails (as is the case with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful if it's desired to get an advisory
     * lock (see flock()) before attempting to modify the file, as using 'w' could truncate the file before the
     * lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
     */
    case CREATE_WRITE_NO_TRUNCATE = 'c';

    /**
     * Open the file for reading and writing; otherwise it has the same behavior as 'c'.
     */
    case CREATE_READ_WRITE_NO_TRUNCATE = 'c+';

    /**
     * Set close-on-exec flag on the opened file descriptor. Only available in PHP compiled on POSIX.1-2008
     * conforming systems.
     */
    case CLOSE_ON_EXECUTE = 'e';
}
