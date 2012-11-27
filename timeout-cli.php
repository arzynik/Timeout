#!/usr/local/bin/php
<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(10);

// arguments
$a = (object)getopt('s::c::r::f::');

// sleep
if ($a->s) {
	sleep($a->s / 1000); // ms
}

// include our libraries AFTER the nap, so we keep mem clean
require 'Timeout.php';
	
// require function. useful for requires
if ($a->r) {
	$r = unserialize(base64_decode($a->r));
	$r->__invoke();
}

// or execute suberclosure (recommended)
if ($a->c) {
	$c = unserialize(base64_decode($a->c));
	$c->__invoke();
}

// or you can execute a file
if ($a->f) {
	require_once('./'.$a->f);
}