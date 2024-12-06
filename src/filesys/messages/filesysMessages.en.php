<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare (strict_types=1);

return [
    'file_entry_not_exist' => 'file/directory ${fileName} does not exist or you do not have permissions to list the file.',

    'file_entry_cannot_be_dir' => 'file ${fileName} exists but is a directory not a file.',
    'file_entry_not_readable' => 'file ${fileName} exists but is not readable - check permissions',
    'file_entry_not_writeable' => 'file ${fileName} exists but is not writeable - check permissions',
    'file_contents_could_not_read' => 'file ${fileName} exists and is readable but there was an error getting its contents',
    'file_contents_could_not_write' => 'file ${fileName} exists and is writeable but there was an error writing to it',
    'file_could_not_open' => 'unable to write to ${fileName}',

    'directory_entry_cannot_be_file' => 'directory entry ${dirName} exists but is a file not a directory.',
    'directory_entry_not_readable' => 'directory ${dirName} exists but you do not have permissions to list its contents',
    'directory_entry_not_writeable' => 'directory ${dirName} exists but you do not have permissions to write to it.',
    'directory_contents_cannot_be_listed' => 'directory ${dirName} exists and is readable but there was an error listing its contents',


];
