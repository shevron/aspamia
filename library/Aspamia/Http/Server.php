<?php

require_once 'Aspamia/Http/Request.php';
require_once 'Aspamia/Http/Response.php';
require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server
{
    const ASPAMIA_VERSION = '0.0.1';
    const DEFAULT_ADDR    = '127.0.0.1';
    const DEFAULT_PORT    = 8000;
    
    protected $_config = array(
        'bind_addr'      => self::DEFAULT_ADDR,
        'bind_port'      => self::DEFAULT_PORT,
        'stream_wrapper' => 'tcp',
        'handler'        => 'Aspamia_Http_Server_Handler_Mock'
    );
    
    /**
     * Array of registered plugins
     *
     * @var unknown_type
     */
    protected $_plugins = array();
    
    /**
     * Main listening socket
     *
     * @var resource
     */
    protected $_socket = null;
    
    /**
     * Stream context (if set)
     *
     * @var resource
     */
    protected $_context = null;
    
    /**
     * Request handler object
     *
     * @var Aspamia_Http_Server_Handler_Abstract
     */
    protected $_handler = null;
    
    public function __construct($config = array())
    {
        $this->setConfig($config);
        
        // Initialize handler
        if ($this->_config['handler'] instanceof Aspamia_Http_Server_Handler_Abstract) {
            $this->setHandler($handler);
             
        } elseif (is_string($this->_config['handler'])) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($this->_config['handler']);
            $handler = new $this->_config['handler'];
            
            if (! $handler instanceof Aspamia_Http_Server_Handler_Abstract) {
                require_once 'Aspamia/Http/Server/Exception.php';
                throw new Aspamia_Http_Server_Exception("Provded handler is not a Aspamia_Http_Server_Handler_Abstract object");
            }
            
            $this->setHandler($handler);
        }
    }
    
    /**
     * Set the configuration data for this server object
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } 
        
        if (! is_array($config)) {
            require_once 'Aspamia/Http/Server/Exception.php';
            throw new Aspamia_Http_Server_Exception("\$config is expected to be an array or a Zend_Config object, got " . gettype($config));
        }
        
        foreach($config as $k => $v) {
            $this->_config[$k] = $v;
        }
    }
    
    /**
     * Get a configuration option value or the entire configuration array
     * 
     * @param  null|string $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->_config;
        } elseif (isset($this->_config[$key])) {
            return $this->_config[$key];
        } else {
            return null;
        }
    }
        
    /**
     * Register a plug-in
     *
     * @param Aspamia_Http_Server_Plugin_Abstract $plugin
     */
    public function registerPlugin(Aspamia_Http_Server_Plugin_Abstract $plugin)
    {
        $plugin->setServer($this);
        $this->_plugins[] = $plugin;
    }
    
    /**
     * Get the bind address for the server
     *
     * @return string
     */
    public function getBindAddr()
    {
        return $this->_config['stream_wrapper'] . '://' . 
               $this->_config['bind_addr'] . ':' . 
               $this->_config['bind_port'];   
    }
    /**
     * TODO: Should this be adapter based?
     *
     */
    public function run()
    {
        $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        
        if (! $this->_context) {
            $this->_context = stream_context_create();
        }
        
        $errno = 0;
        $errstr = null;
        $this->_socket = stream_socket_server($this->getBindAddr(), $errno, $errstr, $flags, $this->_context);
        if (! $this->_socket) {
            require_once 'Aspamia/Http/Server/Exception.php';
            $message = "Unable to bind to '$addr'";
            if ($errno || $errstr) $message .= ": [#$errno] $errstr";
            throw new Aspamia_Http_Server_Exception($message);
        }
        
        $this->_callServerStartupPlugins();
        
        while(true) {
            if (($conn = @stream_socket_accept($this->_socket))) {
                try { 
                    $this->_handle($conn);
                } catch (Aspamia_Http_Exception $ex) {
                    $this->_callOnErrorPlugins($ex);
                    // Supress exception and continue looping
                } catch (Exception $ex) {
                     $this->_callOnErrorPlugins($ex);
                     throw $ex;   
                }
            }
        }
        
        fclose($this->_socket);
        $this->_callServerShutdownPlugins();
    }
    
    /**
     * Set the handler object
     * 
     * @param  Aspamia_Http_Server_Handler_Abstract $handler
     */
    public function setHandler(Aspamia_Http_Server_Handler_Abstract $handler)
    {
        $this->_handler = $handler;
        $handler->setServer($this);
    }
    
    protected function _handle($connection)
    {
        // Read and parse the HTTP request line
        $this->_callPreRequestPlugins($connection);
        $request = $this->_readRequest($connection);
        $this->_callPostRequestPlugins($request);
        
        $response = $this->_handler->handle($request);
        $this->_callPreResponsePlugins($response);
        
        $serverSignature = 'Aspamia/' . self::ASPAMIA_VERSION . ' ' . 
                           'PHP/' . PHP_VERSION;
        
        $response->setHeader(array(
        	'Server' => $serverSignature,
            'Date'   => date(DATE_RFC1123)
        ));
        
        // TODO: Right now only 'close' is working, make keep-alive work too.  
        $response->setHeader('connection', 'close');
        
        fwrite($connection, (string) $response);
        $this->_callPostResponsePlugins($connection);
    }
    
    protected function _readRequest($connection)
    {
        return Aspamia_Http_Request::read($connection); 
    }
    
    /**
     * Call the server startup hook of all plugins 
     *
     */
    protected function _callServerStartupPlugins()
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->serverStartup();
    }
    
    /**
     * Call the server shutdown hook of all plugins
     *
     */
    protected function _callServerShutdownPlugins()
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->serverShutdown();
    }
    
    /**
     * Call the on-error hook of all plugins
     *
     * @param Exception $ex
     */
    protected function _callOnErrorPlugins(Exception $ex)
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->onError($ex);
    }
    
    /**
     * Call the pre-request hook of all plugins
     *
     * @param resource $conn
     */
    protected function _callPreRequestPlugins($conn)
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->preRequest($conn);
    }
    
    /**
     * Call the post-request hook of all plugins
     *
     * @param Aspamia_Http_Request $request
     */
    protected function _callPostRequestPlugins(Aspamia_Http_Request $request)
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->postRequest($request);
    }
    
    /**
     * Call the pre-response hook of all plugins
     *
     * @param Aspamia_Http_Response $response
     */
    protected function _callPreResponsePlugins(Aspamia_Http_Response $response)
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->preResponse($response);
    }
    
    /**
     * Call the post-response hook of all plugins
     *
     * @param resource $conn
     */
    protected function _callPostResponsePlugins($conn)
    {
        foreach ($this->_plugins as $plugin) /* @var $plugin Aspamia_Http_Server_Plugin_Abstract */
            $plugin->postResponse($conn);
    }
}
