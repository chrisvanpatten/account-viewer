<?php

namespace App\Http\Controllers;

use App\Account;

class AlexaController extends BaseController
{
	public function post( $request, $response, $args )
	{
		$body    = $request->getBody();
		$keyword = $body['request']['intent']['slots']['Account']['value'];

		// Find the account ID for the given keyword
		$account = Account::search($keyword);

		// Format the response
		$response = [
			"response" => [
				"outputSpeech" => [
					"type" => "PlainText",
					"text" => $this->speakableSentence($account),
				],
				"shouldEndSession" => true,
			],
		];

		return $response;
	}

	/**
	 * Create a speakable balance string
	 *
	 * @param float|int $balance
	 *
	 * @return string
	 */
	private function speakableBalance( $balance )
	{
		$balance = number_format($balance, 2);
		$balance = explode('.', strval($balance));

		$dollars = explode(',', $balance[0]);

		if ( isset($dollars[1]) )
			$text = $dollars[0] . ' thousand ' . intval($dollars[1]) . ' dollars';
		else
			$text = $balance[0] . ' dollars';

		if ( isset($balance[1]) && intval($balance[1]) !== 0 )
			$text .= ' and ' . intval($balance[1]) . ' cents';

		return $text;
	}

	/**
	 * Generate a speakable name for the account. Defaults
	 * to your Mint-set name, if it's set. Otherwise it's
	 * constructed from some of Mint's internal sources.
	 *
	 * @param array $account
	 *
	 * @return string
	 */
	private function speakableName( $account )
	{
		return $account['userName'] ? $account['userName'] : $account['fiLoginDisplayName'] . ' ' . $account['yodleeName'];
	}

	/**
	 * Generate a speakable sentence for the account.
	 * Uses speakableName() and speakableBalance(), plus
	 * conditionals based on the account type.
	 *
	 * @param array $account
	 *
	 * @return string
	 */
	private function speakableSentence( $account )
	{
		$name    = $this->speakableName($account);
		$balance = $this->speakableBalance($account['currentBalance']);

		if ( $account['accountType'] === 'credit' || $account['accountType'] === 'loan' ) {
			if ( $account['currentBalance'] == 0 ) {
				$content = "You owe nothing on your {$name} account.";
			} else {
				$content = "You owe {$balance} on your {$name} account.";
			}
		} else if ( $account['accountType'] === 'bank' ) {
			if ( $account['currentBalance'] == 0 ) {
				$content = "Your {$name} account is empty.";
			} else {
				$content = "Your {$name} account balance is {$balance}.";
			}
		}

		return $content;
	}
}
