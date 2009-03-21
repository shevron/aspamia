<?php

/**
 * Logger plugin class for Aspamia Server
 *
 */

require_once 'Aspamia/Http/Server/Plugin/Abstract.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

class Aspamia_Http_Server_Plugin_Logger extends Aspamia_Http_Server_Plugin_Abstract 
{
    protected $_config = array(
        'stream'   => 'php://stdout',
        'priority' => Zend_Log::INFO 
    );
    
    protected $_log = null;
    
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_log = new Zend_Log(new Zend_Log_Writer_Stream($this->_config['stream']));
        $this->_log->addFilter($this->_config['priority']);
    }
    
    /**
     * Access the log object
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }
    
    /**
     * Called just after server startup
     *
     */
    public function serverStartup()
    {
        $this->_log->info("Aspamia server starting up");
    }
    
    /**
     * Called just before server shutdown
     *
     */
    public function serverShutdown()
    {
        $this->_log->info("Aspamia server shutting down");
    }
    
    /**
     * Called before each request. The open connection is passed in
     *
     * @param resource $connection
     */
    public function preRequest($connection)
    { 
        $this->_log->debug("Recieved connection from " . 
            stream_socket_get_name($connection, true));
    }
    
    /**
     * Called after the request is recieved. The request object is passed in
     *
     * @param Aspamia_Http_Request $request
     */
    public function postRequest(Aspamia_Http_Request $request)
    { 
        $this->_log->info("Request: {$request->getMethod()} {$request->getUri()}");
    }
    
    /**
     * Called before the response is sent. The response object is passed in
     *
     * @param Aspamia_Http_Response $response
     */
    public function preResponse(Aspamia_Http_Response $response)
    { 
        $bodySize = $response->getHeader('content-length');
        $this->_log->info("Response: {$response->getStatus()} {$response->getMessage()}, $bodySize bytes of data");
    }
    
    /**
     * Called after the response is sent. The open connection is passed in
     *
     * @param resource $connection
     */
    public function postResponse($connection)
    { 
        $this->_log->debug("Closing connection to " . 
            stream_socket_get_name($connection, true));
    }
    
    /**
     * Called in case of an error when handling the request. 
     * 
     * The caught exception is passed in
     *
     * @param Exception $ex
     */
    public function onError(Exception $ex)
    { 
        $this->_log->warn("Error handling request: {$ex->getMessage()}");    
    }
}