<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app['twig'] = $app->share($app->extend('twig', function(Twig_Environment $twig, $app) {
	$twig->addExtension(new Twig_Extensions_Extension_Text());
	return $twig;
}));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), [
	'security.firewalls' => [
		'secured' => [
			'pattern' => '^/',
			'anonymous' => true,
			'logout' => true,
			'form' => [
				'login_path' => '/login',
				'check_path' => '/login_check'
			],
			'users' => $app->share(function() use ($app) {
				return new WebLinks\DAO\UserDAO($app['db']);
			})
		]
	],
	'security.role_hierarchy' => [
		'ROLE_ADMIN' => ['ROLE_USER']
	],
	'security.access_rules' => [
		['^/admin', 'ROLE_ADMIN']
	]
]);
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), [
	'monolog.logfile' => __DIR__ . '/../var/logs/weblinks.log',
	'monolog.name' => 'WebLinks',
	'monolog.level' => $app['monolog.level']
]);
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
if (isset($app['debug']) && $app['debug'])
{
	$app->register(new Silex\Provider\WebProfilerServiceProvider(), [
		'profiler.cache_dir' => __DIR__ . '/../var/cache/profiler'
	]);
}
$app->register(new Silex\Provider\HttpFragmentServiceProvider());

// Register services
$app['dao.user'] = $app->share(function($app) {
	return new WebLinks\DAO\UserDAO($app['db']);
});

$app['dao.link'] = $app->share(function ($app) {
    $linkDAO = new WebLinks\DAO\LinkDAO($app['db']);
	$linkDAO->setUserDAO($app['dao.user']);
	
	return $linkDAO;
});

// Register error handler
$app->error(function(\Exception $e, $code) use ($app) {
	switch ($code) {
		case 403:
			$message = 'Acces denied.';
			break;
		case 404:
			$message = 'The requested resource could not be found.';
			break;
		default:
			$message = 'Something went wrong.';
	}
	
	return $app['twig']->render('error.html.twig', ['message' => $message]);
});
