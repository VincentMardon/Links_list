<?php
namespace WebLinks\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use WebLinks\Domain\Link;
use WebLinks\Domain\User;
use WebLinks\Form\Type\LinkType;
use WebLinks\Form\Type\UserType;

class AdminController
{
	/**
	 * Admin home page controller
	 * @param Application $app Silex application
	 */
	public function indexAction(Application $app)
	{
		$links = $app['dao.link']->findAll();
		$users = $app['dao.user']->findAll();
		
		return $app['twig']->render('admin.html.twig', [
			'links' => $links,
			'users' => $users
		]);
	}
	
	/**
	 * Add link controller
	 * @param Request $request Incoming Request
	 * @param Application $app Silex application
	 */
	public function addLinkAction(Request $request, Application $app)
	{
		$link = new Link();
		$linkForm = $app['form.factory']->create(new LinkType(), $link);
		$linkForm->handleRequest($request);
	
		if ($linkForm->isSubmitted() && $linkForm->isValid())
		{
			// Set the user who adds the link
			$user = $app['security']->getToken()->getUser();
			$link->setAuthor($user);
			
			$app['dao.link']->save($link);
			$app['session']->getFlashBag()->add('success', 'Your link was succesfully added.');
		}
	
		return $app['twig']->render('link_form.html.twig', [
			'title' => 'New link',
			'linkForm' => $linkForm->createView()
		]);
	}
	
	/**
	 * Edit link controller
	 * @param integer $id Link id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function editLinkAction($id, Request $request, Application $app)
	{
		$link = $app['dao.link']->find($id);
		$linkForm = $app['form.factory']->create(new linkType(), $link);
		$linkForm->handleRequest($request);
	
		if ($linkForm->isSubmitted() && $linkForm->isValid())
		{
			$app['dao.link']->save($link);
			$app['session']->getFlashBag()->add('success', 'The link was succesfully updated');
		}
	
		return $app['twig']->render('link_form.html.twig', [
			'title' => 'Link edit',
			'linkForm' => $linkForm->createView()
		]);
	}
	
	/**
	 * Delete link controller
	 * @param integer $id Link id
	 * @param Application $app Silex application
	 */
	public function deleteLinkAction($id, Application $app)
	{
		$app['dao.link']->delete($id);
		$app['session']->getFlashBag()->add('success', 'The link was successfully removed.');
	
		return $app->redirect('/admin');
	}
	
	/**
	 * Add user controller
	 * @param Request $request Incoming Request
	 * @param Application $app Silex application
	 */
	public function addUserAction(Request $request, Application $app)
	{
		$user = new User();
		$userForm = $app['form.factory']->create(new UserType(), $user);
		$userForm->handleRequest($request);
	
		if ($userForm->isSubmitted() && $userForm->isValid())
		{
			// Generate a random salt value
			$salt = substr(md5(time()), 0, 23);
			$user->setSalt($salt);
		
			$plainPassword = $user->getPassword();
		
			// Find the default encoder
			$encoder = $app['security.encoder.digest'];
		
			// Compute the encoded password
			$password = $encoder->encodePassword($plainPassword, $user->getSalt());
			$user->setPassword($password);
		
			$app['dao.user']->save($user);
			$app['session']->getFlashBag()->add('success', 'The user was successfully added.');
		}
	
		return $app['twig']->render('user_form.html.twig', [
			'title' => 'New user',
			'userForm' => $userForm->createView()
		]);
	}
	
	/**
	 * Edit user controller
	 * @param integer $id User id
	 * @param Request $request Incoming Request
	 * @param Application $app Silex application
	 */
	public function editUserAction($id, Request $request, Application $app)
	{
		$user = $app['dao.user']->find($id);
		$userForm = $app['form.factory']->create(new UserType(), $user);
		$userForm->handleRequest($request);
	
		if ($userForm->isSubmitted() && $userForm->isValid())
		{
			$plainPassword = $user->getPassword();
		
			// Find the encoder for the user
			$encoder = $app['security.encoder_factory']->getEncoder($user);
		
			// Compute the encoded password
			$password = $encoder->encodePassword($plainPassword, $user->getSalt());
			$user->setPassword($password);
		
			$app['dao.user']->save($user);
			$app['session']->getFlashBag()->add('success', 'The user was successfully updated.');
		}
	
		return $app['twig']->render('user_form.html.twig', [
			'title' => 'Edit user',
			'userForm' => $userForm->createView()
		]);
	}
	
	/**
	 * Delete user controller
	 * @param integer $id User id
	 * @param Application $app Silex application
	 */
	public function deleteUserAction($id, Application $app)
	{
		// Delete all associated links
		$app['dao.link']->deleteAllByUser($id);
	
		// Delete the user
		$app['dao.user']->delete($id);
		$app['session']->getFlashBag()->add('success', 'The user was successfully removed.');
	
		return $app->redirect('/admin');
	}
}
