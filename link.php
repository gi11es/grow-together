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

require_once (dirname(__FILE__).'/includes/token.php');
require_once (dirname(__FILE__).'/includes/userhistory.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');
require_once (dirname(__FILE__).'/includes/cachemanager.php');
require_once (dirname(__FILE__).'/includes/app.php');

error_reporting (E_ALL);

$host = $_SERVER['REMOTE_ADDR'];

if (isset($_REQUEST["token"]) || isset($debugtoken)) {
	
	if (isset($_REQUEST["token"]))
		$tokenid = $_REQUEST["token"];
	else
		$tokenid = $debugtoken;
	
	try {
		$token = Token::getToken($tokenid);
		$source_app_id = $token->getSourceAppId();
		$dest_app_id = $token->getDestAppId();
		
		$source_app = App::getApp($source_app_id);
		$dest_app = App::getApp($dest_app_id);

		// We only credit apps if the link wasn't used before'
		if (!$token->isDeleted()) {
			// If there is an application the clicks credit should be transfered to, let's do it, otherwise credit this app
			$balance = App::getDonated($source_app_id) - App::getReceived($source_app_id);
			$transferto = $source_app->getTransferTo();
			$tocredit = $source_app_id;
			if ($balance > 0 && $transferto != null) {
				$transferapp = App::getApp($transferto);
				if ($transferapp) $tocredit = $transferto;
			}
			
			try {
				$lastclick = CacheManager::get("LastClick-".$host."-".$dest_app_id);
				if (time() - $lastclick < 86400) {
					$lastclick = CacheManager::replace("LastClick-".$host."-".$dest_app_id, time());
				} else {
					App::incrDonated($tocredit);
					App::incrReceived($dest_app_id);
					AppHistory::incrClicks($source_app_id, $dest_app_id);
				}
			} catch (CacheManagerException $e) {
				$lastclick = CacheManager::set("LastClick-".$host."-".$dest_app_id, time());
				App::incrDonated($tocredit);
				App::incrReceived($dest_app_id);
				AppHistory::incrClicks($source_app_id, $dest_app_id);
			}
	
			// Marks this specific link as visited
			$token->setDeleted(true);
		}
		
		header("Location: ".$dest_app->getLink());
	} catch (TokenException $e) {
		// Find the most needy app and direct them to it
		$apps_ids = App::getAppList();
		$apps_received = array();
		foreach ($apps_ids as $app_id => $ownerid) {
			if (App::getDonated($app_id) > 1) { // Ignore ones that haven't donated yet
				$apps_received[$app_id] = App::getReceived($app_id);
			}
		}
		
		if (!empty($apps_received)) {
			arsort($apps_received);
			$lastkey = end(array_keys($apps_received));
			$app = App::getApp($lastkey);
			header("Location: ".$app->getLink());
		} else {
			header("Location: http://www.facebook.com/apps/application.php?api_key=c8f3f4b796166594516dfc8d3496db27");
		}
	}
} else {
	// Find the most needy app and direct them to it
	$apps_ids = App::getAppList();
	$apps_received = array();
	foreach ($apps_ids as $app_id => $ownerid) {
		if (App::getDonated($app_id) > 1) { // Ignore ones that haven't donated yet
			$apps_received[$app_id] = App::getReceived($app_id);
		}
	}
	
	if (!empty($apps_received)) {
		arsort($apps_received);
		$lastkey = end(array_keys($apps_received));
		$app = App::getApp($lastkey);
		header("Location: ".$app->getLink());
	} else {
		header("Location: http://www.facebook.com/apps/application.php?api_key=c8f3f4b796166594516dfc8d3496db27");
	}
}

?>