<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as Guzzle;

class IndexController extends BaseController
{

	/**
	 *
	 */
	public function get( $request, $response, $args )
	{
		$client = new Guzzle();

		$remote = $client->get('https://alexa.chrisvanpatten.com/alexa-bookkeeper/alexa-bookkeeper.php');
		$accounts = json_decode($remote->getBody(), true);

		$vars = [
			'accounts' => $accounts,
		];

		return $this->view->render($response, 'pages/index.html.twig', $vars);
	}

}
