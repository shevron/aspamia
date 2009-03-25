<?php

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server_Handler_Router extends Aspamia_Http_Server_Handler_Abstract
{
    protected $_config = array(
        'routes' => array(),
    );
    
    protected $_routes = array();
    
    /**
     * Extend setConfig to set the routes 
     * 
     * @see Aspamia_Http_Server_Handler_Abstract::setConfig()
     */
    public function setConfig($config)
    {
        parent::setConfig($config);
        
        if (is_array($this->_config['routes'])) {
            foreach($this->_config['routes'] as $route) {
                if (isset($route['route']) && isset($route['handler'])) {
                    $config = (isset($route['options']) ? $route['options'] : 
                                                          array());
                    $this->addRouteHandler(
                        $route['route'], $route['handler'], $config
                    );
                }
            }
        }
    }
    
    /**
     * Set the server also for all registered handlers
     * 
     * @see Aspamia_Http_Server_Handler_Abstract::setServer()
     */
    public function setServer(Aspamia_Http_Server $server)
    {
        parent::setServer($server);
        foreach($this->_routes as $handler) {
            $handler->setServer($server);
        }
    }
    
    /**
     * Add a route handler
     * 
     * @param  string                                        $route
     * @param  Aspamia_Http_Server_Handler_Abstract | string $handler
     * @param  Zend_Config | array                           $config
     * @return Aspamia_Http_Server_Handler_Router
     */
    public function addRouteHandler($route, $handler, $config = array())
    {
        if (is_string($handler)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($handler);
            $handler = new $handler;
        }
        
        if (! $handler instanceof Aspamia_Http_Server_Handler_Abstract) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception('Provided $handler is not a handler object');
        }
        
        $handler->setConfig($config);
        if ($this->_server) $handler->setServer($this->_server);
        $this->_routes[$route] = $handler;
        
        return $this;
    }
    
    /**
     * Get a route handler (or null if does not exist)
     * 
     * @param  $route
     * @return Aspamia_Http_Server_Handler_Abstract | null
     */
    public function getRouteHandler($route)
    {
        if(isset($this->_routes[$route])) {
            return $this->_routes[$route];
        } else {
            return null;
        }
    }

    /**
     * Handle the reuqest
     * 
     * @see Aspamia_Http_Server_Handler_Abstract::handle()
     */
    public function handle(Aspamia_Http_Request $request)
    {
        if (empty($this->_routes)) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception('No routes were specified');
        }
        
        foreach ($this->_routes as $route => $handler) {
            if ($this->_routeMatch($route, $request)) {
                return $handler->handle($request);
                break;
            }
        }
        
        return self::_errorResponse(404, "The requested URL does not exist");
    }
    
    /**
     * Try to match the request to a route
     * 
     * @param  string               $route
     * @param  Aspamia_Http_Request $request
     * @return boolean
     */
    protected function _routeMatch($route, $request)
    {
        return (boolean) preg_match("#^$route#", $request->getUri());
    }
}