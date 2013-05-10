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
require_once (dirname(__FILE__).'/../settings.php');

class CacheManagerException extends Exception {
}

class CacheManager {
	private static $started = false;
	private static $memcache = null;

	private static function initCheck() {
		global $MEMCACHE;
		
		if (!CacheManager::$started) {
			LogManager::trace(__CLASS__, "*** starting ***");
			CacheManager::$memcache = new Memcache;
			$result = CacheManager::$memcache->connect($MEMCACHE["HOST"], $MEMCACHE["PORT"]);
			if (!$result)
				throw new CacheManagerException("Could not locate memcache on ".$MEMCACHE["HOST"].":".$MEMCACHE["PORT"]);
			CacheManager::$started = true;
			register_shutdown_function(array('CacheManager', 'shutdown'));
		}
	}

	public static function shutdown() {
		LogManager::trace(__CLASS__, "*** stopping ***");
		if (CacheManager::$started && CacheManager::$memcache) {
			CacheManager::$memcache->close();
		}
	}
	
	public static function get($key) {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "getting object with key=".$MEMCACHE["PREFIX"].$key);
		$result = CacheManager::$memcache->get($MEMCACHE["PREFIX"].$key);
		if (!$result && is_bool($result))
			throw new CacheManagerException("This key is missing or has expired");
		return $result;
	}
	
	public static function getStats() {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "getting stats");
		$result = CacheManager::$memcache->getStats();
		if (!$result && is_bool($result))
			throw new CacheManagerException("Failed to obtain server stats");
		return $result;
	}
	
	// The default is 24 hours expiry for a cache element
	public static function set($key, $obj, $compressed=false, $duration=86400) {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "setting object with key=".$MEMCACHE["PREFIX"].$key);
		if (!CacheManager::$memcache->set($MEMCACHE["PREFIX"].$key, $obj, $compressed, $duration))
			throw new CacheManagerException("Failed to set value in the cache for key=".$key);
	}
	
	public static function replace($key, $obj, $compressed=false, $duration=86400) {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "replacing object with key=".$MEMCACHE["PREFIX"].$key);
		if (!CacheManager::$memcache->replace($MEMCACHE["PREFIX"].$key, $obj, $compressed, $duration))
			throw new CacheManagerException("Failed to replace value in the cache for key=".$key);
	}
	
	public static function increment($key) {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "incrementing object with key=".$MEMCACHE["PREFIX"].$key);
		if (!CacheManager::$memcache->increment($MEMCACHE["PREFIX"].$key))
			throw new CacheManagerException("Failed to increment value in the cache for key=".$key);
	}
	
	public static function delete($key) {
		global $MEMCACHE;
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "deleting object with key=".$MEMCACHE["PREFIX"].$key);
		if (!CacheManager::$memcache->delete($MEMCACHE["PREFIX"].$key))
			throw new CacheManagerException("Failed to delete value in the cache for key=".$key);
	}
	
	public static function flush() {
		CacheManager::initCheck();
		
		LogManager::trace(__CLASS__, "Flushing cache");
		if (!CacheManager::$memcache->flush())
			throw new CacheManagerException("Failed to flush the cache");
	}
}

?>