<?php

require 'Timeout.php';

Timeout::run(function() {
	file_put_contents('example-basic.txt', 'hello timeout!');
}, 1000);
