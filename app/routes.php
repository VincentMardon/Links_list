<?php

// Home page
$app->get('/', "WebLinks\Controller\HomeController::indexAction");

// Login form
$app->get('/login', "WebLinks\Controller\HomeController::loginAction")
	->bind('login'); // named route so that path ('login') works in Twig templates

// Admin home page
$app->get('/admin', "WebLinks\Controller\AdminController::indexAction");

// Add a new link
$app->match('/link/submit', "WebLinks\Controller\AdminController::addLinkAction");

// Edit an existing link
$app->match('admin/link/{id}/edit', "WebLinks\Controller\AdminController::editLinkAction");

// Remove a link
$app->get('/admin/link/{id}/delete', "WebLinks\Controller\AdminController::deleteLinkAction");

// Add a user
$app->match('/admin/user/add', "WebLinks\Controller\AdminController::addUserAction");

// Edit an existing user
$app->match('/admin/user/{id}/edit', "WebLinks\Controller\AdminController::editUserAction");

// Remove a user
$app->get('/admin/user/{id}/delete', "WebLinks\Controller\AdminController::deleteUserAction");

// API : get all links
$app->get('/api/links', "WebLinks\Controller\ApiController::getLinksAction");

// API : get a link
$app->get('/api/link/{id}', "WebLinks\Controller\ApiController::getLinkAction");
