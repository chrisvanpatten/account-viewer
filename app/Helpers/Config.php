<?php

namespace App\Helpers;

class Config
{
	public static function get()
	{
		$config = file_get_contents(dirname(__FILE__) . '/../../config.json');

		return json_decode($config);
	}
}
