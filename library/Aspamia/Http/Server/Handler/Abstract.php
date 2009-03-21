<?php

abstract class Aspamia_Http_Server_Handler_Abstract
{
    /**
     * Configuration data
     *
     * @var array
     */
    protected $_config = array();
    
    public function __construct($config = array())
    {
       $this->setConfig($config);
    }
    
    /**
     * Set the handler configuration
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } 
        
        if (! is_array($config)) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("Configuration is expected to be an array or a Zend_Config object, got " . gettype($config));
        }
        
        foreach ($config as $k => $v) {
            $this->_config[$k] = $v;
        }
    }
    
    /**
     * Create an error HTTP response message based on code and message
     *
     * @param  integer $code
     * @param  string  $message
     * @return Aspamia_Http_Response
     */
    static protected function _errorResponse($code, $message)
    {
        $headers = array(
            'content-type'   => 'text/plain',
            'content-length' => strlen($message),
            'x-powered-by'   => 'Aspamia_Http_Server/StaticHandler'
        );
        
        return new Aspamia_Http_Response($code, $headers, $message);
    }
    
    /**
     * Handle the request, return a response 
     *
     * @param  Aspamia_Http_Request $request
     * @return Aspamia_Http_Response
     */
    abstract public function handle(Aspamia_Http_Request $request);  
}