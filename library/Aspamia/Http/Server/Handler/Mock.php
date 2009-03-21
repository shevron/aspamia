<?php

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server_Handler_Mock extends Aspamia_Http_Server_Handler_Abstract
{
    protected $_response = null;
    
    public function setResponse($response)
    {
        if ($response instanceof Aspamia_Http_Response) {
            $this->_response = $response;
        } else {
            $this->_response = Aspamia_Http_Message::fromString($response);
        }
    }
    
    public function handle(Aspamia_Http_Request $request)
    {
        if ($this->_response) {
            return $this->_response;
        } else {
            $body = (string) $request;
            
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