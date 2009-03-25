<?php

/**
 * This code is adapted from Zend Framework's Zend_Http_Response, released 
 * under the terms of the new BSD license.
 *  
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @link       http://framework.zend.com 
 */

require_once 'Aspamia/Http/Message.php';

class Aspamia_Http_Response extends Aspamia_Http_Message 
{
    /**
     * List of all known HTTP response codes - used by responseCodeAsText() to
     * translate numeric codes to messages.
     *
     * @var array
     */
    protected static $_messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * The HTTP response code
     *
     * @var int
     */
    protected $_code;

    /**
     * The HTTP response code as string
     * (e.g. 'Not Found' for 404 or 'Internal Server Error' for 500)
     *
     * @var string
     */
    protected $_message;

    /**
     * HTTP response constructor
     *
     * In most cases, you would use Aspamia_Http_Response::fromString to parse an HTTP
     * response string and create a new Aspamia_Http_Response object.
     *
     * NOTE: The constructor no longer accepts nulls or empty values for the code and
     * headers and will throw an exception if the passed values do not form a valid HTTP
     * responses.
     *
     * If no message is passed, the message will be guessed according to the response code.
     *
     * @param integer $code    Response code (200, 404, ...)
     * @param array   $headers Headers array
     * @param string  $body    Response body
     * @param string  $version HTTP version
     * @param string  $message Response code as text
     */
    public function __construct($code, array $headers, $body = null, $version = '1.1', $message = null)
    {
        $this->setStatus($code);
        $this->setHeader($headers);
        $this->setBody($body);
        $this->setHttpVersion($version);

        // If we got the response message, set it. Else, set it according to
        // the response code
        if (is_string($message)) {
            $this->_message = $message;
        } else {
            $this->_message = self::getHttpReasonPhrase($code);
        }
    }

    /**
     * Get the HTTP response status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_code;
    }

    /**
     * Return a message describing the HTTP response code
     * (Eg. "OK", "Not Found", "Moved Permanently")
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Set the HTTP response status code
     *
     * @param  integer $code
     * @return Aspamia_Http_Response
     */
    public function setStatus($code)
    {
        if (! is_int($code) || $code < 100 || $code > 599) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid HTTP status code: '$code'");
        }
        
        $this->_code = $code;
        return $this;
    }

    /**
     * Set the HTTP reason phrase 
     * 
     * @param  string|null $message
     * @return Aspamia_Http_Response
     */
    public function setMessage($message)
    {
        if (! (is_string($message) || $message === null)) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid HTTP message phrase: '$message'");
        }
        
        $this->_message = $message;
        return $this;
    }
    
    /**
     * Get the request start line - e.g. "GET / HTTP/1.1" 
     *
     * @return string
     */
    protected function _getStartLine()
    {
        return "HTTP/{$this->_httpVersion} {$this->_code} {$this->_message}";
    }

    /**
     * Get the standard HTTP 1.1 reason phrase for a status code
     * 
     * Conforms to HTTP/1.1 as defined in RFC 2616 (except for 'Unknown')
     * See http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10 for reference
     *
     * @param  integer $code   HTTP statis code
     * @return string | null
     */
    static public function getHttpReasonPhrase($code)
    {
        if (isset(self::$_messages[$code])) {
            return self::$_messages[$code];
        } else {
            return null;
        }
    }
    
    /**
     * Create an HTTP response object from a string
     *
     * @param  string $message
     * @return Aspamia_Http_Response
     */
    static public function fromString($message)
    {
        list($headers, $body) = self::_parseString($message);
        
        // Extract and check the status line
        $statusLine = array_shift($headers);
        if (! preg_match('|^HTTP/([\d\.]+) (\d+) (.+)$|', $statusLine, $parts)) {
            require_once 'Aspamia/Http/Exception.php';
            throw new Aspamia_Http_Exception("Invalid HTTP response status line: $statusLine");
        }
        
        $response = new Aspamia_Http_Response(
            (int) $parts[2], 
            $headers, 
            $body, 
            $parts[1],
            $parts[3]
        );
        
        return $response;
    }
}
