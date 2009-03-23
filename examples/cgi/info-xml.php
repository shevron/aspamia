<?php

$superGlobs = array(
	'GET'    => $_GET, 
	'POST'   => $_POST, 
	'COOKIE' => $_COOKIE, 
	'SERVER' => $_SERVER, 
	'ENV'    => $_ENV
);

header("Content-type: text/xml");

$xml = new xmlWriter();
$xml->openURI('php://output');
$xml->setIndent(true);
$xml->setIndentString("  ");

$xml->startDocument();
$xml->startElement('superglobals');

foreach ($superGlobs as $globname => $glob) {
	$xml->startElement('superglobal');
	$xml->writeAttribute('name', $globname);
	
	foreach($glob as $k => $v) {
		print_glob_val($xml, $k, $v);
	}

	$xml->endElement();
}

$xml->endElement();
$xml->endDocument();
$xml->flush();

function print_glob_val(XMLWriter $xml, $key, $value)
{
	$xml->startElement('value');
	$xml->writeAttribute('name', $key);

	if (is_array($value)) {
		foreach($value as $subkey => $subval) {
			print_glob_val($xml, $subkey, $subval);
		}
	} else {
		$xml->text($value);
	}

	$xml->endElement();
}

