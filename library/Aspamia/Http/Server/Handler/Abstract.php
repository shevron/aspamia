<?php

abstract class Aspamia_Http_Server_Handler_Abstract
{
    protected $_config = array();
    
    public function __construct($config = array())
    {
       $this->setConfig($config);
    }
    
    public function setConfig($config)
    {
        if ($config instanceof Aspamia_Config) {
            $config = $config->toArray();
        } 
        
        if (! is_array($config)) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("Configuration is expected to be an array or a Aspamia_Config object, got " . gettype($config));
        }
        
        foreach ($config as $k => $v) {
            $this->_config[$k] = $v;
        }
        $this->_config = array();
    }
    
    /**
     * Handle the request, return a response 
     *
     * @param  Aspamia_Http_Request $request
     * @return Aspamia_Http_Response
     */
    abstract public function handle(Aspamia_Http_Request $request);  
}