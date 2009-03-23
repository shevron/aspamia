<?php

require_once realpath(dirname(__FILE__) . '/../../TestHelper.php');

require_once 'Aspamia/Http/Request.php';

class Aspamia_Http_RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that we can set and get valid HTTP methods
     * 
     * @param  string $method
     * @dataProvider validMethodProvider
     */
    public function testGetSetMethod($method)
    {
        $request = new Aspamia_Http_Request('TEST', '/test');
        $this->assertEquals('TEST', $request->getMethod());
        
        $request->setMethod($method);
        $this->assertEquals($method, $request->getMethod());
    }
    
    /**
     * Test that we are unable to set invalid methods
     * 
     * @param  string $method
     * @dataProvider invalidMethodProvider
     * @expectedException Aspamia_Http_Exception
     */
    public function testInvalidMethod($method)
    {
        $request = new Aspamia_Http_Request($method, '/test');
    }
    
    /**
     * Test that we can set and get valid HTTP URIs
     * 
     * @param  string $method
     * @param  string $uri
     * @dataProvider validUriMethodProvider
     */
    public function testGetSetUri($uri, $method)
    {
        $request = new Aspamia_Http_Request('TEST', '/test');
        $this->assertEquals('/test', $request->getUri());
        
        $request->setMethod($method);
        $request->setUri($uri);
        $this->assertEquals($uri, $request->getUri());
    }
    
    /**
     * Test that we are unable to set invalid URIs
     * 
     * @param  string $uri
     * @param  string $method
     * @dataProvider invalidUriMethodProvider
     * @expectedException Aspamia_Http_Exception
     */
    public function testInvalidUri($uri, $method)
    {
        $request = new Aspamia_Http_Request($method, $uri);
    }
    
    /**
     * Data Providers
     */
    
    /**
     * Provider of valid HTTP methods
     * 
     * @return array
     */
    static public function validMethodProvider()
    {
        return array(
            array('GET'),
            array('TRACE'),
            array('PROPFIND'),
            array('MKCOL'),
            array('X-MS-ENUMATTS'),
        );
    }
    
    /**
     * Provider of invalid HTTP methods
     * 
     * @return array
     */
    static public function invalidMethodProvider()
    {
        return array(
            array('WITH SPACE'),
            array('FOO=BAR'),
            array(null),
            array('/foo')
        );
    }
    
    /**
     * Provide valid pairs of URIs and methods
     * 
     * @return array
     */
    static public function validUriMethodProvider()
    {
        return array(
            array('/foo', 'GET'),
            array('/', 'POST'),
            array('*', 'OPTIONS'),
            array('baz:1234', 'CONNECT'),
            array('http://example.com', 'GET'),
            array('/a/b/?d=e&f=g', 'FAKEMETHOD'),
            array('/some+encoded/%aa%bb%cc/text', 'HEAD')
        );
    }
    
    /**
     * Provide invalid pairs of URIs and methods
     * 
     * @return array
     */
    static public function invalidUriMethodProvider()
    {
        return array(
            array('some crap', 'GET'),
            array('*', 'POST'),
            array('ftp://example.com/baz/bar', 'GET'),
            array('host:433', 'HEAD'),
            array('http://example.com/path', 'CONNECT')
        );
    }
}
