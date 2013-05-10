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
require_once (dirname(__FILE__).'/logmanager.php');

class TokenException extends Exception {
}

class Token {
	private $source_app_id;
	private $dest_app_id;
	private $deleted = false;
	private $uniquecode;
	
	public function setSourceAppId($source_app_id) {
		$this->source_app_id = $source_app_id;
	}
	
	public function setDestAppId($dest_app_id) {
		$this->dest_app_id = $dest_app_id;
	}
	
	public function setUniqueCode($code) {
		$this->uniquecode = $code;
	}
	
	public function setDeleted($newdeleted) {
		$this->deleted = $newdeleted;
		LogManager::trace(__CLASS__, "updating cache entry of token with unique code=".$this->uniquecode);
		try {
			CacheManager::replace("Token-".$this->uniquecode, $this);
		} catch (CacheManagerException $e) {
			LogManager::error(__CLASS__, $e->getMessage());
		}
	}
	
	public function getSourceAppId() { return $this->source_app_id; }
	public function getDestAppId() { return $this->dest_app_id; }
	public function getUserId() { return $this->userid; }
	public function isDeleted() { return $this->deleted; }
	
	public static function createToken($app_source_id, $app_dest_id) {
		$token = new Token();
		$token->setSourceAppId($app_source_id);
		$token->setDestAppId($app_dest_id);
		
		$uniquecode = sha1(time()."-".$app_source_id."-".$app_dest_id);
		$token->setUniqueCode($uniquecode);
		
		try {
			CacheManager::set("Token-".$uniquecode, $token, false, 3600); // 1 hour expiry
			LogManager::trace(__CLASS__, "Token ".$uniquecode.' successfully created');
		} catch (CacheManagerException $e) {
			LogManager::error(__CLASS__, $e->getMessage());
			throw new TokenException("Couldn't create the token for app_source_id=".$app_source_id);
		}
		
		return $uniquecode;
	}
	
	public static function getToken($tokenid) {
		try {
			$tok = CacheManager::get("Token-".$tokenid);
			return $tok;
		} catch (CacheManagerException $e) {
			throw new TokenException("Can't find the token in the cache");
		}
	}
	
	public static function deleteToken($tokenid) {		
		$token = Token::getToken($tokenid);
		$token->setDeleted(true);
	}
}

?>