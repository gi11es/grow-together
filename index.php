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

header('Content-Type: text/html; charset=utf-8');

$start_time = microtime(true);

require_once (dirname(__FILE__).'/../client/facebook.php');
require_once (dirname(__FILE__).'/includes/constants.php');
require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');
require_once (dirname(__FILE__).'/includes/analytics.php');
require_once (dirname(__FILE__).'/includes/uihelper.php');
require_once (dirname(__FILE__).'/includes/cachemanager.php');
require_once (dirname(__FILE__).'/settings.php');

include $TEMPLATE["GROW_STYLE"];

$facebook = new Facebook($api_key, $secret);
$facebook->require_frame();
$userid = $facebook->get_loggedin_user();
if ($userid)
	$user = User::getUser($userid, $facebook->api_client);
	
echo UIHelper::RenderMenu($PAGE_CODE['STATS'], $userid);

if (isset($BANNED[$userid]) && $BANNED[$userid]) {
	echo "<fb:error message=\"You are not authorized to use this application. Sorry.\" />";
	echo UIHelper::RenderDiscussion("Discuss why you're not authorized", $PAGE['YOUR_APPS'], $userid);
	exit(0);
}

try {
	$statdata = CacheManager::get("Stats");
	echo $statdata;
} catch (CacheManagerException $e) {
	include (dirname(__FILE__).'/buildstats.php');
	echo $stats;
}

echo UIHelper::RenderDiscussion("Discuss the stats", $PAGE['STATS'], $userid);

?>