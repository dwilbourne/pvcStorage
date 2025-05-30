<?php

declare(strict_types=1);

namespace pvc\storage\filesys;

use pvc\storage\filesys\err\FileDoesNotExistException;
use pvc\storage\filesys\err\FileGetContentsException;
use pvc\storage\filesys\err\FileNotReadableException;
use pvc\storage\filesys\err\FileOpenException;
use pvc\storage\filesys\err\InvalidFileHandleException;
use pvc\storage\filesys\err\InvalidFileModeException;
use pvc\storage\resource\err\InvalidResourceException;
use Throwable;

class File
{
    /**
     * @param string $localFileName
     * @return true
     * @throws FileDoesNotExistException
     */
    public static function mustExist(string $localFileName): true
    {
        /**
         * type checker is not aware that file
         */
        if (!file_exists($localFileName)) {
            throw new FileDoesNotExistException($localFileName);
        }
        return true;
    }

    /**
     * @param string $filePath
     * @return true
     * @throws FileNotReadableException
     * @throws FileDoesNotExistException
     */
    public static function mustBeReadable(string $filePath): true
    {
        if (!file_exists($filePath)) {
            throw new FileDoesNotExistException($filePath);
        }
        if (!is_readable($filePath)) {
            throw new FileNotReadableException($filePath);
        }
        return true;
    }


    /**
     * @param string $filePath
     * @return resource
     * adds a specific test to ensure the file is readable
     */
    public static function openReadOnly(string $filePath)
    {
        self::mustBeReadable($filePath);
        return self::open($filePath, FileMode::READ);
    }

    /**
     * @param string $filePath
     * @param string $mode
     * @return resource
     */
    public static function open(string $filePath, string $mode = 'r')
    {
        if (!FileMode::isDefined($mode)) {
            throw new InvalidFileModeException($mode);
        }

        /**
         * this may seem a bit laborious, but the VfsStream wrapper will throw its own error or exception
         * if it fails because of permissions so we need to catch it.
         */
        try {
            $handle = fopen($filePath, $mode);
        } catch (Throwable $e) {
            throw new FileOpenException($filePath, $mode, $e);
        }

        if ($handle === false) {
            throw new FileOpenException($filePath, $mode);
        }

        return $handle;
    }

    /**
     * @param resource $handle
     * @return void
     * @throws InvalidFileHandleException
     * @throws InvalidResourceException
     */
    public static function close($handle): void
    {
        if (!is_resource($handle)) {
            throw new InvalidResourceException();
        }
        if (get_resource_type($handle) !== 'stream') {
            throw new InvalidFileHandleException();
        }
        fclose($handle);
    }

    /**
     * @param string $filePath
     * @return string
     * @throws FileGetContentsException
     * @throws FileNotReadableException
     */
    public static function getContents(string $filePath): string
    {
        self::mustBeReadable($filePath);
        try {
            $contents = file_get_contents($filePath);
        } catch (Throwable $e) {
            throw new FileGetContentsException($filePath, $e);
        }
        if ($contents === false) {
            throw new FileGetContentsException($filePath);
        }
        return $contents;
    }
}
