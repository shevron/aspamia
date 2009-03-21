<?php

// Add the incubator to the include path
set_include_path(
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'library'
);

require_once 'Aspamia/Http/Server.php';
$server = new Aspamia_Http_Server(); 
$server->run();