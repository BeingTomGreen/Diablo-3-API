<?php

	require_once 'diablo3-api.inc.php';

	$battleTag = 'BTG#2577';
	$args = ['protocol' => 'http://', 'server' => 'eu', 'locale' => 'en_GB'];

	$D3 = new D3 ($battleTag);

/*

	$url = 'http://eu.battle.net/api/d3/profile/BTG-2577/index?locale=en_GB';
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);

	echo '<pre>';
	print_r($data);
	echo '</pre>';*/
?>