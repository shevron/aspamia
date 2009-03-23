<?php

/**
 * Aspamia HTTP Server Library for PHP
 * 
 * @author    Shahar Evron
 * @license   New BSD License, <url>
 */

/**
 * Abstract HTTP message class - 
 * 
 * The HTTP message class defines the common methods and properties of HTTP
 * request and response messages, and provides a set of utility functions for
 * handling HTTP messages. 
 * 
 */
abstract class Aspamia_Http_Message
{
    const CRLF = "\r\n";
    
    /**
     * Some useful regular expressions
     */
    const RE_CONTROL    = '[:cntrl:]';
    const RE_SEPARATOR  = '\s\(\)<>@,;:\\"\/\[\]\?=\{\}';
    
    /**
     * Message headers
     *
     * @var array
     */
    protected $_headers = array();
    
    /**
     * Message body
     *
     * @var string
     */
    protected $_body = null;
    
    /**
     * HTTP version (1.1 or 1.0)
     *
     * @var string
     */
    protected $_httpVersion = '1.1';

    /**
     * Get the body of the message as a string
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Get a specific header by it's name
     *
     * @param  string $header
     * @return string
     */
    public function getHeader($header)
    {
        $header = strtolower($header);
        if (isset($this->_headers[$header])) {
            return $this->_headers[$header];
        } else {
            return null;
        }
    }
    
    /**
     * Get the array of all headers, or the entire headers section as a string
     * 
     * @return array | string
     */
    public function getAllHeaders($as_string = false)
    {
        if ($as_string) {
            $str = $this->_getStartLine() . self::CRLF;
            foreach ($this->_headers as $header => $value) {
                $str .= ucwords($header) . ": " . $value . self::CRLF;
            }
            return $str;
            
        } else {
            return $this->_headers;
        }
    }
    
    /**
     * Get the HTTP version (1.0, 1.1 etc.)
     * 
     * @return string
     */
    public function getHttpVersion()
    {
        return $this->_httpVersion;
    }

    /**
     * Set the body of the message
     * 
     * @param  string $_body
     * @reutnr Aspamia_Http_Message
     */
    public function setBody($_body)
    {
        $this->_body = $_body;
        return $this;
    }

    /**
     * Set a single header or multiple headers passed as an array
     * 
     * @param  string | array $header
     * @param  string         $value
     * @return Aspamia_Http_Message
     */
    public function setHeader($header, $value = null)
    {
        // Handle an array of headers by simply passing them one by one to 
        // this function
        if (is_array($header)) {
            foreach($header as $k => $v) {
                if (is_int($k)) {
                    $this->setHeader($v);
                } else {
                    $this->setHeader($k, $v);
                }
            }

        // If we got a single header, set it
        } else {
            
            // No value - expect a single 'key: value' string
            if ($value === null) {
                $parts = explode(':', $header, 2);
                if (count($parts) != 2) {
                    require_once 'Aspamia/Http/Exception.php';
                    throw new Aspamia_Http_Exception("Invalid HTTP header: '$header'");
                }
                
                $header = trim($parts[0]);
                $value  = trim($parts[1]);
            }
            
            $header = strtolower($header);
            
            // Validate header name - this is not exactly according to the RFC
            // but should usually work in reality (if not, we can fix it ;)
            if (! preg_match('/^[a-z0-9\-]+$/', $header)) {
                require_once 'Aspamia/Http/Exception.php';
                throw new Aspamia_Http_Exception("Invalid HTTP header name: '$header'");
            }
            
            if (isset($this->_headers[$header])) {
                // RFC allows us to join multiple headers and comma separate them
                $this->_headers[$header] .= ", $value";
            } else {
                $this->_headers[$header] = $value;
            }
        }
        
        return $this;
    }

    /**
     * Set the HTTP version
     * 
     * @param  string $_httpVersion
     * @return Aspamia_Http_Message
     */
    public function setHttpVersion($httpVersion)
    {
        $httpVersion = (string) $httpVersion; 
        if (! ($httpVersion === '1.0' || $httpVersion === '1.1')) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Unsupported HTTP version: $httpVersion");
        }
        
        $this->_httpVersion = $httpVersion;
        return $this;
    }
    
    abstract protected function _getStartLine();

    /**
     * Stringify the message object. This could usually be sent over the wire.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAllHeaders(true) . self::CRLF . $this->getBody();
    }
    
    /**
     * Helper function for request & response 'fromString()' methods
     * 
     * Break down an HTTP message into an array of header lines and a body
     * 
     * @param  string $message
     * @return array
     */
    static protected function _parseString($message)
    {
        // Split headers from body
        $parts = preg_split('|(?:\r?\n){2}|m', $message, 2);
        if (count($parts) < 1) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid HTTP message: $message");
        }
        
        // Split headers into lines
        $headers = explode("\n", $parts[0]);
        $headers = array_map('rtrim', $headers);

        if (isset($parts[1])) {
            $body = $parts[1];
        } else {
            $body = '';
        }
        
        return array($headers, $body);
    }
}