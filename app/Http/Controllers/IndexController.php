<?php

namespace App\Http\Controllers;

class IndexController extends BaseController
{

	/**
	 *
	 */
	public function get( $request, $response, $args )
	{
		return $this->view->render($response, 'pages/index.html.twig');
	}

}
