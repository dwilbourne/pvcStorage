<?php

declare(strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

return [
    "file.not.exist" => "The file you are looking for ({filename}) does not exist.",
    "entry.must.be.file" => 'Filename ({filename}) cannot be a directory - must be a file.',
    "file.not.readable" => "You do not have permission to read the file ({filename}).",
    "file.not.writeable" => "You do not have permission to write to the file ({filename}).",

    "directory.not.exist" => "The directory you are looking for ({dirname}) does not exist.",
    "entry.must.be.directory" => 'Directory ({dirname}) cannot be a file - must be a directory.',
    "directory.not.readable" => "You do not have permission to read the directory ({dirname}).",
    "directory.not.writeable" => "You do not have permission to write to the directory ({dirname}).",

    "file.io.error" => "There was an error opening, reading from, or writing to the file ({filename}).",

    "file.already.open" => "Error opening file.  This object already has a file open ({filename}).",
    "file.no.handle" => "Error opening file ({filename}). No file handle returned.",

    "invalid.buffer.size" => "Length of read buffer must be greater than 0.",
    "file.not.open" => "File must be opened before calling {methodname}.",

];
