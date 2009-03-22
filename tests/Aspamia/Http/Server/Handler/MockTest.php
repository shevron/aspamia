<?php

require_once realpath(dirname(__FILE__) . '/../../../../TestHelper.php');

require_once 'Aspamia/Http/Server/Handler/Mock.php';

require_once 'Aspamia/Http/Request.php'; 

class Aspamia_Http_Server_Handler_MockTest extends PHPUnit_Framework_TestCase
{
    /**
     * Handler object 
     * 
     * @var Aspamia_Http_Server_Handler_Mock
     */
    protected $_handler;
    
    public function setUp()
    {
        $this->_handler = new Aspamia_Http_Server_Handler_Mock();
    }
    
    public function tearDown()
    {
        unset($this->_handler);
    }
    
    /**
     * Test that by default the mock handler returns the request as the 
     * response body
     * 
     * @param Aspamia_Http_Request $request
     * @dataProvider requestProvider
     */
    public function testDefaultResponseIsReturned($request)
    {
        $response = $this->_handler->handle($request);
        $this->assertEquals((string) $request, $response->getBody());
    }
    
    /**
     * Test that if set, a specific response is returned
     * 
     * @param Aspamia_Http_Response $expected 
     * @dataProvider responseProvider
     */
    public function testPresetResponseIsReturned($expected)
    {
        $this->_handler->setResponse($expected);
        
        $request = new Aspamia_Http_Request('GET', '/');
        $response = $this->_handler->handle($request);
        
        $this->assertSame($expected, $response);
    }
    
    /**
     * Test that the response can also be set as a string
     * 
     * @param Aspamia_Http_Response $expected 
     * @dataProvider responseProvider
     */
    public function testPresetStringResponseIsReturned($expected)
    {
        $this->_handler->setResponse((string) $expected);
        
        $request = new Aspamia_Http_Request('GET', '/');
        $response = $this->_handler->handle($request);
        
        $this->assertEquals($expected, $response);
    }
    
    /**
     * Data Providers
     */
    
    /**
     * Data provider providing some test request objects
     * 
     * @return array
     */
    static public function requestProvider()
    {
        return array(
            array(new Aspamia_Http_Request(
            	'GET', 
            	'/foo/bar/baz', 
                array(
                    "User-Agent" => "some funky browser",
                    "Accept" => 'text/plain'
                )
            )),
            
            array(new Aspamia_Http_Request(
            	'POST', 
            	'/some/url', 
                array(
                    "X-BlaBla" => "some text"
                ),
                'crapcrap=blabla&bazbaz=quaqua'
            )),
            
            array(new Aspamia_Http_Request(
            	'OPTIONS', 
            	'*', 
                array(
                    "User-Agent" => "some funky browser",
                    "Accept" => 'text/plain'
                )
            )),
        );
    }
    
    /**
     * Data provider providing some test response objects
     * 
     * @return array
     */
    static public function responseProvider()
    {
        return array(
            array(new Aspamia_Http_Response(
              200,
              array(
                  'Content-type'   => 'text/html',
                  'Content-length' => 15,
              ),
              '<h1>hello!</h1>'
            )),
            
            array(new Aspamia_Http_Response(
                304,
                array(
                    'Last-modified' => date(DATE_RFC822)
                )
            )),
            
            array(new Aspamia_Http_Response(
                404,
                array(
                    'Content-type'   => 'text/plain',
                    'Content-length' => 14 
                ),
                'File not found'
            ))
        );
    }
}