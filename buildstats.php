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

error_reporting(E_ALL);

// This is a cron job that builds the stats page

require_once (dirname(__FILE__) . '/../client/facebook.php');
require_once (dirname(__FILE__) . '/includes/constants.php');
require_once (dirname(__FILE__) . '/includes/app.php');
require_once (dirname(__FILE__) . '/includes/appscore.php');
require_once (dirname(__FILE__) . '/includes/apphistory.php');
require_once (dirname(__FILE__) . '/includes/appinfomanager.php');
require_once (dirname(__FILE__) . '/includes/analytics.php');
require_once (dirname(__FILE__) . '/includes/uihelper.php');
require_once (dirname(__FILE__) . '/includes/cachemanager.php');
require_once (dirname(__FILE__) . '/settings.php');

ob_start();

$applist = App :: getAppList();
$balance = array ();
$scores = AppScore::getScores();
$donated = array ();
$received = array ();
$clickcount = 0;

$activeuserscount = 0;
foreach ($applist as $appid => $ownerid) {
	try {
		$local_donated = App :: getDonated($appid);
		if ($local_donated > 0) {
			$app = App :: getApp($appid);
			$activeuserscount += AppInfoManager :: getActiveUsers($app->getApiKey());
			$clickcount += $local_donated;
			$donated[$appid] = $local_donated;
			$received[$appid] = App :: getReceived($appid);
			if ($donated[$appid] > 0) {
				$balance[$appid] = ($donated[$appid] - $received[$appid]);
			}
		}
	} catch (AppException $e) {
		unset ($donated[$appid]);
		unset ($received[$appid]);
		unset ($balance[$appid]);
	}
}

$totalimpressions = AppHistory :: getOverallDonations();

echo "<br/>";
echo "<h1>There are " . count($donated) . " active applications registered  and " . (count($applist) - count($donated)) . " pending*</h1>";
echo "<h1>The active applications cumulate " . $activeuserscount . " daily active users</h1>";
echo "<h1>A total of $clickcount clicks were exchanged</h1>";
$impressionspersec = (floatval($totalimpressions) / 86400.0);
echo "<h1>$totalimpressions ads were displayed in the last 24 hours (average = " . substr($impressionspersec, 0, 4) . " per second)</h1>";
?>
<br/>
<h1><u>All-time statistics of active applications (<a href="<?=$PAGE['CSV_STATS']?>">download in CSV format</a>)</u></h1>
<fb:swf width="600" height="300" swfsrc="<?php echo $APP_REAL_PATH; ?>charts/FCF_Column3D.swf" flashVars="&dataURL=<?php echo $APP_REAL_PATH; ?>charts/allstats.php&chartWidth=600&chartHeight=300" />
<?php


echo "<table><tr><th></th><th>Application</th><th>Active users</th><th>Donated</th><th>Received</th><th>Balance</th><th>Score**</th></tr>";

arsort($scores);

foreach ($scores as $appid => $score) {
	$app = App :: getApp($appid);
	try {
		echo "<tr><td><img src=\"" . AppInfoManager :: getIcon($app->getApiKey()) . "\"/></td><td><a href =\"http://www.facebook.com/apps/application.php?api_key=" . $app->getApiKey() . "\">" . AppInfoManager :: getName($app->getApiKey()) . "</a></td><td>" . AppInfoManager :: getActiveUsers($app->getApiKey()) . "</td><td>" . App :: getDonated($appid) . "</td><td>" . App :: getReceived($appid) . "</td><td>" . $balance[$appid] . "</td><td>" . $score . "</td></tr>";
	} catch (AppInfoManagerException $e) {
		// Couldn't retrieve name/icon
	}
}
echo "</table>";
?>
<br/>
*pending apps were added to Grow Together but have yet to donate their first click<br/>
**The score is a calculation based on the donated/received ratio over the last 24 hours. It affects the priorities of the ad rotation algorithm.<br/>
<br/>
<?php


echo "This page was last updated at " . date("H:i:s") . " (GMT -6)";

$stats = ob_get_contents();
try {
	CacheManager :: delete("Stats");
} catch (CacheManagerException $e) {

}
CacheManager :: set("Stats", $stats);
ob_end_clean();

echo $stats;
?>