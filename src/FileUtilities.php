<?php
namespace Palaso\Utilities;

class FileUtilities
{

    /**
     * Replace special characters with the replacement character.
     * Special characters are any of those in Windows or Linux that cannot be used in a fileName.
     *
     * @param string $fileName string to replace characters in
     * @param string $replaceChar [optional] (defaults to underscore)
     * @return string This function returns a string with the replaced values.
     */
    public static function replaceSpecialCharacters($fileName, $replaceChar = '_')
    {
        $search = array(
            '/',
            '\\',
            '?',
            '%',
            '*',
            ':',
            '|',
            '"',
            '<',
            '>'
        );
        return str_replace($search, $replaceChar, $fileName);
    }

    /**
     * Creates all the necessary folders in the path
     *
     * @param string $folderPath
     */
    public static function createAllFolders($folderPath)
    {
        if (! file_exists($folderPath) and ! is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    /**
     * Removes the entire tree of files and folders including the specified folder and below
     *
     * @param string $folderPath
     */
    public static function removeFolderAndAllContents($folderPath)
    {
        if (file_exists($folderPath) and is_dir($folderPath)) {
            self::recursiveRemoveFolder($folderPath);
        }
    }

    /**
     *
     * @param string $folderPath
     */
    protected static function recursiveRemoveFolder($folderPath)
    {
        foreach (glob("{$folderPath}/*") as $file) {
            if (is_dir($file)) {
                self::recursiveRemoveFolder($file);
            } else {
                unlink($file);
            }
        }
        rmdir($folderPath);
    }
}
