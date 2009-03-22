<?php

require_once realpath(dirname(__FILE__) . '/../../TestHelper.php');

require_once 'Aspamia/Http/Response.php';

class Aspamia_Http_ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that we can set and get the response status code
     *  
     * @param integer $code
     * @param string  $message
     * @dataProvider statusCodeMessageProvider
     */
    public function testSetGetStatus($code, $message)
    {
        // Create object
        $response = new Aspamia_Http_Response($code, array());
        
        // Test getting
        $this->assertEquals($code, $response->getStatus());
        
        // Test setting
        $code++;
        $response->setStatus($code);
        $this->assertEquals($code, $response->getStatus());
    }
    
    /**
     * Test that an exception is thrown for invalid status codes
     * 
     * Valid status codes according to the RFC are numbers between 100 and 599
     * 
     * @expectedException Aspamia_Http_Exception
     * @dataProvider      invalidStatusCodeProvider
     */
    public function testCantSetInvalidStatus($code)
    {
        $response = new Aspamia_Http_Response($code, array());
    }
    
	/**
     * Test that the default reason phrase is set if not specifying one
     *  
     * @param integer $code
     * @param string  $message
     * @dataProvider statusCodeMessageProvider
     */
    public function testDefaultReasonPhraseSet($code, $message)
    {
        // Create object
        $response = new Aspamia_Http_Response($code, array());
        
        // Test getting
        $this->assertEquals($message, $response->getMessage());
    }
    
    /**
     * Test that a custom reason phrase can be set and accessed
     * 
     * @param  integer $code
     * @param  string  $message
     * @dataProvider fakeReasonPhraseProvider
     */
    public function testCustomReasonPhraseSet($code, $message)
    {
        $response = new Aspamia_Http_Response($code, array(), '', '1.1', $message);
        
        // Test code and message are correct
        $this->assertEquals($code, $response->getStatus());
        $this->assertEquals($message, $response->getMessage());
    }
    
    /**
     * Test that we can create request objects from strings
     * 
     * @param string  $requestFile
     * @param integer $code
     * @param string  $xtest
     * @param string  $body
     * @dataProvider  responseTestFileProvider
     */
    public function testFromString($requestFile, $code, $xtest, $body)
    {
        $str = file_get_contents(dirname(__FILE__) . '/_files/' . $requestFile);
        $response = Aspamia_Http_Response::fromString($str);
        
        $this->assertEquals($code, $response->getStatus());
        $this->assertEquals($xtest, $response->getHeader('x-test'));
        $this->assertEquals(substr($body, 0, 10), 
                            substr($response->getBody(), 0, 10));
    }
    
    /**
     * Test that we can convert a request object back to a string
     * 
     * @param string  $requestFile
     * @param integer $code
     * @param string  $xtest
     * @param string  $body
     * @dataProvider  responseTestFileProvider
     */
    public function testToString($requestFile, $code, $xtest, $body)
    {
        $str = file_get_contents(dirname(__FILE__) . '/_files/' . $requestFile);
        $response = Aspamia_Http_Response::fromString($str);
        $this->assertEquals($str, (string) $response);
    }
    
    /**
     * Test that the class can properly map HTTP reason phrases
     *  
     * @param integer $code
     * @param string  $message
     * @dataProvider statusCodeMessageProvider
     */
    public function testGetHttpReasonPhrase($code, $message)
    {
        $this->assertEquals($message, 
            Aspamia_Http_Response::getHttpReasonPhrase($code));
    }
    
    /**
     * Test that trying to get a non-existing reason phrase returns null
     * 
     */
    public function testGetNotExistingReasonPhrase()
    {
        $this->assertNull(Aspamia_Http_Response::getHttpReasonPhrase(599));
    }
    
    /**
     * Data Providers
     */
    
    /**
     * Provide some status codes with their standard message
     * 
     * @return array
     */
    static public function statusCodeMessageProvider()
    {
        return array(
            array(101, 'Switching Protocols'),    
            array(200, 'OK'),
            array(206, 'Partial Content'),
            array(301, 'Moved Permanently'),
            array(402, 'Payment Required'),
            array(404, 'Not Found'),
            array(501, 'Not Implemented'),
            array(505, 'HTTP Version Not Supported')
        );
    }
    
    /**
     * Provider for some fake, non-standard reason phrases
     * 
     * @return array
     */
    static public function fakeReasonPhraseProvider()
    {
        return array(
            array(100, 'Here It Comes'),
            array(200, 'Heres Your Porn'),
            array(300, 'Come Back Some Other Time'),
            array(400, 'MSIE Not Allowed'),
            array(500, 'Server On Fire')
        );
    }
    
    /**
     * Provide some invalid HTTP status codes
     * 
     * @return array
     */
    static public function invalidStatusCodeProvider()
    {
        return array(
            array(null),
            array('x'),
            array(600),
            array(1235),
            array('some garbage'),
            array(0),
            array(12)
        );
    }
    
    /**
     * Provide HTTP response file names along with expected code, header and 
     * beginning of body
     * 
     * @return array
     */
    static public function responseTestFileProvider()
    {
        return array(
            array('http_response_01.txt', 200, '01', 'Here is some body for'),
            array('http_response_02.txt', 404, '02', '<!DOCTYPE HTML PUBLIC'),
            array('http_response_03.txt', 302, '03', 'Error: Zend Platform '),
            array('http_response_04.txt', 404, '04', ''),
        );
    }
}