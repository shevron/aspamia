<?php

// Add the incubator to the include path
set_include_path(
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'library'
);

require_once 'Aspamia/Http/Server.php';
require_once 'Aspamia/Http/Server/Plugin/Logger.php';
require_once 'Aspamia/Http/Server/Handler/Static.php';

// Create the server object 
$server = new Aspamia_Http_Server();

// Create the handler object - a single static handler for now
$handler = new Aspamia_Http_Server_Handler_Static(array(
    'document_root' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'static'
));
$server->setHandler($handler);

// Register the logger plugin
$server->registerPlugin(new Aspamia_Http_Server_Plugin_Logger());

// Run the server!
echo "Running Aspamia, use Ctrl+C to abort...\n";
$server->run();
