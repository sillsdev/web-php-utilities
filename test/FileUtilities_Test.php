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
        FileUtilities::removeFolderAndAllContents('/non/existant/folder/path');
    }

    public function testRemoveFolderAndAllContents_ContentExists_ContentRemoved()
    {
        // create folder and subfolder
        $folderPath = sys_get_temp_dir() . '/testFolder';
        $subFolderPath = $folderPath . '/subfolder';
        FileUtilities::createAllFolders($subFolderPath);
        $this->assertTrue(file_exists($subFolderPath) and is_dir($subFolderPath));

        // put a file in each folder
        $filePath = $folderPath . '/test.txt';
        $subFolderFilePath = $subFolderPath . '/subFolderTest.txt';
        file_put_contents($filePath, 'test file contents');
        file_put_contents($subFolderFilePath, 'test sub folder file contents');
        $this->assertTrue(file_exists($filePath) and ! is_dir($filePath));
        $this->assertTrue(file_exists($subFolderFilePath) and ! is_dir($subFolderFilePath));

        // remove the folder and all contents
        FileUtilities::removeFolderAndAllContents($folderPath);
        $this->assertFalse(file_exists($folderPath));
    }

}

class FileUtilitiesPromoteFolderTest extends PHPUnit_Framework_TestCase
{
    protected $folderPath;
    protected $subFolderPath;
    protected $filePath;
    protected $subFolderFilePath;

    protected function setUp()
    {
        // create folder and subfolder
        $this->folderPath = sys_get_temp_dir() . '/testFolder_' . mt_rand();
        $this->subFolderPath = $this->folderPath . '/subfolder';
        FileUtilities::createAllFolders($this->subFolderPath);
        // put a file in each folder
        $this->filePath = $this->folderPath . '/test.txt';
        $this->subFolderFilePath = $this->subFolderPath . '/subFolderTest.txt';
        file_put_contents($this->filePath, 'test file contents');
        file_put_contents($this->subFolderFilePath, 'test sub folder file contents');
    }

    protected function tearDown()
    {
        FileUtilities::removeFolderAndAllContents($this->folderPath);
    }

    public function testPromoteDirContents_ParamDoesNotExist_NoThrow()
    {
        FileUtilities::promoteDirContents('/non/existant/folder/path');
    }

    public function testPromoteDirContents_ParamIsNotAFile_NoThrow()
    {
        FileUtilities::promoteDirContents($this->subFolderFilePath);
    }

    public function testPromoteDirContents_Works()
    {
        $this->assertTrue(file_exists($this->filePath) and ! is_dir($this->filePath));
        $this->assertTrue(file_exists($this->subFolderFilePath) and ! is_dir($this->subFolderFilePath));
        FileUtilities::promoteDirContents($this->subFolderPath);
        $this->assertTrue(file_exists($this->filePath) and ! is_dir($this->filePath));
        $this->assertFalse(file_exists($this->subFolderFilePath));
        // subFolderTest.txt should now be one level up
        $newSubFolderFilePath = $this->folderPath . '/subFolderTest.txt';
        $this->assertTrue(file_exists($newSubFolderFilePath) and ! is_dir($newSubFolderFilePath));
    }

}