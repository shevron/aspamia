<?php

/**
 * Aspamia - main tests file
 * 
 */

// Load the test helper
require_once dirname(dirname(__FILE__)) . 
    DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Aspamia_AllTests::main');
}


// Load the sub-tests of this suite
require_once 'Aspamia/Http/AllTests.php';

class Aspamia_AllTests
{
    public static function main()
    {  
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Aspamia');
        $suite->addTest(Aspamia_Http_AllTests::suite());
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Aspamia_AllTests::main') {
    Aspamia_AllTests::main();
}
