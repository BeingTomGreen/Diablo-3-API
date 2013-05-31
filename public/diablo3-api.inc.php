<?php

class D3 {
	// Default API URL information
	private $protocol = 'http://';
	private $server = 'eu';
	private $host = '.battle.net/';
	private $apiSlug = 'api/d3/';
	private $locale = 'en_GB';

	// These hold the various API URLs built by buildAPIURLs()
	private $careerURL; // <host> "/api/d3/profile/" <battletag-name> "-" <battletag-code> "/"
	private $heroURL; // <host> "/api/d3/profile/" <battletag-name> "-" <battletag-code> "/hero/" <hero-id>
	private $itemURL; // <host> "/api/d3/data/item/" <item-data>
	private $followerURL; // <host> "/api/d3/data/follower/" < follower-type>
	private $artisanURL; // <host> "/api/d3/data/artisan/" < artisan-type>
	private $paperDollURL; // <host> "/static/images/profile/hero/paperdoll/" < class-type> "-" < gender-type> ".jpg"

	// These hold the possible protocols, servers & locals
	private $possibleProtocols = ['http://', 'https://'];
	private $possibleServers = ['us', 'eu', 'tw', 'kr', 'cn'];
	private $possibleLocale = ['en_US', 'en_GB', 'es_MX', 'es_ES', 'it_IT', 'pt_PT', 'pt_BR', 'fr_FR', 'ru_RU', 'pl_PL', 'de_DE', 'ko_KR', 'zh_TW', 'zh_CN'];

	// These hold the possibilities for various inputs to be checked against
	private $possibleFollowers = ['enchantress', 'templar', 'scoundrel'];
	private $possibleArtisans = ['blacksmith', 'jeweler'];
	private $possibleClasses = ['barbarian', 'witch-doctor', 'demon-hunter', 'monk', 'wizard'];
	private $possibleGenders = ['male', 'female'];

	// These hold the Regular Expressions for various inputs to be checked against
	private $battleTagPattern = '/^[\p{L}\p{Mn}][\p{L}\p{Mn}0-9]{2,11}-[0-9]{4,5}+$/u'; // - https://github.com/XjSv/Diablo-3-API-PHP/issues/4#issuecomment-15982672
	private $heroIDPattern = '/^[\d]+$/';
	private $itemIDPattern = '/^[A-Za-z0-9]+$/';

	// This allows users to add additional CURL options
	public $extraCURLOptions;

	/**
		* __construct
		*
		* @param array $args - Optional array of settings (protocol, server and locale)
		*
		*/
	function __construct ($args = null)
	{
		// Make all options lower case
		$args = array_map('strtolower', $args);

		// Check if we have been passed a valid protocol and if so use it
		if (isset($args['protocol']) and in_array($args['protocol'], $this->possibleProtocols))
		{
			$this->protocol = $args['protocol'];
		}

		// Check if we have been passed a valid server and if so use it
		if (isset($args['server']) and in_array($args['server'], $this->possibleServers))
		{
			$this->server = $args['server'];
		}

		// Check if we have been passed a valid locale and if so use it
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
		* @param string $battleTag - the users Battle Tag
		*
		* @return json/false - data if we have it, otherwise false
		*
		*/
	public function getCareer($battleTag)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$battleTag = str_replace('#', '-', $battleTag);

		// Check that the Battle Tag is valid
		if ($this->validBattleTag($battleTag) == true)
		{
			// Build the URL
			$url = sprintf($this->careerURL, $battleTag);

			// Grab the data
			return $this->makeCURLCall($url);
		}
		// Battle Tag not valid - make a note of it and return false
		else
		{
			error_log('Invalid BattleTag ('. $battleTag .')');
			return false;
		}
	}

	/**
		* getHero
		*
		* Returns the Hero data
		*
		* @param string $battleTag - the users Battle Tag
		* @param string $herID - the Hero ID
		*
		* @return json/false - data if we have it, otherwise false
		*
		*/
	public function getHero($battleTag, $heroID)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$battleTag = str_replace('#', '-', $battleTag);

		// Check that the Battle Tag and Hero ID are valid
		if ($this->validBattleTag($battleTag) == true and $this->validHeroID($heroID) == true)
		{
			// Build the URL
			$url = sprintf($this->heroURL, $battleTag, $heroID);

			// Grab the data
			return $this->makeCURLCall($url);
		}
		// Battle Tag or Hero ID not valid - make a note of it and return false
		else
		{
			error_log('Invalid Battle Tag or Hero ID (Battle Tag: '. $battleTag .'Hero ID: '. $heroID .')');
			return false;
		}
	}

	/**
		* getItem
		*
		* Returns the Item data
		*
		* @param string $itemID - the Item ID
		*
		* @return json/false - data if we have it, otherwise false
		*
		*/
	public function getItem($itemID)
	{
		// Check that the Item ID is valid
		if ($this->validItemID($itemID) == true)
		{
			// Build the URL
			$url = sprintf($this->itemURL, $itemID);

			// Grab the data
			return $this->makeCURLCall($url);
		}
		// Item ID not valid - make a note of it and return false
		else
		{
			error_log('Invalid Item ID ('. $itemID .')');
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
		* @return json/false - data if we have it, otherwise false
		*
		*/
	public function getFollower($followerType)
	{
		// Make the Follower lowercase
		$followerType = strtolower($followerType);

		// Check that the Follower is valid
		if (in_array($followerType, $this->possibleFollowers))
		{
			// Build the URL
			$url = sprintf($this->followerURL, $followerType);

			// Grab the data
			return $this->makeCURLCall($url);
		}
		// Follower not valid - make a note of it and return false
		else
		{
			error_log('Invalid Follower ('. $followerType .')');
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
		* @return json/false - data if we have it, otherwise false
		*
		*/
	public function getArtisan($artisanType)
	{
		// Make the Artisan lowercase
		$artisanType = strtolower($artisanType);

		// Check that the Artisan is valid
		if (in_array($artisanType, $this->possibleArtisans))
		{
			// Build the URL
			$url = sprintf($this->artisanURL, $artisanType);

			// Grab the data
			return $this->makeCURLCall($url);
		}
		// Artisan not valid - make a note of it and return false
		else
		{
			error_log('Invalid Artisan ('. $artisanType .')');
			return false;
		}
	}

	/**
		* getPaperDoll
		*
		* Returns a link to the Paper Doll image
		*
		* @param string $class - the Class
		* @param string $gender - the Gender
		*
		* @return string/false - data if we have it, otherwise false
		*
		*/
	public function getPaperDoll($classType, $genderType)
	{
		// Check that the Class and Gender are valid
		if (in_array(strtolower($classType), $this->possibleClasses) and in_array(strtolower($genderType), $this->possibleGenders))
		{
			// Build and return the URL
			return sprintf($this->paperDollURL, strtolower($classType), strtolower($genderType));
		}
		// Class or Gender not valid - make a note of it and return false
		else
		{
			error_log('Invalid Class or Gender (Class: '. $classType .' Gender: '. $genderType .')');
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

		// Do we have any extra CURL options?
		if (is_array($this->extraCURLOptions) and !empty($this->extraCURLOptions))
		{
			// Set any extra CURL options
			curl_setopt_array($handle, $this->extraCURLOptions);
		}

		// Set the CURL options we need
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

		// Grab the data
		$data = curl_exec($handle);

		// Grab the CURL error code and message
		$errorCode = curl_errno($handle);
		$errorMessage = curl_error($handle);

		// Close the CURL connection
		curl_close($handle);

		// Check our error code is 0 (0 means OK!)
		if ($errorCode == 0)
		{
			// Decode the json response
			$data = json_decode($data, true);

			// Check we don't have an error code
			if (isset($data['code']) and isset($data['reason']))
			{
				// API error - make a note of it and return false
				error_log('API error: '. $data['code'] .' - '. $data['reason'] .' ('. $url .')!');
				return false;
			}
			// No errors - return the data
			else
			{
				return $data;
			}
		}
		// CURL error - make a note of it and return false
		else
		{
			error_log('CURL error "'. $errorCode .'" ('. $errorMessage .').');
			return false;
		}
	}

	/**
		* buildAPIURLs
		*
		* Build the various API URLs based on provided information
		*
		* @todo drop this method - build the URLs in getX methods
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
		* validBattleTag
		*
		* Checks that a supplied Battle Tag is valid - according to https://us.battle.net/support/en/article/BattleTagNamingPolicy
		*
		* @param string $battleTag - The Battle Tag
		*
		* @return bool - is the Battle Tag valid or not?
		*
		*/
	public function validBattleTag ($battleTag)
	{
		return preg_match($this->battleTagPattern, $battleTag) ? true : false;
	}

	/**
		* validHeroID
		*
		* Checks that a supplied Hero ID is valid
		*
		* @param string $heroID - The Hero ID
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
		* Checks that a supplied Item ID is valid
		*
		* @param string $itemID - The Item ID
		*
		* @return bool - is the Item ID valid or not?
		*
		*/
	public function validItemID ($itemID)
	{
		return preg_match($this->itemIDPattern, $itemID) ? true : false;
	}
}

?>