<?php
namespace WebLinks\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\request;
use WebLinks\Domain\Link;
use WebLinks\Form\Type\LinkType;

class HomeController
{
	/**
	 * Home page controller
	 * @param Application $app Silex application
	 */
	public function indexAction(Application $app)
	{
		$links = $app['dao.link']->findAll();
		
		return $app['twig']->render('index.html.twig', ['links' => $links]);
	}
	
	/**
	 * User login controller
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function loginAction(Request $request, Application $app)
	{
		return $app['twig']->render('login.html.twig', [
			'error'			=> $app['security.last_error']($request),
			'last_username' => $app['session']->get('_security.last_username')
		]);
	}
}
