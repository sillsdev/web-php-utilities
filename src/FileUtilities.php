<?php
namespace Palaso\Utilities;

class FileUtilities
{
    /**
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
