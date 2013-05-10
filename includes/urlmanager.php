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

class URLManager {

	private static $ch = null;
	private static $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6";
	private static $timeout = 30;
	private static $cookiefile = '/home/daruma/logs/growtogether/cookie.txt';

	public static function shutdown() {
		LogManager::trace(__CLASS__, "*** stopping ***");
    	curl_close(URLManager::$ch);
	}
	
	public static function clearCookies() {
		unlink(URLManager::$cookiefile);
	}
	
	private static function checkInit() {
		if (URLManager::$ch == null) {
			LogManager::trace(__CLASS__, "*** starting ***");
			URLManager::$ch = curl_init();
			register_shutdown_function(array('URLManager', 'shutdown'));
		}
	}
	
	public static function getURL($request, $post=null, $authstring=null, $referer=null) {
		URLManager::checkInit();
		LogManager::trace(__CLASS__, "getURL ".$request);
		
		curl_setopt(URLManager::$ch, CURLOPT_URL, $request); // set url to post to
		curl_setopt(URLManager::$ch, CURLOPT_FAILONERROR, 1);              // Fail on errors
		curl_setopt(URLManager::$ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt(URLManager::$ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt(URLManager::$ch, CURLOPT_TIMEOUT, URLManager::$timeout); // times out after 15s
		curl_setopt(URLManager::$ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		if ($referer != null)
			curl_setopt(URLManager::$ch, CURLOPT_REFERER, $referer);
		
		if (URLManager::$cookiefile != null) {
			curl_setopt(URLManager::$ch, CURLOPT_COOKIEJAR, URLManager::$cookiefile);
			curl_setopt(URLManager::$ch, CURLOPT_COOKIEFILE, URLManager::$cookiefile);
		}
		
		if ($post != null) {
			curl_setopt(URLManager::$ch, CURLOPT_POST, true);
			curl_setopt(URLManager::$ch, CURLOPT_POSTFIELDS, $post);
		}
		
		if ($authstring != null) {
			curl_setopt(URLManager::$ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($authstring)));
		}

		curl_setopt(URLManager::$ch, CURLOPT_USERAGENT, URLManager::$user_agent);

		return curl_exec(URLManager::$ch);
	}
}

?>