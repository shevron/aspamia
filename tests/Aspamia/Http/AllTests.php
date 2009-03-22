<?php

/**
 * Aspamia - Aspamia_Http main tests file
 * 
 */

// Load the test helper
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 
	'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Aspamia_Http_AllTests::main');
}


// Load the sub-tests of this suite
require_once 'Aspamia/Http/ServerTest.php';

class Aspamia_Http_AllTests
{
    public static function main()
    {  
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Aspamia - Aspamia_Http');
        $suite->addTestSuite('Aspamia_Http_ServerTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Aspamia_Http_AllTests::main') {
    Aspamia_Http_AllTests::main();
}
