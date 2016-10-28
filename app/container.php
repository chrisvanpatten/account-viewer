<?php

use Interop\Container\ContainerInterface;
use Slim\Container;
use Slim\Views\Twig;

$container = $app->getContainer();

// Twig
$container['view'] = function ( $c ) {

	$settings = $c->get('settings');

	$view = new Twig(
		$settings['view']['template_path'],
		$settings['view']['twig']
	);

	$view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));

	return $view;

};
