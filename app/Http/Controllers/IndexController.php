<?php

namespace App\Http\Controllers;

use App\Account;

class IndexController extends BaseController
{

	/**
	 */
	public function get( $request, $response, $args )
	{
		// Build the vars to pass to Twig
		$vars = [
			'accounts' => Account::all(),
		];

		return $this->view->render($response, 'pages/index.html.twig', $vars);
	}

}
