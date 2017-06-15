<?php

use Palaso\Utilities\FileUtilities;
use PHPUnit\Framework\TestCase;

class FileUtilitiesTest extends TestCase
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
        $folderPath = '/non/existent/folder/path';
        $this->assertFalse(file_exists($folderPath));
        FileUtilities::removeFolderAndAllContents($folderPath);
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

class FileUtilitiesPromoteFolderTest extends TestCase
{
    protected $folderPath;
    protected $subFolderPath;
    protected $filePath;
    protected $subFolderFilePath;
    protected $foldersToTearDown;

    protected function setUp()
    {
        $this->foldersToTearDown = [];

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

    public function testPromoteFolderContents_ParamDoesNotExist_NoThrow()
    {
        $folderPath = '/non/existent/folder/path';
        $this->assertFalse(file_exists($folderPath));
        FileUtilities::promoteFolderContents($folderPath);
        $this->assertFalse(file_exists($folderPath));
    }

    public function testPromoteFolderContents_ParamIsNotAFile_NoThrow()
    {
        $this->assertTrue(file_exists($this->subFolderPath) && is_dir($this->subFolderPath));
        FileUtilities::promoteFolderContents($this->subFolderPath);
        $this->assertFalse(file_exists($this->subFolderPath));
    }

    public function testPromoteFolderContents_Works()
    {
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) && !is_dir($this->subFolderFilePath));
        FileUtilities::promoteFolderContents($this->subFolderPath);
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertFalse(file_exists($this->subFolderFilePath));

        // subFolderTest.txt should now be one level up
        $newSubFolderFilePath = $this->folderPath . DIRECTORY_SEPARATOR . 'subFolderTest.txt';
        $this->assertTrue(file_exists($newSubFolderFilePath) && !is_dir($newSubFolderFilePath));
    }

    public function testCopyFolderTree_Works()
    {
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) && !is_dir($this->subFolderFilePath));

        $destinationPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'newTestFolder';
        $this->foldersToTearDown[] = $destinationPath;

        FileUtilities::copyFolderTree($this->folderPath, $destinationPath);

        $this->assertTrue(file_exists($destinationPath) && is_dir($destinationPath));
        foreach (['test.txt', 'subFolder', 'subFolder/subFolderTest.txt'] as $filename) {
            $destinationFilePath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
            $this->assertTrue(file_exists($destinationFilePath));
            if ($filename == 'subFolder') {
                $this->assertTrue(is_dir($destinationFilePath));
            } else {
                $this->assertFalse(is_dir($destinationFilePath));
            }
        }
    }

    public function testCopyFolderTreeNormalize_Works()
    {
        $this->assertTrue(file_exists($this->filePath) && !is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) && !is_dir($this->subFolderFilePath));

        $subFolderNfdFilePath = $this->subFolderPath . DIRECTORY_SEPARATOR . 'tårta.txt'; // NFD
        file_put_contents($subFolderNfdFilePath, 'test sub folder NFD filename file contents');
        $destinationPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'newTestFolder';
        $this->foldersToTearDown[] = $destinationPath;

        FileUtilities::copyFolderTreeNormalize($this->folderPath, $destinationPath);

        $this->assertTrue(file_exists($destinationPath) && is_dir($destinationPath));
        foreach (['test.txt', 'subFolder', 'subFolder/tårta.txt'] as $filename) { // NFC
            $destinationFilePath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
            $this->assertTrue(file_exists($destinationFilePath));
            if ($filename == 'subFolder') {
                $this->assertTrue(is_dir($destinationFilePath));
            } else {
                $this->assertFalse(is_dir($destinationFilePath));
            }
        }
    }
}
