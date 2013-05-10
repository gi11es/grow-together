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

require_once (dirname(__FILE__).'/constants.php');
require_once (dirname(__FILE__).'/dbmanager.php');
require_once (dirname(__FILE__).'/cachemanager.php');
require_once (dirname(__FILE__).'/logmanager.php');
require_once (dirname(__FILE__).'/app.php');
require_once (dirname(__FILE__).'/../settings.php');

class User {
	private $id;
	private $status;
	private $session_key = "";
	
	private static $statements_started = false;
	private static $statement_getUser;
	private static $statement_getUserList;
	private static $statement_createUser;
	private static $statement_setStatus;
	private static $statement_setSessionKey;
	private static $statement_delete;

	public static function getUser($userid, $self=false, $api_client=null) {
		global $TABLE;
		global $COLUMN;
		global $STATUS;

		if (!User::$statements_started) User::prepareStatements();
		LogManager::trace(__CLASS__, "retrieving user with id=".$userid);

		// First try to retrieve the user from the cache
		try {
			$ibuser = CacheManager::get("User-".$userid);
			LogManager::trace(__CLASS__, "Found in the cache user with id=".$userid);
			return $ibuser;
		} catch (CacheManagerException $e) { // If that fails, get the user from the DB
			LogManager::trace(__CLASS__, "Can't find user in the cache, looking in the DB for user with id=".$userid);
			$result = User::$statement_getUser->execute($userid);	

			if (!$result || $result->numRows() != 1) {
				$ibuser = false;
			} else {
				$row = $result->fetchRow();
				$ibuser = new User();
				$ibuser->setId($row[$COLUMN["USER_ID"]]);
				$ibuser->setStatus($row[$COLUMN["STATUS"]], false);
				$ibuser->setSessionKey($row[$COLUMN["SESSION_KEY"]], false);
			}
			
			$result->free();
		}

		// We couldn't find that user id in the database or in the cache, let's create the user entry
		if (!$ibuser) {
			LogManager::trace(__CLASS__, "Can't be found in cache or DB, must create user with id=".$userid);
			$ibuser = new User();
			$ibuser->setId($userid);
			$ibuser->setStatus($STATUS["ACTIVE"], false);

			User::$statement_createUser->execute(array($ibuser->getId(), $STATUS["ACTIVE"]));
			try {
				CacheManager::set("User-".$ibuser->getId(), $ibuser);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
			}
			
			return $ibuser;
		} else {
			// Since we just fetched the user from the DB, let's put him/her in the cache
			try {
				CacheManager::set("User-".$ibuser->getId(), $ibuser);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
			}
			return $ibuser;
		}
	}
	
	public static function getUserList() {
		global $COLUMN;
		
		if (!User::$statements_started) User::prepareStatements();		
		$list = Array();
		
		$result = User::$statement_getUserList->execute(1);
		while ($row = $result->fetchRow()) {
			$list []= $row[$COLUMN["USER_ID"]];
		}
		$result->free();
		return $list;
	}
	
	public static function deleteUser($userid) {
		if (!User::$statements_started) User::prepareStatements();
	
		LogManager::trace(__CLASS__, "deleting user with id=".$userid);
			
		$result = User::$statement_delete->execute($userid);	

		if ($result != 1) {
			LogManager::error(__CLASS__, "Could not delete user entry for user_id=".$userid);
		}
		
		try {
			CacheManager::delete("User-".$userid);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
	}

	public function saveCache() {
		LogManager::trace(__CLASS__, "updating cache entry of user with id=".$this->id);
		try {
			CacheManager::replace("User-".$this->id, $this);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($newstatus, $persist=true) {
		if (!User::$statements_started) User::prepareStatements();
	
		$this->status = $newstatus;
		if ($persist) {
			$this->saveCache();
			User::$statement_setStatus->execute(array($this->status, $this->id));	
		}
	}
	
	public function getSessionKey() {
		return $this->session_key;
	}

	public function setSessionKey($newkey, $persist=true) {
		if (!User::$statements_started) User::prepareStatements();
	
		$this->session_key = $newkey;
		if ($persist) {
			$this->saveCache();
			User::$statement_setSessionKey->execute(array($this->session_key, $this->id));	
		}
	}
	
	public function hasAddedApp($api_client) {
		$fql_result = $api_client->fql_query("SELECT has_added_app FROM user WHERE uid = ".$this->id);
		
		if (isset($fql_result[0])) {
			return ($fql_result[0]["has_added_app"] == 1);
		} else return false;
	}
	
	public function getApps() {
		$allapps = App::getAppList();
		$apps = array();
		$result = array();
		
		foreach ($allapps as $appid => $userid) {
			if ($userid == $this->id) $apps []= $appid;
		}
		
		foreach ($apps as $appid)
			$result [$appid]= App::getApp($appid);
			
		return $result;
	}
	
	public static function recreateDBSchema() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;

		LogManager::info(__CLASS__, "Recreating the DB schema, this will drop the tables for this class");
		
		/**** TABLE STRUCTURE ****/

		DBManager::dropMasterDBTable("USER");
		DBManager::createMasterDBTable("USER", Array("USER_ID", "STATUS", "SESSION_KEY"));
		DBManager::alterMasterDBTablePrimaryKey("USER", Array("USER_ID"));
	}
	
	private static function prepareStatements() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
		
		LogManager::trace(__CLASS__, "Preparing DB statements for this class");
		
		User::$statement_getUser = DBManager::prepareReadMasterDB( 
				"SELECT ".$COLUMN["USER_ID"].", ".$COLUMN["STATUS"].", ".$COLUMN["SESSION_KEY"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]
				." WHERE ".$COLUMN["USER_ID"]." = ?"
						, array('integer'));
						
		User::$statement_getUserList = DBManager::prepareReadMasterDB( 
				"SELECT ".$COLUMN["USER_ID"]." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]." WHERE ?"
						, array('integer'));
		
		User::$statement_createUser = DBManager::prepareWriteMasterDB( 
				"INSERT INTO ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]." (".$COLUMN["USER_ID"].", ".$COLUMN["STATUS"].") VALUES(?, ?)"
						, array('integer', 'integer'));
						
		User::$statement_setStatus = DBManager::prepareWriteMasterDB( 
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]." SET ".$COLUMN["STATUS"]." = ? WHERE ".$COLUMN["USER_ID"]." = ?"
						, array('integer', 'integer'));

		User::$statement_setSessionKey = DBManager::prepareWriteMasterDB( 
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]." SET ".$COLUMN["SESSION_KEY"]." = ? WHERE ".$COLUMN["USER_ID"]." = ?"
						, array('text', 'integer'));
						
		User::$statement_delete = DBManager::prepareWriteMasterDB( 
				"DELETE FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["USER"]." WHERE ".$COLUMN["USER_ID"]." = ?"
						, array('integer'));
		
		User::$statements_started = true;
	}
}

?>