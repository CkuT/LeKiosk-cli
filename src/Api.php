<?php

namespace Colfej\LeKioskCLI;

abstract class Api {

	const URL = '';

	protected static function __request($verb, $url, $params = array(), $path = null) {

		$config = Configuration::load();

		throw new \Exception("Error Processing Request");
		
	}

	public static function get($url, $params = array()) {

		return self::__request('GET', $url, $params);
		
	}

	public static function download($url, $path) {

		return self::__request('GET', $url, array(), $path);
		
	}

}