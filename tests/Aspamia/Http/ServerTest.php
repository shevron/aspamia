<?php

require_once realpath(dirname(__FILE__) . '/../../TestHelper.php');
require_once 'Aspamia/Http/Server.php';

class Aspamia_Http_ServerTest extends PHPUnit_Framework_TestCase
{
    public function testSetConfigSetsConfig()
    {
        $config = array(
            'foo'       => 'bar',
            'bind_addr' => '1.2.3.4',
        	'bind_port' => 9988
        );
        
        $server = new Aspamia_Http_Server();
        $server->setConfig($config);
        
        $serverConfig = $this->readAttribute($server, '_config');
        
        foreach($config as $k => $v) {
            $this->assertEquals($v, $serverConfig[$k]);
        }
    }
    
    public function testSetConfigKeepsDefaults()
    {
        $config = array(
            'foo'       => 'bar',
        	'bind_port' => 9988
        );
        
        $server = new Aspamia_Http_Server();
        $server->setConfig($config);
        
        $serverConfig = $this->readAttribute($server, '_config');
        $this->assertEquals(Aspamia_Http_Server::DEFAULT_ADDR, 
            $serverConfig['bind_addr']);
    }
    
    public function testConstructorSetsConfig()
    {
        $config = array(
            'foo'       => 'bar',
            'bind_addr' => '1.2.3.4',
        	'bind_port' => 9988
        );
        
        $server = new Aspamia_Http_Server($config);
        $serverConfig = $this->readAttribute($server, '_config');
        
        foreach($config as $k => $v) {
            $this->assertEquals($v, $serverConfig[$k]);
        }
    }
    
    /**
     * Test that you can set the bind address of the server through the config
     * 
     * @param string  $stream
     * @param string  $host
     * @param integer $port
     * @param string  $ex
     * 
     * @dataProvider bindAddressDataProvider
     */
    public function testBindAddressSetGet($stream, $host, $port, $ex)
    {
        $server = new Aspamia_Http_Server(array(
            'stream_wrapper' => $stream,
            'bind_addr'      => $host,
            'bind_port'      => $port
        ));
        
        $this->assertEquals($ex, $server->getBindAddr());
    }
    
    /**
     * Data Providers
     */
    
    /**
     * Data provider for stream wrapper, host, port triplets and the expected
     * bind address
     * 
     * @return array
     */
    static public function bindAddressDataProvider()
    {
        return array(
            array('tcp', '127.0.0.8', 1234, 'tcp://127.0.0.8:1234'),
            array('ssl', '0.0.0.0',   443, 'ssl://0.0.0.0:443'),
            array('test', 'myhost.bar.baz', 54321, 'test://myhost.bar.baz:54321')
        );
    }
}
