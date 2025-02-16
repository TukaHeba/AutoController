<?php

namespace CodingPartners\AutoController\Helpers;

class Suffix
{
    /**
     * Get the suffix from a given file name.
     *
     * This method splits the filename by underscores and returns the last part.
     *
     * @param string $fileName The file name to extract the suffix from.
     * @return string The extracted suffix.
     */
    public static function getSuffix($fileName)
    {
        $parts = explode('_', $fileName);
        return end($parts);
    }
}
