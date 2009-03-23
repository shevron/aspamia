<?php

require_once 'Aspamia/Http/Message.php';

class Aspamia_Http_Request extends Aspamia_Http_Message 
{
    /**
     * Well-known HTTP methods
     */
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';
    const TRACE   = 'TRACE';
    
    /**
     * Request method
     *
     * @var string
     */
    protected $_method;
    
    /**
     * Requested URI
     *
     * @var string
     */
    protected $_uri;
    
    /**
     * Open connection socket, may be used for efficient body reading
     *
     * @var resource
     */
    protected $_socket;
    
    /**
     * Create a new request object
     *
     * @param string $method
     * @param string $uri
     * @param array  $headers
     * @param string $body
     */
    public function __construct($method, $uri, array $headers = array(), $body = null)
    {
        $this->setMethod($method);
        $this->setUri($uri);
        $this->setHeader($headers);
        $this->setBody($body);
    }
    
    /**
     * Get the request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;    
    }
    
    /**
     * Get the request URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }
    
    /**
     * Set the request method
     *
     * @param  string $method
     * @return Aspamia_Http_Request
     */
    public function setMethod($method)
    {
        $regex = '/^[^' . self::RE_CONTROL . self::RE_SEPARATOR . ']+$/';
         
        if (! preg_match($regex, $method)) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid HTTP method: '$method'");
        }
        
        $this->_method = $method;
        return $this;
    }
    
    /**
     * Validate and set the request URI
     *
     * @param  string $uri
     * @return Aspamia_Http_Request
     */
    public function setUri($uri)
    {
        // Validate the URI
        $uriValid = false;
        
        if ($this->_method == 'CONNECT') {
            if (preg_match('/^[a-zA-Z\-0-9\.]+:\d+$/', $uri)) {
                // 'Authority' URL can be used for CONNECT method
                $uriValid = true;
            }
            
        } else {
            if ($uri{0} == '/') { 
                // Absolute Path
                // TODO: Validate path
                $uriValid = true;
                
            } elseif (strpos($uri, 'http') === 0) { 
                // Absolute URL
                // TODO: Validate URL
                $uriValid = true; 
                
            } elseif ($uri == '*' && $this->_method == 'OPTIONS') { 
                // * - used for OPTIONS method
                $uriValid = true;
                
            }
        }

        if (! $uriValid) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid Request URI: '$uri'");
        }
        
        $this->_uri = $uri;
        return $this;
    }
    
    /**
     * Get the request start line - e.g. "GET / HTTP/1.1" 
     *
     * @return string
     */
    protected function _getStartLine()
    {
        return "{$this->_method} {$this->_uri} HTTP/{$this->_httpVersion}";
    }
    
    /**
     * Read an HTTP request from an open socket and return it as an object
     *
     * Will not read the full body unless explicitly asked to.
     * 
     * @param  resource $connection
     * @param  boolean  $read_body
     * @return Aspamia_Http_Request
     */
    static public function read($connection, $read_body = false)
    {
        $headerlines = self::_readHeaders($connection);
        if (empty($headerlines)) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Unable to read request: headers are empty");
        }
        
        $requestline = explode(' ', array_shift($headerlines), 3);
        if (! count($requestline) == 3) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Unable to read request: invalid HTTP request line '{$headerlines[0]}'");
        }
        
        $protocol = explode('/', $requestline[2]);
        if (! ($protocol[0] == 'HTTP' && ($protocol[1] == '1.1' || $protocol[1] == '1.0'))) {
            require_once 'Aspamia/Http/Exception.php'; 
            throw new Aspamia_Http_Exception("Unsupported protocol version: {$requestline[2]}");
        }
        
        $method = strtoupper($requestline[0]);
        $uri = $requestline[1];
        
        $headers = array();
        foreach ($headerlines as $line) {
            $header = explode(":", $line, 2);
            if (! count($header) == 2) {
                require_once 'Aspamia/Http/Exception.php';
                throw new Aspamia_Http_Exception("Invalid HTTP header format: $line");
            }
            
            $headers[strtolower(trim($header[0]))] = trim($header[1]);
        }

        $request = new Aspamia_Http_Request($method, $uri, $headers);
        $request->_httpVersion = $protocol[1];
        $request->_socket = $connection;
        
        if ($read_body) {
            $request->_readBody();
        }
        
        return $request;
    }
    
    /**
     * Read the entire headers section of the response, returning it as an
     * array of lines
     *
     * @param  resource $connection
     * @return array
     */
    static protected function _readHeaders($connection)
    {
        $headers = array();
        while (($line = @fgets($connection)) !== false) {
            $line = trim($line);
            if (! $line) break; 
            $headers[] = $line;
        }
        
        return $headers; 
    }
}
