<?php

/**
 * Base server plugin class
 * 
 * This class offers various hooks which are called by the server throughout 
 * the different stages of the server's lifecycle and for each request. It 
 * should be inherited by different plugins which may implement these 
 * functions to suite a specific need.  
 *
 */

abstract class Aspamia_Http_Server_Plugin_Abstract
{
    /**
     * Configuration data
     *
     * @var array
     */
    protected $_config = array();
    
    /**
     * Server object
     *
     * @var Aspamia_Http_Server
     */
    protected $_server = null;
    
    /**
     * Create a new plugin object
     *
     * @param Zend_Config | array $config
     */
    public function __construct($config = array())
    {
        $this->setConfig($config);
    }
    
    /**
     * Set the configuration for this plugin
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } 
        
        if (! is_array($config)) {
            require_once 'Aspamia/Http/Server/Plugin/Exception.php';
            throw new Aspamia_Http_Server_Plugin_Exception("\$config is expected to be an array or a Zend_Config object, got " . gettype($config));
        }
        
        foreach($config as $k => $v) {
            $this->_config[$k] = $v;
        }
    }
    
    /**
     * Set the related server object. Called when registering a plugin
     *
     * @param Aspamia_Http_Server $server
     */
    public function setServer(Aspamia_Http_Server $server)
    {
        $this->_server = $server; 
    }
    
    /**
     * Called just after server startup
     *
     */
    public function serverStartup()
    { }
    
    /**
     * Called just before server shutdown
     *
     */
    public function serverShutdown()
    { }
    
    /**
     * Called before each request. The open connection is passed in
     *
     * @param resource $connection
     */
    public function preRequest($connection)
    { }
    
    /**
     * Called after the request is recieved. The request object is passed in
     *
     * @param Aspamia_Http_Request $request
     */
    public function postRequest(Aspamia_Http_Request $request)
    { }
    
    /**
     * Called before the response is sent. The response object is passed in
     *
     * @param Aspamia_Http_Response $response
     */
    public function preResponse(Aspamia_Http_Response $response)
    { }
    
    /**
     * Called after the response is sent. The open connection is passed in
     *
     * @param resource $connection
     */
    public function postResponse($connection)
    { }
    
    /**
     * Called in case of an error when handling the request. 
     * 
     * The caught exception is passed in
     *
     * @param Exception $ex
     */
    public function onError(Exception $ex)
    { }
}