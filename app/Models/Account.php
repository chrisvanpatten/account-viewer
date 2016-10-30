<?php

namespace App\Models;

use App\Helpers\Config;

class Account
{
	public function __construct($data)
	{
		$this->data = $data;
	}

	public static function where($key, $value)
	{
		$accounts = Account::all();

		foreach ( $accounts as $account ) {
			if ( $account->data[$key] === $value )
				return $account;
		}

		return [];
	}

	public static function fetch($credentials)
	{
		$config = Config::get();

		// Set the path to the accounts cache file
		$cache = $config->paths->cache . '/accounts-' . md5($credentials->email) . '.json';

		// If the cache is less than one hour old, reuse it
		if ( file_exists($cache) && filemtime($cache) > ( time() - 60 * 60 ) ) {
			$accounts = file_get_contents($cache);
		} else {
			// Otherwise, the cache is stale and should be updated
			$accounts = shell_exec($config->paths->mintapi . ' --accounts ' . $credentials->email . ' "' . $credentials->password . '" --session=' . $credentials->session);
			file_put_contents($cache, $accounts, LOCK_EX);
		}

		// Decode the accounts
		$raw_accounts = json_decode($accounts, true);

		// Get our own Account objects
		foreach ( $raw_accounts as $account ) {
			$accounts[] = new self($account);
		}

		// Return the array
		return $accounts;
	}

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
			if ($account['userName'] === 'ignore')
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
				if ( isset($account[$key]) )
					$corpus[] = $account[$key];
			}

			// Remove duplicate words, punctuation, and lowercase it all
			$corpus = implode(' ', $corpus);
			$corpus = explode(' ', $corpus);
			$corpus = array_unique($corpus);
			$corpus = implode(' ', $corpus);
			$corpus = trim(preg_replace("/[^0-9a-z]+/i", " ", $corpus));
			$corpus = strtolower($corpus);

			// Add to the index
			$index[$account['id']] = $corpus;
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
