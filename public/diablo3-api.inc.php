<?php

class D3 {
	// Hold the URL parts
	private $protocol = 'http://';
	private $server = 'eu';
	private $host = '.battle.net/';
	private $apiSlug = 'api/d3/';
	private $mediaSlug = 'd3/static/images/';
	private $locale = 'en_GB';

	// Hold the built URLs
	private $apiURL;
	private $mediaURL;

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

	// Holds the pool of available Memcached servers
	private $memcachedPool;

	// Time (in seconds) to cache items for
	public $cachePeriod = 120;

	// Holds the Memcahce instance
	private $memcache = null;

	/**
		* __construct
		*
		* @param array $args - Optional array of settings (protocol, server and locale)
		*
		*/
	function __construct ($args = null)
	{
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

		// Lets build the main part of the URLs to save us repeating ourselves
		$this->apiURL = $this->protocol . $this->server . $this->host . $this->apiSlug;
		$this->mediaURL = $this->protocol . $this->server . $this->host . $this->mediaSlug;

		// Check if we have the Memcache module and have been given an array of Memcached servers
		if (isset($args['memcachedPool']) and !empty($args['memcachedPool']) and class_exists('Memcache'))
		{
			// Create a new Memcache instance
			$this->memcache = new Memcache;

			// Loop through each of the specified servers
			foreach ($args['memcachedPool'] as $server)
			{
				// Add each server to the pool
				$this->memcache->connect($server['host'], $server['port']);
			}
		}
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
			$url = sprintf(
				$this->apiURL .'profile/%s/?locale='. $this->locale,
				$battleTag
			);

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
			$url = sprintf(
				$this->apiURL .'profile/%s/hero/%d?locale'. $this->locale,
				$battleTag,
				$heroID
			);

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
			$url = sprintf(
				$this->apiURL .'data/item/%s?locale='. $this->locale,
				$itemID
			);

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
			$url = sprintf(
				$this->apiURL .'data/follower/%s?locale='. $this->locale,
				strtolower($followerType)
			);

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
			$url = sprintf(
				$this->apiURL .'data/artisan/%s?locale='. $this->locale,
				strtolower($artisanType)
			);

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
			return sprintf(
				$this->mediaURL .'profile/hero/paperdoll/%s-%s.jpg',
				strtolower($classType),
				strtolower($genderType)
			);
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
		* @return json/bool - data if we have it, otherwise false
		*
		*/
	private function makeCURLCall($url)
	{
		// Check if we have a Memcache instance and see if the data is already cached
		if ($this->memcache != false and $this->memcache->get($url) != false)
		{
			return $this->memcache->get($url);
		}
		// Data not in cache lets grab it
		else
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
				// No errors - cache and return the data
				else
				{
					// Check if we are using Memcache
					if ($this->memcache != false)
					{
						// Store the data in Memcache using the URL as the key
						$this->memcache->set($url, $data, MEMCACHE_COMPRESSED, $this->cachePeriod);
					}

					// Return the data
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