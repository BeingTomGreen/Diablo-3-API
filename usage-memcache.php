<?php

// Enable full error reporting
error_reporting(-1);

// Set the timezone, required for Authenticated requests
date_default_timezone_set('GMT');

// Include the API class
require_once 'diablo3-api.inc.php';

// Create our new Memcache instance
$memcache = new Memcache();

// Connect to our Memcache server
$memcache->connect('127.0.0.1', '11211');

// Time (in seconds) to cache items for
$cachePeriod = 120;

// Create a new instance of the D3 class
$D3 = new D3 (['protocol' => 'http://', 'server' => 'eu', 'locale' => 'en_GB']);

// Set the Battle Tag
$battleTag = 'BTG#2577';

// Set a key for caching the item (suggest <method-name>-<arguments>)
$key = 'career-'. $battleTag;

// Check if we have the data already cached
if ($memcache->get($key) != false)
{
  // We have the data, lets use it
  $data = $memcache->get($key);
}
// Data not in cache lets grab it
else
{
  // Grab the data
  $data = $D3->getCareer($battleTag);

  // Cache the data for later
  $memcache->set($key, $data, MEMCACHE_COMPRESSED, $cachePeriod);
}

var_dump($data);

?>