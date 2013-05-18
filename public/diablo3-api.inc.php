<?php

class D3 {
	// Battle Tag
	private $battleTag;

	// Default URL information
	private $protocol = 'http://';
	private $server = 'eu';
	private $host = '.battle.net/api/d3/';
	private $locale = 'en_GB';

	// These hold all of the possible Protocols, Servers & Locals
	private $possibleProtocols = ['http://', 'https://'];
	private $possibleServers = ['us', 'eu', 'tw', 'kr', 'cn'];
	private $possibleLocale = ['en_US', 'en_GB', 'es_MX', 'es_ES', 'it_IT', 'pt_PT', 'pt_BR', 'fr_FR', 'ru_RU', 'pl_PL', 'de_DE', 'ko_KR', 'zh_TW', 'zh_CN'];

	// Regular Expression to match Valid BattleTags
	// TODO - Refactor - this is taken from a random GitHub Repo (https://github.com/XjSv/Diablo-3-API-PHP/blob/master/diablo3.api.class.php)!
	private $battleTagPattern = '/^[\p{L}\p{Mn}][\p{L}\p{Mn}0-9]{2,11}-[0-9]{4,5}+$/u';

	// These are extra CURL options which users can specify or change
	private $extraCURLOptions = [
		CURLOPT_CONNECTTIMEOUT => 5
	];

	/**
		* __construct
		*
		* @param array $args - An array of optional settings for Protocol, Server and Locale
		*
		*/
	function __construct ($args = null)
	{
		// Check we have all of the CURL functions we need
		if ($this->checkCURL() == false)
		{
			// We are missing some functions, lets make a note of this then exit
			error_log('Missing some CURL functions.');
			exit('Sorry, missing some CURL functions - please contact your system administrator.');
		}

		// Have we been passed a valid Protocol
		if (isset($args['protocol']) and $args['protocol'] != '' and in_array($args['protocol'], $this->possibleProtocols))
		{
			$this->protocol = $args['protocol'];
		}

		// Have we been passed a valid Server
		if (isset($args['server']) and $args['server'] != '' and in_array($args['server'], $this->possibleServers))
		{
			$this->server = $args['server'];
		}

		// Have we been passed a valid Locale
		if (isset($args['locale']) and $args['locale'] != '' and in_array($args['locale'], $this->possibleLocale))
		{
			$this->locale = $args['locale'];
		}
	}

	/**
		* getCareer
		*
		* Returns the Career data
		*
		* @param string $battleTag - The users Battle Tag (https://us.battle.net/support/en/article/BattleTagNamingPolicy)
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getCareer($battleTag)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$this->battleTag = str_replace('#', '-', $battleTag);

		// Validate that we have a valid Battle Tag
		if ($this->validBattleTag($this->battleTag) == true)
		{
			// Build the API URL
			$url = $this->protocol . $this->server . $this->host .'profile/'. $this->battleTag .'/?locale='.$this->locale;

			return $this->makeCURLCall($url);
		}
		// BattleTag error lets make a note of this then return false
		else
		{
			error_log('BattleTag provided not valid. ('. $battleTag .')');
			return false;
		}
	}

	/**
		* makeCURLCall
		*
		* Makes the specified CURL request - this is the meat of the class!
		*
		* @param string $url - The URL to for the CURL request
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	private function makeCURLCall($url)
	{
		// Initialise CURL
		$handle = curl_init();

		// Set the CURL options we need
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

		// Do we have any extra CURL options?
		if (isset($this->extraCURLOptions) and !empty($this->extraCURLOptions))
		{
			// Set any customisable/extra CURL options
			curl_setopt_array($handle, $this->extraCURLOptions);
		}

		// Grab the data
		$data = curl_exec($handle);

		// Grab the CURL error code and message
		$errorCode = curl_errno($handle);
		$errorMessage = curl_error($handle);

		// Close the connection
		curl_close($handle);

		// Our error code is 0 (0 means OK!)
		if ($errorCode == 0)
		{
			// json decode the $data
			$data = json_decode($data, true);

			// Check we don't have an error code
			if (isset($data['code']) and isset($data['reason']))
			{
				// API error lets make a note of this then return false
				error_log('API error: '. $data['code'] .' - '. $data['reason'] .' ('. $url .')!');
				return false;
			}
			// No errors, lets return the data
			else
			{
				return $data;
			}
		}
		// CURL error lets make a note of this then return false
		else
		{
			error_log('CURL error "'. $errorCode .'" ('. $errorMessage .').');
			return false;
		}
	}

	/**
		* checkCURL
		*
		* Checks that we have all of the required CURL functions
		*
		* @todo Refactor to use ternery operator (smaller code footprint)
		*
		* @return bool - do we have all of the CURL functions?
		*
		*/
		public function checkCURL()
		{
			if(!function_exists("curl_init") or !function_exists("curl_setopt") or !function_exists("curl_exec") or !function_exists("curl_close"))
			{
				return false;
			}
			else
			{
				return true;
			}
		}

	/**
		* validBattleTag
		*
		* Checks that a supplied BattleTag is valid - according to https://us.battle.net/support/en/article/BattleTagNamingPolicy
		*
		* @todo Refactor to use ternery operator (smaller code footprint)
		*
		* @param string $battleTag - The users Battle Tag
		*
		* @return bool - is the BattleTag valid or not?
		*
		*/
	private function validBattleTag ($battleTag)
	{
		// Now check it is valid
		if (preg_match($this->battleTagPattern, $battleTag))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>