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
     * @throws \ErrorException
     */
    public static function createAllFolders($folderPath)
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) use ($folderPath) {
            $dirName = self::findPathThatExists($folderPath);
            
            $perms = decoct(fileperms($dirName) & 0777);
            $owner = posix_getpwuid(fileowner($dirName))['name'];
            $group = posix_getgrgid(filegroup($dirName))['name'];
            $user = posix_getpwuid(posix_geteuid())['name'];

            $msg = "Error in createAllFolders($folderPath)\n"
            . $errstr ."\n"
            . "Parent folder: $dirName\n"
            . "Owner of parent folder: $owner\n"
            . "Group of parent folder: $group\n"
            . "Permissions on parent folder: $perms\n"
            . "PHP running as user: $user\n";

            throw new \ErrorException($msg, 0, $errno, $errfile, $errline);
        });
        if (! file_exists($folderPath) and ! is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        restore_error_handler();
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
     * Delete entire contents of a directory, including hidden files
     * @param string $folderPath
     */
    protected static function recursiveRemoveFolder($folderPath)
    {
        $files = array_diff(scandir($folderPath), array('.', '..'));
        foreach ($files as $file) {
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath) && !is_link($filePath)) {
                self::recursiveRemoveFolder($filePath);
            } else {
                unlink($filePath);
            }
        }
        if (is_dir($folderPath) && !is_link($folderPath)) {
            rmdir($folderPath);
        } else {
            unlink($folderPath);
        }
    }

    /**
     * Removes a single folder and promotes all its contents "up" one folder
     * @deprecated in favour of rename method
     * @param string $folderPath
     */
    public static function promoteDirContents($folderPath)
    {
        self::promoteFolderContents($folderPath);
    }

    /**
     * Removes a single folder and promotes all its contents "up" one folder
     * @param string $folderPath
     */
    public static function promoteFolderContents($folderPath)
    {
        $realFolderPath = realpath($folderPath);
        if (!is_dir($realFolderPath)) {
            return;
        }
        $parent = realpath($realFolderPath . DIRECTORY_SEPARATOR . '..');
        if (!is_dir($parent)) {
            return;
        }

        // Rename folder to a name not found inside it before we promote its contents
        // E.g., if folder "foo" contains a file "foo", we don't want to try "mv foo/foo ./foo", which
        // would fail. Instead, we want to try "mv renamed_foo/foo ./foo", which should succeed.
        $newName = 'renamed';
        do {
            $newName = $newName . '_' . mt_rand();
        } while (file_exists($realFolderPath . DIRECTORY_SEPARATOR . $newName) || file_exists($parent . DIRECTORY_SEPARATOR . $newName));
        $newFolderPath = $parent . DIRECTORY_SEPARATOR . $newName;
        rename($realFolderPath, $newFolderPath);

        // Now it's safe to promote files and remove the now-empty folder
        foreach (scandir($newFolderPath) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            rename($newFolderPath . DIRECTORY_SEPARATOR . $filename, $parent . DIRECTORY_SEPARATOR . $filename);
        }
        rmdir($newFolderPath);
    }

    /**
     * Copy a folder and all sub-folders
     * @param string $sourcePath
     * @param string $destinationPath
     */
    public static function copyFolderTree($sourcePath, $destinationPath) {
        FileUtilities::createAllFolders($destinationPath);
        foreach (scandir($sourcePath) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $sourceFile = $sourcePath . DIRECTORY_SEPARATOR . $filename;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($sourceFile)) {
                self::copyFolderTree($sourceFile, $destinationFile);
            } else {
                copy($sourceFile, $destinationFile);
            }
        }
    }

    /**
     * Copy a folder and all sub-folders and normalize file names
     * @param string $sourcePath
     * @param string $destinationPath
     * @param string $form (optional, defaults to NFC)
     */
    public static function copyFolderTreeNormalize($sourcePath, $destinationPath, $form = \Normalizer::FORM_C) {
        FileUtilities::createAllFolders($destinationPath);
        foreach (scandir($sourcePath) as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $sourceFile = $sourcePath . DIRECTORY_SEPARATOR . $filename;
            $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . \Normalizer::normalize($filename, $form);
            if (is_dir($sourceFile)) {
                self::copyFolderTreeNormalize($sourceFile, $destinationFile);
            } else {
                copy($sourceFile, $destinationFile);
            }
        }
    }

    /**
     * Gets the part of a path that exists
     * @oaram string $input theoretical path
     * @return string part of $input that currently exists
     */
    public static function findPathThatExists($input) {
        while ($input != '/') {
            if (file_exists($input)) {
                return $input;
            }
            $input = dirname($input);
        }
        return '';
    }
}
