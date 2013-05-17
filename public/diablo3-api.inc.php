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

	/**
		* __construct
		*
		* @param string $battleTag - The users Battle Tag (https://us.battle.net/support/en/article/BattleTagNamingPolicy)
		* @param array $args - An array of optional settings for Protocol, Server and Locale
		*
		*/
	function __construct ($battleTag, $args = null)
	{
		// Replace '#' with '-' as some users may enter it with '#'
		$this->battleTag = str_replace('#', '-', $battleTag);

		// Validate that we have a valid Battle Tag
		if ($this->validBattleTag($this->battleTag) == true)
		{
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
		else
		{
			error_log('BattleTag provided not valid. ('. $battleTag .')');
			exit('Sorry, invalid Battle Tag');
		}
	}

	// TODO - Refactor to use ternery operator (smaller code footprint)
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