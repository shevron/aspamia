<?php

// Add the incubator to the include path
set_include_path(
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'library'
);

require_once 'Aspamia/Http/Server.php';
require_once 'Aspamia/Http/Server/Plugin/Logger.php';

// Create the server object with a single static handler
$server = new Aspamia_Http_Server(array(
    'handler' => 'Aspamia_Http_Server_Handler_Static'
));

// Register the logger plugin
$server->registerPlugin(new Aspamia_Http_Server_Plugin_Logger());

// Run the server!
$server->run();