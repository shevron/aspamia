<?php

/**
 * Aspamia - Aspamia_Http main tests file
 * 
 */

// Load the test helper
require_once realpath(dirname(__FILE__) . '/../../../../TestHelper.php'); 

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Aspamia_Http_Server_Handler_AllTests::main');
}


// Load the sub-tests of this suite
require_once 'Aspamia/Http/Server/Handler/MockTest.php';

class Aspamia_Http_Server_Handler_AllTests
{
    public static function main()
    {  
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Aspamia - Aspamia_Http_Server_Handler');
        $suite->addTestSuite('Aspamia_Http_Server_Handler_MockTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Aspamia_Http_Server_Handler_AllTests::main') {
    Aspamia_Http_AllAspamia_Http_Server_Handler_AllTestsTests::main();
}
