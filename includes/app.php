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
require_once (dirname(__FILE__).'/../settings.php');
require_once (dirname(__FILE__).'/../../client/facebook.php');

class AppException extends Exception {
}

class App {
	private static $applist;
	private $id;
	private $userid;
	private $api_key;
	private $text = "";
	private $link = "";
	private $transfer_to; // App that will receive credits from this one
	
	private static $statements_started = false;
	private static $statement_getApp;
	private static $statement_getAppList;
	private static $statement_createApp;
	private static $statement_delete;
	private static $statement_setText;
	private static $statement_setLink;
	private static $statement_setTransferTo;
	private static $statement_incrDonated;
	private static $statement_incrReceived;
	private static $statement_getDonated;
	private static $statement_getReceived;

	public static function getApp($appid) {
		global $TABLE;
		global $COLUMN;
		global $STATUS;
		
		if (!App::$statements_started) App::prepareStatements();

		LogManager::trace(__CLASS__, "retrieving app with id=".$appid);

		// First try to retrieve the app from the cache
		try {
			$app = CacheManager::get("App-".$appid);
			LogManager::trace(__CLASS__, "Found in the cache app with id=".$appid);
			return $app;
		}
		catch (CacheManagerException $e) { // If that fails, get the app from the DB
			LogManager::trace(__CLASS__, "Can't find app in the cache, looking in the DB for app with id=".$appid);
			$result = App::$statement_getApp->execute($appid);

			if (!$result || PEAR::isError($result) || $result->numRows() != 1) {
				$app = false;
			} else {
				$row = $result->fetchRow();
				$app = new App();
				$app->setId($row[$COLUMN["APP_ID"]]);
				$app->setUserId($row[$COLUMN["USER_ID"]]);
				$app->setApiKey($row[$COLUMN["API_KEY"]]);
				$app->setText($row[$COLUMN["TEXT"]], false);
				$app->setLink($row[$COLUMN["LINK"]], false);
				$app->setTransferTo($row[$COLUMN["TRANSFER_TO"]], false);
				$result->free();
			}
			
		} 

		if (!$app) {
			LogManager::error(__CLASS__, "Couldn't find app with id=".$appid);
			throw new AppException("App is missing in both the cache and the DB");
		} else {
			// Since we just fetched the app from the DB, let's put him/her in the cache
			try {
				CacheManager::set("App-".$app->getId(), $app);
			} catch (CacheManagerException $e) {
				LogManager::error(__CLASS__, $e->getMessage());
			}
			return $app;
		}
	}	
	
	public function setId($appid) {	$this->id = $appid;	}
	public function getId() {	return $this->id;	}
	public function setUserId($userid) { $this->userid = $userid; }
	public function getUserId() {	return $this->userid;	}
	public function setApiKey($apikey) { $this->api_key = $apikey; }
	public function getApiKey() {	return $this->api_key;	}
	
	public function setText($text, $persist=true) { 
		if (!App::$statements_started) App::prepareStatements();

		$this->text = $text; 
		
		if ($persist) {
			$this->saveCache();
			App::$statement_setText->execute(array($this->text, $this->id));
		}
	}
	
	public function getText() {	return stripslashes($this->text);	}
	
	public function setLink($link, $persist=true) { 
		if (!App::$statements_started) App::prepareStatements();
	
		$this->link = $link; 
		
		if ($persist) {
			$this->saveCache();
			App::$statement_setLink->execute(array($this->link, $this->id));
		}
	}
	
	public function getLink() {	return $this->link;	}
	
	public function setTransferTo($appid, $persist=true) { 
		if (!App::$statements_started) App::prepareStatements();
	
		$this->transfer_to = $appid; 
		
		if ($persist) {
			$this->saveCache();
			App::$statement_setTransferTo->execute(array($this->transfer_to, $this->id));
		}
	}
	
	public function getTransferTo() {	return $this->transfer_to;	}
	
	public static function getDonated($appid) {
		global $COLUMN;
		
		if (!App::$statements_started) App::prepareStatements();
		
		try {
			$donated = CacheManager::get("App-Donated-".$appid);
		} catch (CacheManagerException $e) {
			$result = App::$statement_getDonated->execute($appid);
			if (!$result || $result->numRows() != 1) {
				throw new AppException("Donated information missing in both cache and DB");
			} else {
				$row = $result->fetchRow();
				$donated = $row[$COLUMN["DONATED"]];
				try {
					CacheManager::set("App-Donated-".$appid, $donated);
				} catch (CacheManagerException $ex) {
					LogManager::error(__CLASS__, $ex->getMessage());
				}
				$result->free();
			}
		}
		return $donated;
	}
	
	public function getLocalDonated() {
		return App::getDonated($this->id);
	}
	
	public static function getReceived($appid) {
		global $COLUMN;
		
		if (!App::$statements_started) App::prepareStatements();
		
		try {
			$received = CacheManager::get("App-Received-".$appid);
		} catch (CacheManagerException $e) {
			$result = App::$statement_getReceived->execute($appid);
			if (!$result || $result->numRows() != 1) {
				throw new AppException("Received information missing in both cache and DB");
			} else {
				$row = $result->fetchRow();
				$received = $row[$COLUMN["RECEIVED"]];
				try {
					CacheManager::set("App-Received-".$appid, $received);
				} catch (CacheManagerException $ex) {
					LogManager::error(__CLASS__, $ex->getMessage());
				}
				$result->free();
			}
		}
		return $received;
	}
	
	public function getLocalReceived() {
		return App::getReceived($this->id);
	}
	
	public static function incrDonated($appid) {
		if (!App::$statements_started) App::prepareStatements();
	
		App::getDonated($appid);
		try {
			CacheManager::increment("App-Donated-".$appid);
		} catch (CacheManagerException $e) {
			LogManager::error(__CLASS__, $e->getMessage());
		}
		App::$statement_incrDonated->execute($appid);
	}
	
	public static function incrReceived($appid) {
		if (!App::$statements_started) App::prepareStatements();
	
		App::getReceived($appid);
		try {
			CacheManager::increment("App-Received-".$appid);
		} catch (CacheManagerException $e) {
			LogManager::error(__CLASS__, $e->getMessage());
		}
		App::$statement_incrReceived->execute($appid);
	}
	
	public function saveCache() {
		LogManager::trace(__CLASS__, "updating cache entry of app with id=".$this->id);
		try {
			CacheManager::replace("App-".$this->id, $this);
		} catch (CacheManagerException $e) {
			LogManager::error(__CLASS__, $e->getMessage());
		}
	}
	
	public static function createApp($api_key, $userid) {
		if (!App::$statements_started) App::prepareStatements();
	
		$app = new App();
		$app->setId(sha1($api_key."-".$userid));
		$app->setUserId($userid);
		$app->setApiKey($api_key);
		try {
			CacheManager::set("App-".$app->getId(), $app);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
		App::$statement_createApp->execute(array($app->getId(), $userid, $api_key));
		
		App::addToApplist($app->getId(), $userid);
		
		return $app;
	}
	
	public static function getAppList() {
		global $COLUMN;
		
		if (!App::$statements_started) App::prepareStatements();
		
		try {
			App::$applist = CacheManager::get("AppList");
		} catch (CacheManagerException $e) {
			$result = App::$statement_getAppList->execute(1);

			App::$applist = array();
			
			if ($result && $result->numRows() > 0) {
				while ($row = $result->fetchRow()) {
					App::$applist[$row[$COLUMN["APP_ID"]]] = $row[$COLUMN["USER_ID"]];
				}
			}
			$result->free();
			
			try {
				CacheManager::set("AppList", App::$applist);
			} catch (CacheManagerException $ex) {
				LogManager::error(__CLASS__, $ex->getMessage());
			}
		}
		
		return App::$applist;
	}
	
	public static function addToAppList($appid, $userid) {
		App::getAppList();
		App::$applist[$appid] = $userid;
		try {
			CacheManager::replace("AppList", App::$applist);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
	}
	
	public static function deleteFromAppList($appid) {
		App::getAppList();
		if (isset(App::$applist[$appid]))
			unset(App::$applist[$appid]);
		else throw new AppException("Trying to delete an application from the list which is not there");
		try {
			CacheManager::replace("AppList", App::$applist);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
	}
	
	public function publishActionNewApp($api_client) {
		$title_data = array ("app" => "<a href=\"http://www.facebook.com/apps/application.php?api_key=".$this->getApiKey()."\">".AppInfoManager::getName($this->getApiKey())."</a>");
		$api_client->feed_publishTemplatizedAction($this->getUserId(), 
	"{actor} added {app} to the <a href=\"http://apps.facebook.com/growtogether/index.php\">Grow Together</a> community",
	json_encode($title_data),
	 "{actor} joined the non-profit cross-promotion revolution. Promoting a Facebook application can be achieved for free, by joining this <a href=\"http://apps.facebook.com/growtogether/index.php\">healthy pool of cross-promoting applications</a>.",
	 "", "" );
	}
	
	public function publishActionExistingApp($api_client) {
		$title_data = array ("app" => "<a href=\"http://www.facebook.com/apps/application.php?api_key=".$this->getApiKey()."\">".AppInfoManager::getName($this->getApiKey())."</a>");
		$body_data = array ("app" => $title_data["app"], "donated" => $this->getLocalDonated(), "received" => $this->getLocalReceived());
		if ($body_data["donated"] > 0) {
		$api_client->feed_publishTemplatizedAction($this->getUserId(), 
	"{actor} <fb:if-multiple-actors>use<fb:else>uses</fb:else></fb:if-multiple-actors> <a href=\"http://apps.facebook.com/growtogether/index.php\">Grow Together</a> to cross-promote {app}",
	json_encode($title_data),
	 "{app} donated {donated} clicks and received {received}.",
	 json_encode($body_data), $title_data["app"]." is part of a community of facebook apps that exchange thousands of clicks every day on <a href=\"http://apps.facebook.com/growtogether/index.php\">Grow Together</a>, all for free." );
		}
	}
	
	public function delete() {
		if (!App::$statements_started) App::prepareStatements();
	
		LogManager::trace(__CLASS__, "deleting app with id=".$this->id);
		
		$result = App::$statement_delete->execute($this->id);		

		if ($result != 1) {
			LogManager::error(__CLASS__, "Could not delete app entry for user_id=".$this->id);
		}
		
		try {
			CacheManager::delete("App-".$this->id);
		} catch (CacheManagerException $ex) {
			LogManager::error(__CLASS__, $ex->getMessage());
		}
		App::deleteFromAppList($this->id);
	}
	
	public static function recreateDBSchema() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;

		LogManager::info(__CLASS__, "Recreating the DB schema, this will drop the tables for this class");
		
		/**** TABLE STRUCTURE ****/
		
		DBManager::dropMasterDBTable("APP");
		DBManager::createMasterDBTable("APP", Array("APP_ID", "USER_ID", "API_KEY", "DONATED", "RECEIVED", "TEXT", "LINK", "TRANSFER_TO"));
		DBManager::alterMasterDBTablePrimaryKey("APP", Array("APP_ID"));
	}
	
	private static function prepareStatements() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
		
		LogManager::trace(__CLASS__, "Preparing DB statements for this class");
		
		App::$statement_getApp = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["APP_ID"]
						.", ".$COLUMN["USER_ID"]
						.", ".$COLUMN["API_KEY"]
						.", ".$COLUMN["TEXT"]
						.", ".$COLUMN["LINK"]
						.", ".$COLUMN["TRANSFER_TO"]
						." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
						." WHERE ".$COLUMN["APP_ID"]." = ?"
						, array('text'));
						
		App::$statement_getAppList = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["APP_ID"].", ".$COLUMN["USER_ID"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." WHERE ?"
				, array('integer')
		);
		
		App::$statement_createApp = DBManager::prepareWriteMasterDB(
				"INSERT INTO ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." (".$COLUMN["APP_ID"].",".$COLUMN["USER_ID"].", ".$COLUMN["API_KEY"].") VALUES(?, ?, ?)"
				, array('text', 'integer', 'text')
		);
		
		App::$statement_delete = DBManager::prepareWriteMasterDB(
				"DELETE FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text')
		);
		
		App::$statement_setText = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." SET ".$COLUMN["TEXT"]." = ? WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text', 'text')
		);
		
		App::$statement_setLink = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." SET ".$COLUMN["LINK"]." = ? WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text', 'text')
		);
		
		App::$statement_setTransferTo = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." SET ".$COLUMN["TRANSFER_TO"]." = ? WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text', 'text')
		);
		
		App::$statement_incrDonated = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." SET ".$COLUMN["DONATED"]." = ".$COLUMN["DONATED"]." + 1 WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text')
		);
		
		App::$statement_incrReceived = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." SET ".$COLUMN["RECEIVED"]." = ".$COLUMN["RECEIVED"]." + 1 WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text')
		);
		
		App::$statement_getDonated = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["DONATED"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text')
		);
		
		App::$statement_getReceived = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["RECEIVED"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP"]
				." WHERE ".$COLUMN["APP_ID"]." = ?"
				, array('text')
		);
		
		App::$statements_started = true;
	}
}