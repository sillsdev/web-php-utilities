<?php
require_once dirname(__FILE__) . '/TestConfig.php';
require_once SimpleTestPath . 'autorun.php';

class AllTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->addFile(TestPath . 'FileUtilities_Test.php');
    }
}
