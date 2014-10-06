<?php
use Palaso\Utilities\FileUtilities;

class FileUtilitiesTest extends PHPUnit_Framework_TestCase
{

    public function testRemoveFolderAndAllContents_NoContentExists_NoThrow()
    {
        FileUtilities::removeFolderAndAllContents('/non/existant/folder/path');
    }

    public function testFolderAndAllContentCRUD_ContentExists_ContentRemoved()
    {
        // create folder and subfolder
    	$folderPath = sys_get_temp_dir() . '/testFolder';
    	$subFolderPath = $folderPath . '/subfolder';
    	FileUtilities::createAllFolders($subFolderPath);
    	$this->assertTrue(file_exists($subFolderPath) and is_dir($subFolderPath));

        // put a file in each folder
        $filePath = $folderPath . 'test.txt';
        $subFolderFilePath = $subFolderPath . 'subFolderTest.txt';
    	file_put_contents($filePath, 'test file contents');
        file_put_contents($subFolderFilePath, 'test sub folder file contents');
        $this->assertTrue(file_exists($filePath) and ! is_dir($filePath));
        $this->assertTrue(file_exists($subFolderFilePath) and ! is_dir($subFolderFilePath));

        // remove the folder and all contents
        FileUtilities::removeFolderAndAllContents($folderPath);
    	$this->assertFalse(file_exists($folderPath));
    }
}
