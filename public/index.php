<?php

	require_once 'diablo3-api.inc.php';

	$battleTag = 'BTG#2577';
	$heroID = '27721760';
	$args = ['protocol' => 'http://', 'server' => 'eu', 'locale' => 'en_GB'];

	$D3 = new D3 ($args);

	// Career Data
	//var_dump($D3->getCareer($battleTag));
	var_dump($D3->getHero($battleTag, $heroID));
?>