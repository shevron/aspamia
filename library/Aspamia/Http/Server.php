<?php

require_once 'Aspamia/Http/Request.php';
require_once 'Aspamia/Http/Response.php';
require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server
{
    const DEFAULT_ADDR = '127.0.0.1';
    const DEFAULT_PORT = 8000;
    
    protected $_config = array(
        'bind_addr'      => self::DEFAULT_ADDR,
        'bind_port'      => self::DEFAULT_PORT,
        'stream_wrapper' => 'tcp',
        'handler'        => 'Aspamia_Http_Server_Handler_Mock'
    );
    
    protected $_socket = null;
    
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
            $this->_handler = $this->_config['handler'];
             
        } elseif (is_string($this->_config['handler'])) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($this->_config['handler']);
            $handler = new $this->_config['handler'];
            
            if (! $handler instanceof Aspamia_Http_Server_Handler_Abstract) {
                require_once 'Aspamia/Http/Server/Exception.php';
                throw new Aspamia_Http_Server_Exception("Provded handler is not a Aspamia_Http_Server_Handler_Abstract object");
            }
            
            $this->_handler = $handler;
        }
    }
    
    public function setConfig($config)
    {
        if ($config instanceof Aspamia_Config) {
            $config = $config->toArray();
        } 
        
        if (! is_array($config)) {
            throw new ErrorException("\$config is expected to be an array or a Aspamia_Config object, got " . gettype($config));
        }
        
        foreach($config as $k => $v) {
            $this->_config[$k] = $v;
        }
    }
        
    /**
     * TODO: Should this be adapter based?
     *
     */
    public function run()
    {
        $addr = $this->_config['stream_wrapper'] . '://' . 
                $this->_config['bind_addr'] . ':' . 
                $this->_config['bind_port'];
        
        $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        
        if (! $this->_context) {
            $this->_context = stream_context_create();
        }
        
        $this->_socket = stream_socket_server($addr, $errno, $errstr, $flags, $this->_context);
        if (! $this->_socket) {
            return false;
        }
        
        while(true) {
            if (($conn = @stream_socket_accept($this->_socket))) { 
                $this->_handle($conn);
            }
        }
        
        fclose($this->_socket);
    }
    
    public function setHandler(Aspamia_Http_Server_Handler_Abstract $handler)
    {
        $this->_handler = $handler;
    }
    
    protected function _handle($connection)
    {
        // Read and parse the HTTP request line
        $request = $this->_readRequest($connection);
        $response = $this->_handler->handle($request);
        
        // TEST
        $response->setHeader('connection', 'close');
        
        fwrite($connection, (string) $response);
    }
    
    protected function _readRequest($connection)
    {
        return Aspamia_Http_Request::read($connection); 
    }
}
