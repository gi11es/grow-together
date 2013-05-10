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

require_once 'MDB2.php';

class AppHistoryException extends Exception {
}

	/* There is potentially a concurrency issue inside the cache with the donors/receivers list in the extremely unlikely
	 * case that two concurrent threads would modify one of the lists at the same time. In which case there is a possibility
	 * that one of the app ids added to the list will be lost. This could be solved with a semaphore but I have no will to do it now.
	 */

class AppHistory {
	
	private static $statements_started = false;
	private static $statement_getHistory;
	private static $statement_getOverallDonatedHistory;
	private static $statement_getOverallClicksHistory;
	private static $statement_getDetailedHistory;
	private static $statement_getDonatedHistory;
	private static $statement_getReceivedHistory;
	private static $statement_getSumDonatedHistory;
	private static $statement_getSumReceivedHistory;
	private static $statement_createQueries;
	private static $statement_createClicks;
	private static $statement_incrQueries;
	private static $statement_incrClicks;
	
	public static function getOverallDonations() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
	
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();

		$firstdate = date("Y-m-d-G", time() - 90000);
		$lastdate = date("Y-m-d-G", time() - 3600);
		
		// The prepared statement wasn't working for this one
		$receiverresult = AppHistory::$statement_getOverallDonatedHistory->execute(array($firstdate, $lastdate));	

		if (PEAR::isError($receiverresult)) {
			echo $receiverresult->getMessage().' - '.$receiverresult->getUserinfo();
			return 0;
		} else {
			$row = $receiverresult->fetchRow();
			$queries = $row[$COLUMN["QUERIES"]];
			$receiverresult->free();
			return $queries;
		}
	}
	
	public static function getOverallClicks() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
	
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();

		$firstdate = date("Y-m-d-G", time() - 90000);
		$lastdate = date("Y-m-d-G", time() - 3600);
		
		// The prepared statement wasn't working for this one
		$receiverresult = AppHistory::$statement_getOverallClicksHistory->execute(array($firstdate, $lastdate));	

		if (PEAR::isError($receiverresult)) {
			echo $receiverresult->getMessage().' - '.$receiverresult->getUserinfo();
			return 0;
		} else {
			$row = $receiverresult->fetchRow();
			$queries = $row[$COLUMN["CLICKS"]];
			$receiverresult->free();
			return $queries;
		}
	}
	
	public static function getCTRHistory($donorid, $receiverid) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
	
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();

		$firstdate = date("Y-m-d-G", time() - 90000);
		$lastdate = date("Y-m-d-G", time() - 3600);
		
		// The prepared statement wasn't working for this one
		$receiverresult = AppHistory::$statement_getHistory->execute(array($donorid, $receiverid, $firstdate, $lastdate));	

		if (PEAR::isError($receiverresult)) {
			echo $receiverresult->getMessage().' - '.$receiverresult->getUserinfo();
			return 0;
		} else {
			$row = $receiverresult->fetchRow();
			if ($row[$COLUMN["CLICKS"]] == 0) $ctr = 0; else
			$ctr = floatval($row[$COLUMN["CLICKS"]]) / floatval($row[$COLUMN["QUERIES"]]);
			$receiverresult->free();
			return $ctr;
		}
	}
	
	public static function getReceivedHistory($receiverid) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
	
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();

		$firstdate = date("Y-m-d-G", time() - 90000);
		
		// The prepared statement wasn't working for this one
		$receiverresult = AppHistory::$statement_getSumReceivedHistory->execute(array($receiverid, $firstdate));	

		if (PEAR::isError($receiverresult)) {
			echo $receiverresult->getMessage().' - '.$receiverresult->getUserinfo();
			return 0;
		} else {
			$row = $receiverresult->fetchRow();
			$received = $row[$COLUMN["CLICKS"]];
			$receiverresult->free();
			return $received;
		}
	}
	
	public static function getDonatedHistory($donorid) {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
	
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();

		$firstdate = date("Y-m-d-G", time() - 90000);
		
		// The prepared statement wasn't working for this one
		$donorresult = AppHistory::$statement_getSumDonatedHistory->execute(array($donorid, $firstdate));	

		if (PEAR::isError($donorresult)) {
			echo $donorresult->getMessage().' - '.$donorresult->getUserinfo();
			return 0;
		} else {
			$row = $donorresult->fetchRow();
			$donated = $row[$COLUMN["CLICKS"]];
			$donorresult->free();
			return $donated;
		}
	}
	
	public static function getAppDetailedHistory($appid, $start=null, $end=null) {
		global $COLUMN;	
		
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();
		
		$firstdate = ($start == null?date("Y-m-d-G", time() - 90000):date("Y-m-d-G", $start));
		$lastdate = ($end == null?date("Y-m-d-G", time() + 86400):date("Y-m-d-G", $end + 86400));
		
		$historyresult = array();
			
		$detailedresult = AppHistory::$statement_getDetailedHistory->execute(array($appid, $appid, $firstdate, $lastdate));

		if (PEAR::isError($detailedresult))
			echo $detailedresult->getMessage().' - '.$detailedresult->getUserinfo();
		else {
			while ($row = $detailedresult->fetchRow()) {
				$historyresult[$row[$COLUMN["HISTORY_DAY"]]][$row[$COLUMN["DONOR"]]][$row[$COLUMN["RECEIVER"]]]["queries"] = $row[$COLUMN["QUERIES"]];
				$historyresult[$row[$COLUMN["HISTORY_DAY"]]][$row[$COLUMN["DONOR"]]][$row[$COLUMN["RECEIVER"]]]["clicks"] = $row[$COLUMN["CLICKS"]];
			}
			$detailedresult->free();
		}
		
		return $historyresult;
	}
	
	public static function getAppHistory($appid, $start=null, $end=null) {
		global $COLUMN;	
		
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();
		
		$firstdate = ($start == null?date("Y-m-d-G", time() - 90000):date("Y-m-d-G", $start));
		$lastdate = ($end == null?date("Y-m-d-G", time() - 3600):date("Y-m-d-G", $end));
		
		$historyresult = array();
		
		$donors = array();
		$receivers = array();
			
		// Couldn't find today's list of apps this one has interacted with, let's try and pull all the history data from the DB
		$donorresult = AppHistory::$statement_getReceivedHistory->execute(array($appid, $firstdate, $lastdate));

		if (PEAR::isError($donorresult))
			echo $donorresult->getMessage().' - '.$donorresult->getUserinfo();
		else {
			while ($row = $donorresult->fetchRow()) {
				$historyresult["queriesReceived"][$row[$COLUMN["DONOR"]]] = $row[$COLUMN["QUERIES"]];
				$historyresult["clicksReceived"][$row[$COLUMN["DONOR"]]] = $row[$COLUMN["CLICKS"]];
				$donors []= $row[$COLUMN["DONOR"]];
			}
			$donorresult->free();
		}
		
		$receiverresult = AppHistory::$statement_getDonatedHistory->execute(array($appid, $firstdate, $lastdate));

		if (PEAR::isError($receiverresult))
			echo $receiverresult->getMessage().' - '.$receiverresult->getUserinfo();
		else {
			while ($row = $receiverresult->fetchRow()) {
				$historyresult["queriesDonated"][$row[$COLUMN["RECEIVER"]]] = $row[$COLUMN["QUERIES"]];
				$historyresult["clicksDonated"][$row[$COLUMN["RECEIVER"]]] = $row[$COLUMN["CLICKS"]];
				$receivers []= $row[$COLUMN["RECEIVER"]];
			}
			$receiverresult->free();
		}
		
		return $historyresult;
	}
		
	public static function incrQueries($donor_id, $receiver_id) {
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();
	
		$realdate = date("Y-m-d-G");	
		$result = AppHistory::$statement_incrQueries->execute(array($realdate, $donor_id, $receiver_id));
		if ($result != 1)
			AppHistory::$statement_createQueries->execute(array($realdate, $donor_id, $receiver_id));
	}
	
	public static function incrClicks($donor_id, $receiver_id) {
		if (!AppHistory::$statements_started) AppHistory::prepareStatements();
	
		$realdate = date("Y-m-d-G");	
		$result = AppHistory::$statement_incrClicks->execute(array($realdate, $donor_id, $receiver_id));
		if ($result != 1)
			AppHistory::$statement_createClikcs->execute(array($realdate, $donor_id, $receiver_id));
	}
	
	public static function recreateDBSchema() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;

		LogManager::info(__CLASS__, "Recreating the DB schema, this will drop the tables for this class");
		
		/**** TABLE STRUCTURE ****/
		
		DBManager::dropMasterDBTable("APP_HISTORY");
		DBManager::createMasterDBTable("APP_HISTORY", Array("DONOR", "RECEIVER", "HISTORY_DAY", "QUERIES", "CLICKS"));
		DBManager::alterMasterDBTablePrimaryKey("APP_HISTORY", Array("DONOR", "RECEIVER", "HISTORY_DAY"));
	}
	
	private static function prepareStatements() {
		global $TABLE;
		global $COLUMN;
		global $DATABASE_WRITE;
		global $COLUMN_TYPE;
		global $STATUS;
		
		LogManager::trace(__CLASS__, "Preparing DB statements for this class");
		
		AppHistory::$statement_getReceivedHistory = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["DONOR"]
				.", SUM(".$COLUMN["CLICKS"]
				.") AS ".$COLUMN["CLICKS"]
				.", SUM(".$COLUMN["QUERIES"]
				.") AS ".$COLUMN["QUERIES"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["RECEIVER"]." = ? AND ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
				." GROUP BY ".$COLUMN["DONOR"]
						, array('text', 'timestamp', 'timestamp'));
						
		AppHistory::$statement_getDonatedHistory = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["RECEIVER"]
				.", SUM(".$COLUMN["CLICKS"]
				.") AS ".$COLUMN["CLICKS"]
				.", SUM(".$COLUMN["QUERIES"]
				.") AS ".$COLUMN["QUERIES"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["DONOR"]." = ? AND ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
				." GROUP BY ".$COLUMN["RECEIVER"]
						, array('text', 'timestamp', 'timestamp'));
						
		AppHistory::$statement_getHistory = DBManager::prepareReadMasterDB(
				"SELECT SUM(".$COLUMN["CLICKS"]
				.") AS ".$COLUMN["CLICKS"]
				.", SUM(".$COLUMN["QUERIES"]
				.") AS ".$COLUMN["QUERIES"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["DONOR"]." = ? AND ".$COLUMN["RECEIVER"]." = ? AND ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
						, array('text', 'text', 'timestamp', 'timestamp'));
						
		AppHistory::$statement_getDetailedHistory = DBManager::prepareReadMasterDB(
				"SELECT ".$COLUMN["HISTORY_DAY"]
				.", ".$COLUMN["DONOR"]
				.", ".$COLUMN["RECEIVER"]
				.", ".$COLUMN["QUERIES"]
				.", ".$COLUMN["CLICKS"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE (".$COLUMN["DONOR"]." = ? OR ".$COLUMN["RECEIVER"]." = ?) AND ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
						, array('text', 'text', 'timestamp', 'timestamp'));
	
		AppHistory::$statement_getOverallDonatedHistory = DBManager::prepareReadMasterDB(
				"SELECT SUM(".$COLUMN["QUERIES"].") AS ".$COLUMN["QUERIES"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
						, array('timestamp', 'timestamp'));
						
		AppHistory::$statement_getOverallClicksHistory = DBManager::prepareReadMasterDB(
				"SELECT SUM(".$COLUMN["CLICKS"].") AS ".$COLUMN["CLICKS"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["HISTORY_DAY"]." >= ? AND ".$COLUMN["HISTORY_DAY"]." <= ?"
						, array('timestamp', 'timestamp'));
						
		AppHistory::$statement_getSumDonatedHistory = DBManager::prepareReadMasterDB(
				"SELECT SUM(".$COLUMN["CLICKS"].") AS ".$COLUMN["CLICKS"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["DONOR"]." = ? AND ".$COLUMN["HISTORY_DAY"]." >= ? GROUP BY ".$COLUMN["DONOR"]
						, array('text', 'timestamp'));
						
		AppHistory::$statement_getSumReceivedHistory = DBManager::prepareReadMasterDB(
				"SELECT SUM(".$COLUMN["CLICKS"].") AS ".$COLUMN["CLICKS"]
				." FROM ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." WHERE ".$COLUMN["RECEIVER"]." = ? AND ".$COLUMN["HISTORY_DAY"]." >= ? GROUP BY ".$COLUMN["RECEIVER"]
						, array('text', 'timestamp'));
						
		AppHistory::$statement_createQueries = DBManager::prepareWriteMasterDB(
				"INSERT INTO ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." (".$COLUMN["HISTORY_DAY"]
				.",".$COLUMN["DONOR"]
				.", ".$COLUMN["RECEIVER"]
				.", ".$COLUMN["CLICKS"]
				.", ".$COLUMN["QUERIES"].") VALUES(?, ?, ?, 0, 1)"
						, array('timestamp', 'text', 'text'));
						
		AppHistory::$statement_createClicks = DBManager::prepareWriteMasterDB(
				"INSERT INTO ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." (".$COLUMN["HISTORY_DAY"]
				.",".$COLUMN["DONOR"]
				.", ".$COLUMN["RECEIVER"]
				.", ".$COLUMN["QUERIES"]
				.", ".$COLUMN["CLICKS"].") VALUES(?, ?, ?, 1, 1)"
						, array('timestamp', 'text', 'text'));
						
		AppHistory::$statement_incrQueries = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." SET ".$COLUMN["QUERIES"]." = ".$COLUMN["QUERIES"]." + 1 WHERE ".$COLUMN["HISTORY_DAY"]." = ? AND ".$COLUMN["DONOR"]." = ? AND ".$COLUMN["RECEIVER"]." = ?"
						, array('timestamp', 'text', 'text'));
				
		AppHistory::$statement_incrClicks = DBManager::prepareWriteMasterDB(
				"UPDATE ".$DATABASE_WRITE["PREFIX"].$TABLE["APP_HISTORY"]
				." SET ".$COLUMN["CLICKS"]." = ".$COLUMN["CLICKS"]." + 1 WHERE ".$COLUMN["HISTORY_DAY"]." = ? AND ".$COLUMN["DONOR"]." = ? AND ".$COLUMN["RECEIVER"]." = ?"
						, array('timestamp', 'text', 'text'));
		
		AppHistory::$statements_started = true;
	}
}

?>
