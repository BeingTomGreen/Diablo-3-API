<?php

error_reporting(-1);

// Include the API class
require_once 'diablo3-api.inc.php';

// Set data to be used in the demo
$battleTag = 'BTG#2577';
$heroID = '27721760';
$itemID = 'COGHsoAIEgcIBBXIGEoRHYQRdRUdnWyzFB2qXu51MA04kwNAAFAKYJMD';
$followerType = 'enchantress';
$artisanType = 'blacksmith';
$classType = 'monk';
$genderType = 'male';

// Specify URL information here
$args = ['protocol' => 'http://', 'server' => 'eu', 'locale' => 'en_GB'];

// Create a new instance
$D3 = new D3 ($args);

// Set any extra CURL options here
// Can be any of the CURL constants defined here: http://php.net/manual/en/function.curl-setopt.php
$D3->extraCURLOptions = [
	CURLOPT_CONNECTTIMEOUT => 5
];

// Examples API calls
//var_dump($D3->getCareer($battleTag));
//var_dump($D3->getHero($battleTag, $heroID));
//var_dump($D3->getItem($itemID));
//var_dump($D3->getFollower($followerType));
//var_dump($D3->getArtisan($artisanType));
var_dump($D3->getPaperDoll($classType, $genderType));

?>