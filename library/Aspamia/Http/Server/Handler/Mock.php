<?php

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server_Handler_Mock extends Aspamia_Http_Server_Handler_Abstract
{
    protected $_response = null;
    
    public function setResponse($response)
    {
        // Set the response from either a string or a response object
    }
    
    public function handle(Aspamia_Http_Request $request)
    {
        if ($this->_response) {
            return $this->_response;
        } else {
            $body = $request->getUri() . "\r\n";
            
            return new Aspamia_Http_Response(
                200, 
                array(
                	'X-Powered-By'   => 'Aspamia_Http_Server/MockHandler',
                    'Content-type'   => 'text/plain',
                    'Content-length' => strlen($body), 
                ),
                $body
            );
        }
    }
}