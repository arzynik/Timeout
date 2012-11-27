<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors',true);
set_time_limit(10);

require 'Timeout.php';
require 'ExampleRequire.php';

// our user object
$user = new User(array(
	'Name' => 'Devin',
	'Location' => 'LA',
	'Food' => array(
		'Sushi',
		'Beer'
	)
));

// async
$o = Timeout::run(function() use($user) {
	file_put_contents('example-options.txt', print_r($user->data(), 1));
}, 1, array(
	'require' => function() {
		// you will need to include class definitions first, so when decoding them they will be completed
		require 'exampleRequire.php';
	}
));

// sync
$o = Timeout::run(function() use($user) {
	$data = $user->data();
	print_r($data['Name']);

}, 1, array(
	'async' => false,
	'require' => function() {
		// you will need to include class definitions first, so when decoding them they will be completed
		require 'exampleRequire.php';
	}
));

// example output
print_r($o);
