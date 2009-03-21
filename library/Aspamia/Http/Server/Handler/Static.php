<?php

require_once 'Aspamia/Http/Server/Handler/Abstract.php';

class Aspamia_Http_Server_Handler_Static extends Aspamia_Http_Server_Handler_Abstract 
{
    protected $_config = array(
        'document_root'    => null,
        'list_directories' => false,
        'followsymlinks'   => false,
    );
    
    public function hanle(Aspamia_Http_Request $request)
    {
        $oldcwd = getcwd();
        if ($this->_config['document_root']) {
            $document_root = $this->_config['document_root'];
            chdir($document_root);
        }
        
        $file = ltrim($request->getUri(), '/');
        
        if (file_exists($file)) {
            if (is_readable($file)) {
                if (! $this->_config['followsymlinks'] || ! is_link($file)) {
                    if (is_file($file)) {
                        $response = $this->_serveFile($file);
                    } elseif (is_dir($file) && $this->_config['list_directories']) {
                        $response = $this->_serveDirListing($file);
                    } else {
                        // Directory and can't list, or something else - 403
                        $response = new Aspamia_Http_Response(403, array(), "Directory listing not allowed");
                    }
                } else {
                    // symlink and we can't follow - 404
                    $response = new Aspamia_Http_Response(404, array(), "The requested URL does not exist"); 
                }
            } else {
                // not readable - 403
                $response = new Aspamia_Http_Response(403, array(), "You have no permissions to read the requested URL");
            }
        } else {
            // not found - 404
            $response = new Aspamia_Http_Response(404, "The requested URL does not exist");
        }
        
        return $response;
    }
    
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
    }
    
    protected function _getFileType($file)
    {
        return 'text/plain';    
    }
}