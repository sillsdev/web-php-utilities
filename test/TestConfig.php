<?php
$rootPath = realpath(dirname(__FILE__) . '/../') . '/';
$mainRepoRootPath = realpath(dirname(__FILE__) . '/../../../../../') . '/';

define('TestMode', true);

define('TestPath', $rootPath . 'test/');
define('SourcePath', $rootPath . 'src/');
define('SimpleTestPath', $mainRepoRootPath . 'test/lib/simpletest/');

// Fake some CodeIgniter path defines
define('APPPATH', $mainRepoRootPath . 'src/');
define('BASEPATH', $mainRepoRootPath . 'lib/CodeIgniter_2.1.3/system/');

require_once APPPATH . 'helpers/loader_helper.php';
require_once APPPATH . 'vendor/autoload.php';
