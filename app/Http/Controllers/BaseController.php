<?php

namespace App\Http\Controllers;

class BaseController
{

	public function __construct( $container )
	{
		$this->container = $container;

		$this->view   = $this->container['view'];
		$this->config = $this->container['config'];
	}

}
