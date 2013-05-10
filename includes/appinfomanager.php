<?php

/* 
 	Copyright (C) 2007 Gilles Dubuc.
 
 	This file is part of Grow Together.

    Grow Together is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Grow Together is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Grow Together.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once (dirname(__FILE__).'/logmanager.php');
require_once (dirname(__FILE__).'/urlmanager.php');
require_once (dirname(__FILE__).'/cachemanager.php');
require_once (dirname(__FILE__).'/../settings.php');

class AppInfoManagerException extends Exception {
}

class AppInfoManager {
	static public $debug = false;
	
	static public function getName($appid, $emptycache=false) {
		
		try {
			if ($emptycache)
				CacheManager::delete("AppName-".$appid);
		} catch (CacheManagerException $e) {}

		try {
			$name = CacheManager::get("AppName-".$appid);
		} catch (CacheManagerException $e) {
			LogManager::trace(__CLASS__, "Application name is not in the cache, crawling it");
			
			$name = AppInfoManager::crawlName($appid);
						
			try {
				CacheManager::set("AppName-".$appid, $name);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
				throw new AppInfoManagerException("Couldn't set the cache entry for this app's name");
			}
		}
		
		return html_entity_decode($name, ENT_QUOTES);;
	}
	
	static public function getActiveUsers($appid, $emptycache=false) {
		try {
			if ($emptycache)
				CacheManager::delete("AppActiveUsers-".$appid);
		} catch (CacheManagerException $e) {}
	
		try {
			$activeusers = CacheManager::get("AppActiveUsers-".$appid);
		} catch (CacheManagerException $e) {
			LogManager::trace(__CLASS__, "Application active users count is not in the cache, crawling it");
			
			try {
				$activeusers = AppInfoManager::crawlActiveUsers($appid);
			} catch (AppInfoManagerException $e) {
				$activeusers = 0;
			}
			
			try {
				CacheManager::set("AppActiveUsers-".$appid, $activeusers);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
			}
		}
		
		return $activeusers;
	}
	
	static public function getIcon($appid, $emptycache=false) {
		try {
			if ($emptycache)
				CacheManager::delete("AppIcon-".$appid);
		} catch (CacheManagerException $e) {}	
	
		try {
			$icon = CacheManager::get("AppIcon-".$appid);
		} catch (CacheManagerException $e) {
			LogManager::trace(__CLASS__, "Application icon is not in the cache, crawling it");
			$icon = AppInfoManager::crawlIcon($appid);
			try {
				CacheManager::set("AppIcon-".$appid, $icon);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
			}
		}
		
		return $icon;
	}
	
	static private function crawlName($api_key) {
		LogManager::trace(__CLASS__, "Crawling facebook for application with id=".$api_key);

		$result = URLManager::getURL("http://www.facebook.com/apps/application.php?api_key=".$api_key, null, null, "http://www.facebook.com/home.php?");

		if (AppInfoManager::$debug)
			echo htmlentities($result);

		preg_match('/<title>([^|]+)\| Facebook<\/title>/si', $result, $matches);
		if (!isset($matches[1])) {
			LogManager::error(__CLASS__, "Couldn't retrieve name from application about page for api key=".$api_key);
			throw new AppInfoManagerException("Couldn't retrieve application name for api key=".$api_key." (regexp failed)");
		} else $name = html_entity_decode(trim($matches[1]), ENT_QUOTES);
		
		if (strcmp($name, "Welcome to Facebook!") == 0 || strcmp($name, "Home") == 0)
			throw new AppInfoManagerException("Couldn't retrieve application name for api key=".$api_key." (main page)");
		
		return $name;
	}
	
	static public function crawlActiveUsers($api_key) {
		LogManager::trace(__CLASS__, "Crawling facebook for application with id=".$api_key);

		$result = URLManager::getURL("http://www.facebook.com/apps/application.php?api_key=".$api_key, null, null, "http://www.facebook.com/home.php?");

		preg_match('/>([0123456789,]+) daily active users/si', $result, $matches3);


		if (!isset($matches3[1])) {
			LogManager::error(__CLASS__, "Couldn't retrieve active users count from application about page for api key=".$api_key);
			throw new AppInfoManagerException("Couldn't retrieve application name");
		} else $activeusers = intval(str_replace(",", "", $matches3[1]));
		
		return $activeusers;
	}
	
	static public function crawlIcon($api_key) {
		$name = AppInfoManager::getName($api_key);
			
		LogManager::trace(__CLASS__, "Crawling facebook for application with id=".$api_key);

		$result = URLManager::getURL("http://www.facebook.com/apps/application.php?api_key=".$api_key);

		$name = str_replace("?", "\?", $name);
		$name = str_replace("(", "\(", $name);
		$name = str_replace(")", "\)", $name);
		$name = str_replace("/", "\/", $name);
		$name = str_replace('$', '\$', $name);
		$name = str_replace('.', '\.', $name);
		
		// url('http://photos-c.ak.facebook.com/photos-ak-sctm/v43/22/6184607710/app_2_6184607710_8753.gif');" ><a href="http://apps.facebook.com/funarcade">Fun Arcade</a>

		preg_match("/url\('([^']+)'\);\"\s*><a\s*href=\"([^\"]+)\">\s*".htmlentities($name, ENT_QUOTES)."\s*<\/a>/si", $result, $matches);
		if (!isset($matches[1])) {
			preg_match("/url\('([^']+)'\)\">\s*<span>\s*".htmlentities($name, ENT_QUOTES)."\s*<\/span>/si", $result, $matches2);
			if (!isset($matches2[1])) {
				LogManager::error(__CLASS__, "Couldn't retrieve icon from application about page for api key=".$api_key);
				$icon = "http://static.ak.facebook.com/images/icons/hidden.gif?12:27651";
			} else $icon = $matches2[1];
		} else $icon = $matches[1];

		return $icon;
	}
}