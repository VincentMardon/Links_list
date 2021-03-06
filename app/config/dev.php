<?php

// Doctrine (db)
$app['db.options'] = [
	'driver'   => 'pdo_mysql',
	'charset'  => 'utf8',
	'host'	   => '127.0.0.1',   // Mandatory for PHPUnit testing
	'port'	   => '3306',
	'dbname'   => 'weblinks',
	'user'	   => 'weblinks_user',
	'password' => 'secret'
];

// enable the debug mode
$app['debug'] = true;

// define log level
$app['monolog.level'] = 'INFO';
