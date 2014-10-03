<?php
namespace Palaso\Utilities;

class FileUtilities
{
    /**
     * Creates all the necessary folders in the path
     *
     * @param string $folderPath
     */
    public static function createAllFolders($folderPath) {
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
