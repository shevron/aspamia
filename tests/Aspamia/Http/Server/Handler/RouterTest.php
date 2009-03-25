<?php

require_once realpath(dirname(__FILE__) . '/../../../../TestHelper.php');

require_once 'Aspamia/Http/Server/Handler/Router.php';
require_once 'Aspamia/Http/Server/Handler/Mock.php';
require_once 'Aspamia/Http/Server/Handler/Static.php';

require_once 'Aspamia/Http/Request.php'; 

class Aspamia_Http_Server_Handler_RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that the constuctor properly sets the configuration
     * 
     */
    public function testConstructorSetsConfig()
    {
        $config = array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        );
        
        $router = new Aspamia_Http_Server_Handler_Router($config);
        $allConfig = $router->getConfig();
        
        foreach($config as $k => $v) {
            $this->assertEquals($v, $allConfig[$k]);
        }
    }
    
    /**
     * Test that we can set and get configuration
     * 
     */
    public function testSetGetConfig()
    {
        $config = array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        );
        
        $router = new Aspamia_Http_Server_Handler_Router();
        $router->setConfig($config);
        
        // Test that we can access specific config options
        foreach($config as $k => $v) {
            $this->assertEquals($v, $router->getConfig($k));
        }
    }
    
    /**
     * Test that we can set new configuration values and overwrite old ones
     * 
     */
    public function testCanSetNewConfigValues()
    {
        $config = array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        );
        
        $router = new Aspamia_Http_Server_Handler_Router();
        $router->setConfig($config);
        
        // Test that we can set a new value and the old values will be valid
        $router->setConfig(array('d' => 9));
        foreach($config as $k => $v) {
            $this->assertEquals($v, $router->getConfig($k));
        }
        $this->assertEquals(9, $router->getConfig('d'));
        
        // Test that we can overwrite values
        $router->setConfig(array('a' => 8));
        $this->assertEquals(8, $router->getConfig('a'));
    }

    /**
     * Make sure that we can add and get route handlers
     * 
     * @param  string $route
     * @param  string $handler
     * @param  array  $config
     * 
     * @dataProvider routeHandlerTripletProvider
     */
    public function testAddGetRouteHandler($route, $handler, $config = array())
    {
        $handler = new Aspamia_Http_Server_Handler_Router();
        $this->assertEquals(0, count($this->readAttribute($handler, '_routes')));
        
        $handler->addRouteHandler($route, $handler, $config);
        
        $routeh = $handler->getRouteHandler($route);
        $this->assertTrue($routeh instanceof Aspamia_Http_Server_Handler_Abstract);
            
        foreach ($config as $k => $v) {
            $this->assertEquals($v, $routeh->getConfig($k));
        }
    }
    
    /**
     * Test that handlers could be set through a configuration array
     * 
     */
    public function testSetHandlersThroughConfig()
    {
        $config = array(
            'routes' => array(
                'images' => array(
                    'route'   => '/images/.*',
                    'handler' => 'Aspamia_Http_Server_Handler_Static',
                    'options' => array(
                        'document_root' => '/baz',
                        'xx'            => 'yy'
                    )
                ),

                'phpscripts' => array(
                    'route'   => '/.*.php$',
                    'handler' => 'Aspamia_Http_Server_Handler_Cgi',
                    'options' => array(
                        'handler' => '/usr/bin/php-cgi'
                    )
                )
            )
        );
        
        $handler = new Aspamia_Http_Server_Handler_Router($config);
        
        $route = $handler->getRouteHandler('/images/.*');
        $this->assertTrue($route instanceof Aspamia_Http_Server_Handler_Static);
        $this->assertEquals('yy', $route->getConfig('xx'));
        
        $route = $handler->getRouteHandler('/.*.php$');
        $this->assertTrue($route instanceof Aspamia_Http_Server_Handler_Cgi);
        $this->assertEquals('/usr/bin/php-cgi', $route->getConfig('handler'));
    }
    
    /**
     * Data Providers
     */
    
    /**
     * Provide some route, handler, handlerConfig triplets
     * 
     * @return array
     */
    static public function routeHandlerTripletProvider()
    {
        return array(
            array(
            	'/foo/.*', 
            	'Aspamia_Http_Server_Handler_Static', 
                array('a' => 1, 'b' => 2)
            ),
            array(
                '/images/.*',
                new Aspamia_Http_Server_Handler_Mock(),
                array('c' => 3, 'd' => 4)
            ),
            array(
                '.*',
                'Aspamia_Http_Server_Handler_Handler'
            )
        );
    }
}