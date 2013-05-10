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

require_once (dirname(__FILE__)."/../settings.php");
require_once (dirname(__FILE__).'/constants.php');
require_once (dirname(__FILE__).'/cachemanager.php');

class UserHistoryException extends Exception {
}

class UserHistory {
	private static $duration = 1800; // half an hour
	
	public function setUserHistory($appid, $userid) {
		try {
			CacheManager::set('UserHistory-'.$appid.'-'.$userid, time(), false, UserHistory::$duration);
		} catch (CacheManagerException $e) {
			throw new UserHistoryException("Couldn't set user history entry for userid=".$userid);
		}
	}
	
	public function replaceUserHistory($appid, $userid) {
		try {
			CacheManager::replace('UserHistory-'.$appid.'-'.$userid, time(), false, UserHistory::$duration);
		} catch (CacheManagerException $e) {
			throw new UserHistoryException("Couldn't replace user history entry for userid=".$userid);
		}
	}
	
	public function getUserHistory($appid, $userid) {
		try {
			return CacheManager::get('UserHistory-'.$appid.'-'.$userid);
		} catch (CacheManagerException $e) {
			throw new UserHistoryException("Couldn't replace user history entry for userid=".$userid);
		}
	}
}

?>