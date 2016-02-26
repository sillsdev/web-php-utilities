<?php

use Palaso\Utilities\FileUtilities;

class FileUtilitiesTest extends PHPUnit_Framework_TestCase
{
    public function testReplaceSpecialCharacters_AllCharsInFilename_AllCharsReplacedWithUnderscore()
    {
        $fileName = '/\\?%*:|"<>.mp3';
        $newFileName = FileUtilities::replaceSpecialCharacters($fileName);
        $this->assertEquals('__________.mp3', $newFileName, 'fileName should have all underscores');
    }

    public function testReplaceSpecialCharacters_AllCharsInFilename_AllCharsReplacedWithDashes()
    {
        $fileName = '/\\?%*:|"<>.mp3';
        $newFileName = FileUtilities::replaceSpecialCharacters($fileName, '-');
        $this->assertEquals('----------.mp3', $newFileName, 'fileName should have all dashes');
    }

    public function testRemoveFolderAndAllContents_NoContentExists_NoThrow()
    {
        FileUtilities::removeFolderAndAllContents('/non/existent/folder/path');
    }

    public function testRemoveFolderAndAllContents_ContentExists_ContentRemoved()
    {
        // create folder and sub-folder
        $folderPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testFolder';
        $subFolderPath = $folderPath . DIRECTORY_SEPARATOR . 'subFolder';
        FileUtilities::createAllFolders($subFolderPath);
        $this->assertTrue(file_exists($subFolderPath) && is_dir($subFolderPath));

        // put a file in each folder
        $filePath = $folderPath . DIRECTORY_SEPARATOR . 'test.txt';
        $subFolderFilePath = $subFolderPath . DIRECTORY_SEPARATOR . 'subFolderTest.txt';
        file_put_contents($filePath, 'test file contents');
        file_put_contents($subFolderFilePath, 'test sub folder file contents');
        $this->assertTrue(file_exists($filePath) && !is_dir($filePath));
        $this->assertTrue(file_exists($subFolderFilePath) && !is_dir($subFolderFilePath));

        // remove the folder and all contents
        FileUtilities::removeFolderAndAllContents($folderPath);
        $this->assertFalse(file_exists($folderPath));
    }

    public function testRemoveFolderAndAllContents_LinkExists_LinkRemovedAndTargetContentNotRemoved()
    {
        // create folder and symlink
        $folderPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testFolder';
        FileUtilities::createAllFolders($folderPath);
        $linkFolderPath = $folderPath . DIRECTORY_SEPARATOR . 'linkFolder';
        $targetFolderPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'targetFolder';
        FileUtilities::createAllFolders($targetFolderPath);
        symlink($targetFolderPath, $linkFolderPath);
        $this->assertTrue(is_dir($linkFolderPath) && is_link($linkFolderPath));

        // put a file in each folder
        $filePath = $folderPath . DIRECTORY_SEPARATOR . 'test.txt';
        $targetFilename = 'targetFolderTest.txt';
        $linkFolderFilePath = $linkFolderPath . DIRECTORY_SEPARATOR . $targetFilename;
        $targetFolderFilePath = $targetFolderPath . DIRECTORY_SEPARATOR . $targetFilename;
        file_put_contents($filePath, 'test file contents');
        file_put_contents($linkFolderFilePath, 'test link folder file contents');
        $this->assertTrue(file_exists($filePath) && !is_dir($filePath));
        $this->assertTrue(file_exists($linkFolderFilePath) && !is_dir($linkFolderFilePath));
        $this->assertTrue(file_exists($targetFolderFilePath) && !is_dir($targetFolderFilePath));

        // remove the folder and all contents
        FileUtilities::removeFolderAndAllContents($folderPath);
        $this->assertFalse(file_exists($folderPath));
        $this->assertTrue(file_exists($targetFolderFilePath) && !is_dir($targetFolderFilePath));

        // cleanup
        FileUtilities::removeFolderAndAllContents($targetFolderPath);
        $this->assertFalse(file_exists($targetFolderPath));
    }

}

class FileUtilitiesPromoteFolderTest extends PHPUnit_Framework_TestCase
{
    protected $folderPath;
    protected $subFolderPath;
    protected $filePath;
    protected $subFolderFilePath;
    protected $foldersToTearDown;

    protected function setUp()
    {
        $this->foldersToTearDown = array();

        // create folder and sub-folder
        $this->folderPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testFolder_' . mt_rand();
        $this->subFolderPath = $this->folderPath . DIRECTORY_SEPARATOR . 'subFolder';
        FileUtilities::createAllFolders($this->subFolderPath);
        // put a file in each folder
        $this->filePath = $this->folderPath . DIRECTORY_SEPARATOR . 'test.txt';
        $this->subFolderFilePath = $this->subFolderPath . DIRECTORY_SEPARATOR . 'subFolderTest.txt';
        file_put_contents($this->filePath, 'test file contents');
        file_put_contents($this->subFolderFilePath, 'test sub folder file contents');

        $this->foldersToTearDown[] = $this->folderPath;
    }

    protected function tearDown()
    {
        foreach ($this->foldersToTearDown as $folderPath) {
            FileUtilities::removeFolderAndAllContents($folderPath);
        }
    }

    public function testPromoteDirContents_ParamDoesNotExist_NoThrow()
    {
        FileUtilities::promoteDirContents('/non/existent/folder/path');
    }

    public function testPromoteDirContents_ParamIsNotAFile_NoThrow()
    {
        FileUtilities::promoteDirContents($this->subFolderFilePath);
    }

    public function testPromoteDirContents_Works()
    {
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) && ! is_dir($this->subFolderFilePath));
        FileUtilities::promoteDirContents($this->subFolderPath);
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertFalse(file_exists($this->subFolderFilePath));

        // subFolderTest.txt should now be one level up
        $newSubFolderFilePath = $this->folderPath . DIRECTORY_SEPARATOR . 'subFolderTest.txt';
        $this->assertTrue(file_exists($newSubFolderFilePath) && !is_dir($newSubFolderFilePath));
    }

    public function testCopyDirTree_Works()
    {
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) && !is_dir($this->subFolderFilePath));

        $destDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'newTestFolder';
        $this->foldersToTearDown[] = $destDir;

        FileUtilities::copyDirTree($this->folderPath, $destDir);

        $this->assertTrue(file_exists($destDir) && is_dir($destDir));
        foreach (array('test.txt', 'subFolder', 'subFolder/subFolderTest.txt') as $fileName) {
            $destFile = $destDir . DIRECTORY_SEPARATOR . $fileName;
            $this->assertTrue(file_exists($destFile));
            if ($fileName == 'subFolder') {
                $this->assertTrue(is_dir($destFile));
            } else {
                $this->assertFalse(is_dir($destFile));
            }
        }
    }
}
