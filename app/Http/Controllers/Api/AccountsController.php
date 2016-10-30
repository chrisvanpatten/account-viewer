<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\BaseController;

class AccountsController extends BaseController
{
	public function get( $request, $response, $args )
	{
		$accounts = Account::all();

		foreach ( $accounts as $account ) {
			$data[] = $account->data;
		}

		return $response->withJson($data);
	}
}
