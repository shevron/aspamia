<?php

/**
 * Aspamia - Test helper file
 * 
 * This file should be included by all unit tests before running them. It sets
 * the proper environment, include paths, configuration etc. for running tests.
 */

/**
 * This file was adapted from Zend Framework which is distrubted under the 
 * New BSD License and (c) 2005-2008 Zend Technologies Inc. 
 * See http://framework.zend.com/license/new-bsd for more info.
 *
 */

// Include PHPUnit dependencies
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

// Set error reporting level
error_reporting( E_ALL | E_STRICT );

// Determine the root, library, and tests directories 
$root_path    = dirname(__FILE__) . '/..';
$library_path = "$root_path/library";
$tests_path   = "$root_path/tests";

// Omit from code coverage reports the contents of the tests directory
foreach (array('php', 'phtml', 'csv') as $suffix) {
    PHPUnit_Util_Filter::addDirectoryToFilter($tests_path, ".$suffix");
}

// Set the include path to include Aspamia files first
$path = array(
    $library_path,
    $tests_path,
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

/**
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($tests_path . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $tests_path . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $tests_path . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

// Add the library files to the code coverage reports
if (defined('TESTS_GENERATE_REPORT') && TESTS_GENERATE_REPORT === true &&
    version_compare(PHPUnit_Runner_Version::id(), '3.1.6', '>=')) {
    PHPUnit_Util_Filter::addDirectoryToWhitelist($library_path);
}

// Unset global variables that are no longer needed.
unset($root_path, $library_path, $tests_path, $path);
