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
     * @param string $_body
     */
    public function setBody($_body)
    {
        $this->_body = $_body;
    }

    /**
     * Set a single header or multiple headers passed as an array
     * 
     * @param string | array $header
     * @param string         $value
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
    }

    /**
     * Set the HTTP version
     * 
     * @param string $_httpVersion
     */
    public function setHttpVersion($_httpVersion)
    {
        if (! ($_httpVersion == '1.0' || $_httpVersion == '1.1')) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Unsupported HTTP version: $_httpVersion");
        }
        
        $this->_httpVersion = $_httpVersion;
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
}