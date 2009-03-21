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
        $this->setCode = $code;
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
    public static function getHttpReasonPhrase($code)
    {
        if (isset(self::$_messages[$code])) {
            return self::$_messages[$code];
        } else {
            return null;
        }
    }

//    /**
//     * Extract the response code from a response string
//     *
//     * @param string $response_str
//     * @return int
//     */
//    public static function extractCode($response_str)
//    {
//        preg_match("|^HTTP/[\d\.x]+ (\d+)|", $response_str, $m);
//
//        if (isset($m[1])) {
//            return (int) $m[1];
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * Extract the HTTP message from a response
//     *
//     * @param string $response_str
//     * @return string
//     */
//    public static function extractMessage($response_str)
//    {
//        preg_match("|^HTTP/[\d\.x]+ \d+ ([^\r\n]+)|", $response_str, $m);
//
//        if (isset($m[1])) {
//            return $m[1];
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * Extract the HTTP version from a response
//     *
//     * @param string $response_str
//     * @return string
//     */
//    public static function extractVersion($response_str)
//    {
//        preg_match("|^HTTP/([\d\.x]+) \d+|", $response_str, $m);
//
//        if (isset($m[1])) {
//            return $m[1];
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * Extract the headers from a response string
//     *
//     * @param string $response_str
//     * @return array
//     */
//    public static function extractHeaders($response_str)
//    {
//        $headers = array();
//        
//        // First, split body and headers
//        $parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
//        if (! $parts[0]) return $headers;
//        
//        // Split headers part to lines
//        $lines = explode("\n", $parts[0]);
//        unset($parts);
//        $last_header = null;
//
//        foreach($lines as $line) {
//            $line = trim($line, "\r\n");
//            if ($line == "") break;
//
//            if (preg_match("|^([\w-]+):\s+(.+)|", $line, $m)) {
//                unset($last_header);
//                $h_name = strtolower($m[1]);
//                $h_value = $m[2];
//
//                if (isset($headers[$h_name])) {
//                    if (! is_array($headers[$h_name])) {
//                        $headers[$h_name] = array($headers[$h_name]);
//                    }
//
//                    $headers[$h_name][] = $h_value;
//                } else {
//                    $headers[$h_name] = $h_value;
//                }
//                $last_header = $h_name;
//            } elseif (preg_match("|^\s+(.+)$|", $line, $m) && $last_header !== null) {
//                if (is_array($headers[$last_header])) {
//                    end($headers[$last_header]);
//                    $last_header_key = key($headers[$last_header]);
//                    $headers[$last_header][$last_header_key] .= $m[1];
//                } else {
//                    $headers[$last_header] .= $m[1];
//                }
//            }
//        }
//
//        return $headers;
//    }
//
//    /**
//     * Extract the body from a response string
//     *
//     * @param string $response_str
//     * @return string
//     */
//    public static function extractBody($response_str)
//    {
//        $parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
//        if (isset($parts[1])) { 
//            return $parts[1];
//        }
//        return '';
//    }
//
//    /**
//     * Decode a "chunked" transfer-encoded body and return the decoded text
//     *
//     * @param string $body
//     * @return string
//     */
//    public static function decodeChunkedBody($body)
//    {
//        $decBody = '';
//        
//        while (trim($body)) {
//            if (! preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $body, $m)) {
//                require_once 'Aspamia/Http/Exception.php';
//                throw new Aspamia_Http_Exception("Error parsing _body - doesn't seem to be a chunked _message");
//            }
//
//            $length = hexdec(trim($m[1]));
//            $cut = strlen($m[0]);
//
//            $decBody .= substr($body, $cut, $length);
//            $body = substr($body, $cut + $length + 2);
//        }
//
//        return $decBody;
//    }
//
//    /**
//     * Decode a gzip encoded message (when Content-encoding = gzip)
//     *
//     * Currently requires PHP with zlib support
//     *
//     * @param string $body
//     * @return string
//     */
//    public static function decodeGzip($body)
//    {
//        if (! function_exists('gzinflate')) {
//            require_once 'Aspamia/Http/Exception.php';
//            throw new Aspamia_Http_Exception('Unable to decode gzipped response ' . 
//                '_body: perhaps the zlib extension is not loaded?'); 
//        }
//
//        return gzinflate(substr($body, 10));
//    }
//
//    /**
//     * Decode a zlib deflated message (when Content-encoding = deflate)
//     *
//     * Currently requires PHP with zlib support
//     *
//     * @param string $body
//     * @return string
//     */
//    public static function decodeDeflate($body)
//    {
//        if (! function_exists('gzuncompress')) {
//            require_once 'Aspamia/Http/Exception.php';
//            throw new Aspamia_Http_Exception('Unable to decode deflated response ' . 
//                '_body: perhaps the zlib extension is not loaded?'); 
//        }
//
//        return gzuncompress($body);
//    }
//
//    /**
//     * Create a new Aspamia_Http_Response object from a string
//     *
//     * @param string $response_str
//     * @return Aspamia_Http_Response
//     */
//    public static function fromString($response_str)
//    {
//        $code    = self::extractCode($response_str);
//        $headers = self::extractHeaders($response_str);
//        $body    = self::extractBody($response_str);
//        $version = self::extractVersion($response_str);
//        $message = self::extractMessage($response_str);
//
//        return new Aspamia_Http_Response($code, $headers, $body, $version, $message);
//    }
    
//    /**
//     * Check whether the response is an error
//     *
//     * @return boolean
//     */
//    public function isError()
//    {
//        $restype = floor($this->_code / 100);
//        if ($restype == 4 || $restype == 5) {
//            return true;
//        }
//
//        return false;
//    }
//
//    /**
//     * Check whether the response in successful
//     *
//     * @return boolean
//     */
//    public function isSuccessful()
//    {
//        $restype = floor($this->_code / 100);
//        if ($restype == 2 || $restype == 1) { // Shouldn't 3xx count as success as well ???
//            return true;
//        }
//
//        return false;
//    }
//
//    /**
//     * Check whether the response is a redirection
//     *
//     * @return boolean
//     */
//    public function isRedirect()
//    {
//        $restype = floor($this->_code / 100);
//        if ($restype == 3) {
//            return true;
//        }
//
//        return false;
//    }
}
