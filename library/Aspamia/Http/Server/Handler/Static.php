<?php

/**
 * Aspamia HTTP Server Library for PHP
 * 
 * @author    Shahar Evron
 * @license   New BSD License, <url>
 */

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

/**
 * Static file handler 
 * 
 * This handler allows serving of existing static files located under a 
 * designated document root directory, and, if enabled, of directory listingss 
 * as HTML.
 * 
 * Note: do not use this handler to serve dynamic content - you will end up 
 * sending the source code of your files and not the output they generate.   
 *
 */
class Aspamia_Http_Server_Handler_Static extends Aspamia_Http_Server_Handler_Abstract 
{
    protected $_config = array(
        'document_root'    => '',
        'list_directories' => false,
        'follow_symlinks'  => false,
        'directory_index'  => array('index.html'),
    
        'mime_types'       => array( // Array of MIME type definitions
            'html' => 'text/html',
            'htm'  => 'text/html',
            'txt'  => 'text/plain',
            'xml'  => 'text/xml',
			'css'  => 'text/css',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'png'  => 'image/png'
        )
    );
    
    /**
     * Overload setConfig to make sure some config options are OK
     *
     * @param Zend_Config | array $config
     */
    public function setConfig($config)
    {
        parent::setConfig($config);
        
        if (! is_array($this->_config['directory_index'])) {
            if (strlen($this->_config['directory_index'])) {
                $this->_config['directory_index'] = array(
                    $this->_config['directory_index']
                );    
            } else {
                $this->_config['directory_index'] = array();
            }
        }
        
        if (! is_array($this->_config['mime_types'])) {
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("MIME type configuration must be an array"); 
        }
    }
    
    /**
     * Handle the request
     *
     * @param  Aspamia_Http_Request $request
     * @return Aspamia_Http_Response
     */
    public function handle(Aspamia_Http_Request $request)
    {
        if ($this->_config['document_root']) {
            $document_root = $this->_config['document_root'];
        } else {
            $document_root = getcwd();
        }
        
        $file = rtrim($document_root, '/') . '/' . 
                trim($request->getUri(), '/');
        
        if (! file_exists($file)) {
            // not found - 404
            return self::_errorResponse(404, "The requested URL does not exist");
        }
        
        if (! is_readable($file)) {
            // not readable - 403
            return self::_errorResponse(403, "You have no permissions to read the requested URL");
        }
        
        if (! $this->_config['follow_symlinks'] && is_link($file)) {
            // symlink and we can't follow - 404
            return self::_errorResponse(404, "The requested URL does not exist");
        }
            
        if (is_dir($file)) {
            // Directory - look for index file
            foreach ($this->_config['directory_index'] as $index) {
                $index = $file . '/' . $index;
                if (file_exists($index) && is_file($index)) {
                    return $this->_serveFile($index);
                }
            }
            
            // No index found - can we do directory listing?
            if ($this->_config['list_directories']) {
                return $this->_serveDirListing($file);
            } else {
                // Directory and can't list
                return self::_errorResponse(403, "Directory listing not allowed");
            }
        }
        
        if (is_file($file)) {
            return $this->_serveFile($file);
            
        } else {
            // So what the hell it is?
            require_once 'Aspamia/Http/Server/Handler/Exception.php';
            throw new Aspamia_Http_Server_Handler_Exception("No idea how to serve '$file'"); 
        }
    }
    
    /**
     * Serve a single file
     *
     * @param  string $file
     * @return Aspamia_Http_Response
     */
    protected function _serveFile($file)
    {
        $size = filesize($file);
        $type = $this->_getFileType($file);
        
        $response = new Aspamia_Http_Response(
            200, 
            array(
            	'Content-type' => $type,
                'Content-length' => $size
            ),
            file_get_contents($file)
        );
        
        return $response;
    }
    
    /**
     * Send a directory listing
     *
     * @param  string $dir
     * @return Aspamia_Http_Response
     */
    protected function _serveDirListing($dir)
    {
        return self::_errorResponse(501, "Directory listing is currently not implemented");
    }
    
    /**
     * Get the file MIME type
     *
     * TODO: Add MIME-type auto detection support using mime-magic or fileinfo
     * 
     * @param  string $file
     * @return string
     */
    protected function _getFileType($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext && isset($this->_config['mime_types'][$ext])) {
            return $this->_config['mime_types'][$ext];
        } else {
            return 'text/plain';
        }
    }
}