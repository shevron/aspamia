<?php

/**
 * Aspamia HTTP Server Library for PHP
 * 
 * @author    Shahar Evron
 * @license   New BSD License, <url>
 */

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

/**
 * CGI handler 
 * 
 * This handler implements the CGI/1.1 protocol, allowing the web server to
 * execute programs in order to generate dynamic pages. Through CGI, you can
 * enable PHP as well as other dynamic languages (Python, Perl) to generate web
 * pages. 
 * 
 */
class Aspamia_Http_Server_Handler_Cgi extends Aspamia_Http_Server_Handler_Abstract
{
    protected $_config = array(
        'handler'        => null,
        'handler_script' => null,
        'document_root'  => null,
    );
    
    /**
     * Handle the request
     *
     * @param  Aspamia_Http_Request $request
     * @return Aspamia_Http_Response
     */
    public function handle(Aspamia_Http_Request $request)
    {
        if (! $this->_config['handler']) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("No CGI handler was set");
        }
        
        if (isset($this->_config['document_root'])) {
            $document_root = $this->_config['document_root'];
        } else {
            $document_root = getcwd();
        }
        
        // Parse the URI
        $urlInfo = parse_url($request->getUri());
        
        // Build the translated path
        $script_path = rtrim($document_root, '/') . 
            (isset($this->_config['handler_script']) ? 
            	'/' . ltrim($this->_config['handler_script']) :
                $urlInfo['path'] 
            );
        
        // Set up the environment
        $environment = array(
            'SERVER_SOFTWARE '  => 'Aspamia/' . 
                Aspamia_Http_Server::ASPAMIA_VERSION,
        	'SERVER_NAME'       => $request->getLocalAddress(),
            'SERVER_PROTOCOL'   => 'HTTP/' . $request->getHttpVersion(),
            'SERVER_PORT'       => $this->_server->getConfig('bind_port'),
        	'DOCUMENT_ROOT'     => $document_root,
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'REQUEST_METHOD'    => $request->getMethod(),
            'REQUEST_URI'       => $request->getUri(),
            'PATH_INFO'         => $request->getUri(),
            'PATH_TRANSLATED'   => $script_path,
            'SCRIPT_NAME'       => $urlInfo['path'],
            'REMOTE_ADDR'       => $request->getRemoteAddress(),
            'CONTENT_TYPE'      => $request->getHeader('content-type'),
            'CONTENT_LENGTH'    => $request->getHeader('content-length'),
            'PATH'              => getenv('PATH')
        );
        
        if (isset($urlInfo['query'])) {
            $environment['QUERY_STRING'] = $urlInfo['query']; 
        }
        
        // Add the HTTP headers to the environment
        $headers = $request->getAllHeaders();
        unset(
            $headers['content-type'], 
            $headers['content-length']
        );
        foreach($headers as $header => $value) {
            $key = 'HTTP_' . strtoupper(strtr($header, '-', '_'));
            $environment[$key] = $value;
        }
        
        // Open the CGI handler process
        $cgi_proc = proc_open(
            $this->_config['handler'],
            array(
                array('pipe', 'r'),
                array('pipe', 'w'),
                array('file', '/tmp/cgi-error.log', 'a')
            ),
            $pipes,
            null,
            $environment
        );
        if (! $cgi_proc) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("Unable to run CGI handler");
        }
        
        // Write body, if any, to the CGI STDIN
        fwrite($pipes[0], $request->getBody());
        fclose($pipes[0]);
        
        // Read the response
        $response = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // Close CGI process
        proc_close($cgi_proc);
        
        // Create the status line
        $statusLine = "HTTP/{$request->getHttpVersion()}";
        if (preg_match('/\Astatus:\s*(\d+ .+)$/', $response, $m)) {
            $statusLine .= " {$m[1]}\r\n";
        } else {
            $statusLine .= " 200 OK\r\n"; // Default, send 200 Ok
        }
        
        return Aspamia_Http_Response::fromString($statusLine . $response);
    }
}