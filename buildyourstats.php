#!/usr/bin/php

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

// This is a cron job that builds the your stats page

require_once (dirname(__FILE__).'/includes/user.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');
require_once (dirname(__FILE__).'/includes/cachemanager.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');
require_once (dirname(__FILE__).'/includes/constants.php');
require_once (dirname(__FILE__).'/includes/uihelper.php');
require_once (dirname(__FILE__).'/../client/facebook.php');
require_once (dirname(__FILE__).'/settings.php');

$apps = App::getAppList();

foreach ($apps as $appid => $owner) {
	try {
		$app = App::getApp($appid);
		$api_keys[$app->getApiKey()] = $app->getId();
		$apps[$appid] = $app;
	} catch (AppException $e) { unset($apps[$appid]); }
	}

if (!isset($selected)) {
	foreach ($apps as $appid => $owner) {
		try {
		echo buildYourStats($appid);
		} catch (AppException $e) {
			
		}
	}
} else {
	$stats = buildYourStats($selected);
}

function buildYourStats($selected) {
	global $apps;
	global $PAGE;
	global $APP_REAL_PATH;
	
	ob_start();

	foreach ($apps as $app)
		if ($app->getId() == $selected) $selectedapp = $app;
		
	echo "All times clicks received: ".$selectedapp->getLocalReceived()." ";
	echo "&nbsp;&nbsp;&nbsp;All times clicks donated: ".$selectedapp->getLocalDonated()."<br /><br />";
	
	$history = AppHistory::getAppHistory($selected);

	if (!empty($history["queriesDonated"])) {
		echo "<h1>In the last 24 hours this application displayed ads for the following apps:</h1>";
		if (count($history["queriesDonated"]) > 1) echo "<fb:swf width=\"600\" height=\"300\" swfsrc=\"".$APP_REAL_PATH."charts/FCF_Column3D.swf\" flashVars=\"&dataURL=".$APP_REAL_PATH."charts/appdonatedstats-".$selected.".xml&chartWidth=600&chartHeight=300\" />";
		echo "<table><tr><th colspan=\"2\">Application</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr>";
		$totalimpressions = 0;
		$totalclicks = 0;
		foreach ($history["queriesDonated"] as $appid => $impressions) {
			try {
				$app = App::getApp($appid);
				$clicks = $history["clicksDonated"][$appid];
				$totalimpressions += $impressions;
				$totalclicks += $clicks;
				if ($impressions == 0) $percent = 0; else
				$percent = (floatval($clicks) / floatval($impressions)) * 100.0;
				try {
					echo "<tr><td><img src=\"".AppInfoManager::getIcon($app->getApiKey())."\"/></td><td><a href =\"http://www.facebook.com/apps/application.php?api_key=".$app->getApiKey()."\">".AppInfoManager::getName($app->getApiKey())."</a></td><td>".$impressions."</td><td>".$clicks."</td><td>".substr($percent, 0, 5)." %</td></tr>";
				} catch (AppInfoManagerException $e) {
					// If the app name can't be fetched, just don't display the app
				}
			} catch (AppException $e) {
				// Some apps might have been deleted, no big deal
			}
		}
		if ($impressions == 0) $percent = 0; else
		$percent = (floatval($totalclicks) / floatval($totalimpressions)) * 100.0;
		echo "<tr><td><b>TOTAL</b><td/><td><b>".$totalimpressions."</b></td><td><b>".$totalclicks."</b></td><td><b>".substr($percent, 0, 5)." %</b></td></tr>";
		echo "</table>";
	} else echo "<h1>This application hasn't displayed any ads in the last 24 hours</h1>";
	
	echo "<br/>";
	
	if (!empty($history["queriesReceived"])) {
		echo "<hr/><h1>In the last 24 hours this application's ad was displayed on the following apps:</h1>";
		if (count($history["queriesReceived"]) > 1) echo "<fb:swf width=\"600\" height=\"300\" swfsrc=\"".$APP_REAL_PATH."charts/FCF_Column3D.swf\" flashVars=\"&dataURL=".$APP_REAL_PATH."charts/appreceivedstats-".$selected.".xml&chartWidth=600&chartHeight=300\" />";
		echo "<table><tr><th colspan=\"2\">Application</th><th>Impressions</th><th>Clicks</th><th>CTR</th></tr>";
		$totalimpressions = 0;
		$totalclicks = 0;
		foreach ($history["queriesReceived"] as $appid => $impressions) {
			try {
				$app = App::getApp($appid);
				$clicks = $history["clicksReceived"][$appid];
				$totalimpressions += $impressions;
				$totalclicks += $clicks;
				if ($impressions > 0)
				$percent = (floatval($clicks) / floatval($impressions)) * 100.0;
				else $percent = 0.00;
				try {
					echo "<tr><td><img src=\"".AppInfoManager::getIcon($app->getApiKey())."\"/></td><td><a href =\"http://www.facebook.com/apps/application.php?api_key=".$app->getApiKey()."\">".AppInfoManager::getName($app->getApiKey())."</a></td><td>".$impressions."</td><td>".$clicks."</td><td>".substr($percent, 0, 5)." %</td></tr>";
				} catch (AppInfoManagerException $e) {
					// If the app name can't be fetched, just don't display the app
				}
			} catch (AppException $e) {
				// Some apps might have been deleted, no big deal
			}
		}
		$percent = (floatval($totalclicks) / floatval($totalimpressions)) * 100.0;
		echo "<tr><td><b>TOTAL</b><td/><td><b>".$totalimpressions."</b></td><td><b>".$totalclicks."</b></td><td><b>".substr($percent, 0, 5)." %</b></td></tr>";
		echo "</table>";
	} else echo "<h1>This application's ad wasn't displayed in the last 24 hours</h1>";
	
	echo "<br/>";	
	
	echo "This page was last updated at ".date("H:i:s")." (GMT -6)";
	
	$stats = ob_get_contents();
	try {
		CacheManager::delete("YourStats-".$selected);
	} catch (CacheManagerException $e) {

	}
	try {
		CacheManager::set("YourStats-".$selected, $stats);
	} catch (CacheManagerException $e) {
		echo "OUCH for ".$selected;
	}
	ob_end_clean();
	
	echo $stats;
	
	return $stats;
}

?>