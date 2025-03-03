<?php

namespace CodingPartners\AutoController\Helpers;

class DirectoryMaker
{
    /**
     * Creates the necessary directory if it does not exist.
     *
     * @param string $directoryPath The directory path to create.
     * @return void
     */
    public static function createDirectory($directoryPath)
    {
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
    }
}
