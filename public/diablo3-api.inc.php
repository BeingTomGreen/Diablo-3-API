<?php

class D3 {
	// Battle Tag
	private $battleTag;

	// Default URL information
	private $protocol = 'http://';
	private $server = 'eu';
	private $host = '.battle.net/';
	private $apiSlug = 'api/d3/';
	private $locale = 'en_GB';

	// Variables to hold the various built API URLs
	private $careerURL; // <host> "/api/d3/profile/" <battletag-name> "-" <battletag-code> "/"
	private $heroURL; // <host> "/api/d3/profile/" <battletag-name> "-" <battletag-code> "/hero/" <hero-id>
	private $itemURL; // <host> "/api/d3/data/item/" <item-data>
	private $followerURL; // <host> "/api/d3/data/follower/" < follower-type>
	private $artisanURL; // <host> "/api/d3/data/artisan/" < artisan-type>
	private $paperDollURL; // <host> "/static/images/profile/hero/paperdoll/" < class-type> "-" < gender-type> ".jpg"

	// These hold all of the possible Protocols, Servers & Locals
	private $possibleProtocols = ['http://', 'https://'];
	private $possibleServers = ['us', 'eu', 'tw', 'kr', 'cn'];
	private $possibleLocale = ['en_US', 'en_GB', 'es_MX', 'es_ES', 'it_IT', 'pt_PT', 'pt_BR', 'fr_FR', 'ru_RU', 'pl_PL', 'de_DE', 'ko_KR', 'zh_TW', 'zh_CN'];

	// These hold the possibilities for various inputs to be validated against
	private $possibleFollowers = ['enchantress', 'templar', 'scoundrel'];
	private $possibleArtisans = ['blacksmith', 'jeweler'];
	private $possibleClasses = ['barbarian', 'witch-doctor', 'demon-hunter', 'monk', 'wizard'];
	private $possibleGenders = ['male', 'female'];

	// Regular Expression
	private $battleTagPattern = '/^[\p{L}\p{Mn}][\p{L}\p{Mn}0-9]{2,11}-[0-9]{4,5}+$/u'; // - https://github.com/XjSv/Diablo-3-API-PHP/issues/4#issuecomment-15982672
	private $heroIDPattern = '/^[\d]+$/';
	private $itemIDPattern = '/^[A-Za-z0-9]+$/';

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
		// Lower case any arguments
		$args = array_map('strtolower', $args);

		// Check we have all of the CURL functions we need
		if ($this->checkCURL() == false)
		{
			// We are missing some functions, lets make a note of this then exit
			error_log('Missing some CURL functions.');
			exit('Sorry, missing some CURL functions - please contact your system administrator.');
		}

		// Have we been passed a valid Protocol
		if (isset($args['protocol']) and in_array($args['protocol'], $this->possibleProtocols))
		{
			$this->protocol = $args['protocol'];
		}

		// Have we been passed a valid Server
		if (isset($args['server']) and in_array($args['server'], $this->possibleServers))
		{
			$this->server = $args['server'];
		}

		// Have we been passed a valid Locale
		if (isset($args['locale']) and in_array($args['locale'], $this->possibleLocale))
		{
			$this->locale = $args['locale'];
		}

		// Finally lets build the various API URLs
		$this->buildAPIURLs();
	}

	/**
		* getCareer
		*
		* Returns the Career data
		*
		* @param string $battleTag - the users BattleTag
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getCareer($battleTag)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$battleTag = str_replace('#', '-', $battleTag);

		// Validate that we have a valid BattleTag
		if ($this->validBattleTag($battleTag) == true)
		{
			// Prepare the URL
			$url = sprintf($this->careerURL, $battleTag);

			// Grab the Career data
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
		* getHero
		*
		* Returns the Hero data
		*
		* @param string $battleTag - the users BattleTag
		* @param string $herID - ther Hero ID
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getHero($battleTag, $heroID)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$battleTag = str_replace('#', '-', $battleTag);

		// Validate that we have a valid BattleTag
		if ($this->validBattleTag($battleTag) == true and $this->validHeroID($heroID) == true)
		{
			// Prepare the URL
			$url = sprintf($this->heroURL, $battleTag, $heroID);

			// Grab the Career data
			return $this->makeCURLCall($url);
		}
		// BattleTag error lets make a note of this then return false
		else
		{
			error_log('BattleTag or HeroID provided not valid. (BattleTag: '. $battleTag .'HeroID: '. $heroID .')');
			return false;
		}
	}

	/**
		* getItem
		*
		* Returns the Item data
		*
		* @param string $itemID - the itemID
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getItem($itemID)
	{
		// Validate that we have a valid ItemID
		if ($this->validItemID($itemID) == true)
		{
			// Prepare the URL
			$url = sprintf($this->itemURL, strtolower($itemID));

			// Grab the Career data
			return $this->makeCURLCall($url);
		}
		// ItemID error lets make a note of this then return false
		else
		{
			error_log('ItemID not valid. (ItemID: '. $itemID .')');
			return false;
		}
	}

	/**
		* getFollower
		*
		* Returns the Follower data
		*
		* @param string $followerType - the Follower type
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getFollower($followerType)
	{
		// Force the Follower type to lower case
		$followerType = strtolower($followerType);

		// Validate that we have a valid Follower type
		if (in_array($followerType, $this->possibleFollowers))
		{
			// Prepare the URL
			$url = sprintf($this->followerURL, $followerType);

			// Grab the Follower data
			return $this->makeCURLCall($url);
		}
		// Follower type error lets make a note of this then return false
		else
		{
			error_log('Follower Type not valid. (Follower Type: '. $followerType .')');
			return false;
		}
	}

	/**
		* getArtisan
		*
		* Returns the Artisan data
		*
		* @param string $artisanType - the Artisan type
		*
		* @return array/bool - data if we have it, otherwise false
		*
		*/
	public function getArtisan($artisanType)
	{
		// Force the artisanType to lower case
		$artisanType = strtolower($artisanType);

		// Validate that we have a valid Artisan type
		if (in_array($artisanType, $this->possibleArtisans))
		{
			// Prepare the URL
			$url = sprintf($this->artisanURL, $artisanType);

			// Grab the Artisan data
			return $this->makeCURLCall($url);
		}
		// Artisan type error lets make a note of this then return false
		else
		{
			error_log('Artisan Type not valid. (Artisan Type: '. $artisanType .')');
			return false;
		}
	}

	/**
		* getPaperDoll
		*
		* Returns a link to the Paper Doll image
		*
		* @param string $class - the class
		* @param string $gender - the gender
		*
		* @return string/bool - data if we have it, otherwise false
		*
		*/
	public function getPaperDoll($classType, $genderType)
	{
		// Validate that we have a valid Artisan type
		if (in_array(strtolower($classType), $this->possibleClasses) and in_array(strtolower($genderType), $this->possibleGenders))
		{
			// Prepare the URL
			$url = sprintf($this->paperDollURL, strtolower($classType), strtolower($genderType));

			// Return the URL
			return $url;
		}
		// Artisan type error lets make a note of this then return false
		else
		{
			error_log('Class or Gender Type not valid. (Class: '. $classType .' Gender: '. $genderType .')');
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
		* buildAPIURLs
		*
		* Builds the various API URLs based on provided information
		*
		*/
	private function buildAPIURLs()
	{
		// Lets build the main part of the URL to save us repeating ourselves
		$url = $this->protocol . $this->server . $this->host;

		// Now lets build the API URLs
		$this->careerURL = $url . $this->apiSlug .'profile/%s/?locale='. $this->locale;
		$this->heroURL = $url . $this->apiSlug .'profile/%s/hero/%d?locale='. $this->locale;
		$this->itemURL = $url . $this->apiSlug .'data/item/%s?locale='. $this->locale;
		$this->followerURL = $url . $this->apiSlug .'data/follower/%s?locale='. $this->locale;
		$this->artisanURL = $url . $this->apiSlug .'data/artisan/%s?locale='. $this->locale;
		$this->paperDollURL = $url .'d3/static/images/profile/hero/paperdoll/%s-%s.jpg';
	}

	/**
		* checkCURL
		*
		* Checks that we have all of the required CURL functions
		*
		* @return bool - do we have all of the CURL functions?
		*
		*/
		public function checkCURL()
		{
			return function_exists("curl_init") and function_exists("curl_setopt") and function_exists("curl_exec") and function_exists("curl_close") ? true : false;
		}

	/**
		* validBattleTag
		*
		* Checks that a supplied BattleTag is valid - according to https://us.battle.net/support/en/article/BattleTagNamingPolicy
		*
		* @param string $battleTag - The users BattleTag
		*
		* @return bool - is the BattleTag valid or not?
		*
		*/
	public function validBattleTag ($battleTag)
	{
		return preg_match($this->battleTagPattern, $battleTag) ? true : false;
	}

	/**
		* validHeroID
		*
		* Checks that a supplied HeroID is valid
		*
		* @param string $heroID - The users Hero ID
		*
		* @return bool - is the HeroID valid or not?
		*
		*/
	public function validHeroID ($heroID)
	{
		return preg_match($this->heroIDPattern, $heroID) ? true : false;
	}

	/**
		* validItemID
		*
		* Checks that a supplied Hero ID is valid
		*
		* @param string $itemID - The itemID
		*
		* @return bool - is the itemID valid or not?
		*
		*/
	public function validItemID ($itemID)
	{
		return preg_match($this->itemIDPattern, $itemID) ? true : false;
	}
}

?>