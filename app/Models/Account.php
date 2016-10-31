<?php

namespace App;

use App\Helpers\Config;

class Account
{
	/**
	 */
	public function __construct($data)
	{
		$this->data = $data;
		$this->id   = $data['id'];
	}

	/**
	 * @return string
	 */
	public function getInstitution()
	{
		return $this->data['fiLoginDisplayName'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data['userName'] ?: $this->data['name'];
	}

	/**
	 * @return float
	 */
	public function getBalance()
	{
		return $this->data['value'];
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->data['accountType'];
	}

	/**
	 * @return Account
	 */
	public static function where($key, $value)
	{
		$accounts = Account::all();

		foreach ( $accounts as $account ) {
			if ( $account->data[$key] === $value )
				return $account;
		}

		throw new Exception('Account not found.');
	}

	/**
	 * @return array
	 */
	public static function fetch($credentials)
	{
		$config = Config::get();

		// Set the path to the accounts cache file
		$cache = $config->paths->cache . '/accounts-' . md5($credentials->email) . '.json';

		// If the cache is less than one hour old, reuse it
		if ( file_exists($cache) && filemtime($cache) > (time() - 60 * 60) ) {
			$raw_accounts = file_get_contents($cache);
		} else {
			// Otherwise, the cache is stale and should be updated
			$command = $config->paths->mintapi . ' --accounts ' . $credentials->email . ' "' . $credentials->password . '" --session=' . $credentials->session;
			$raw_accounts = shell_exec($command);
			file_put_contents($cache, $raw_accounts, LOCK_EX);
		}

		// Decode the accounts
		$raw_accounts = json_decode($raw_accounts, true);

		$accounts = [];

		// Get our own Account objects
		foreach ( $raw_accounts as $account ) {
			// Filter out 'ignore' accounts
			if ( $account['name'] === 'ignore' )
				continue;

			$accounts[] = new self($account);
		}

		// Return the array
		return $accounts;
	}

	/**
	 * @return array
	 */
	public static function all()
	{
		$mint_accounts = Config::get()->accounts;
		$accounts = [];

		foreach ( $mint_accounts as $credentials ) {
			$accounts = array_merge($accounts, Account::fetch($credentials));
		}

		return $accounts;
	}

	/**
	 * Build the search index for our accounts
	 *
	 * @return array
	 */
	public static function index()
	{
		// Loop through accounts
		foreach ( Account::all() as $account ) {
			// Skip "ignore" accounts
			if ($account->data['userName'] === 'ignore')
				continue;

			// Start with a fresh corpus
			$corpus = [];

			// The keys we want to add to our corpus
			$keys = [
				'fiLoginDisplayName',
				'userName',
				'accountName',
				'yodleeName',
				'fiName',
			];

			// Add each value to our corpus for this account
			foreach ( $keys as $key ) {
				if ( isset($account->data[$key]) )
					$corpus[] = $account->data[$key];
			}

			// Remove duplicate words, punctuation, and lowercase it all
			$corpus = implode(' ', $corpus);
			$corpus = explode(' ', $corpus);
			$corpus = array_unique($corpus);
			$corpus = implode(' ', $corpus);
			$corpus = trim(preg_replace("/[^0-9a-z]+/i", " ", $corpus));
			$corpus = strtolower($corpus);

			// Add to the index
			$index[$account->id] = $corpus;
		}

		// Return the full index
		return $index;
	}

	/**
	 * Get the closest matching account ID for the search term
	 *
	 * @param string $search
	 *
	 * @return int
	 */
	public static function search( $search )
	{
		$results = [];

		// Lowercase the search term
		$search = strtolower($search);

		// Build the search index
		$index = Account::index();

		// Get similarity ratings for each item in the index
		foreach( $index as $id => $corpus ) {
			$results[$id] = similar_text($search, $corpus);
		}

		// Sort the results by highest to lowest match
		arsort($results);

		$resultsSorted = [];

		// Rebuild the sorted array of IDs
		foreach ( $results as $id => $similarity ) {
			$resultsSorted[] = $id;
		}

		// Return the first Account in the bunch
		return Account::where('id', $resultsSorted[0]);
	}
}
