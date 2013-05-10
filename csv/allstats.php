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

require_once (dirname(__FILE__).'/../includes/app.php');
require_once (dirname(__FILE__).'/../includes/appinfomanager.php');

header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=stats-".date("Y-m-d-H-i").".csv");

$applist = App::getAppList();
$donated = array();
$received = array();
foreach ($applist as $appid => $ownerid) {
	try {	
		$donatedlocal = App::getDonated($appid);
		if ($donatedlocal > 0) {
			$donated[$appid] = $donatedlocal;
			$received[$appid] = App::getReceived($appid);
		}
	} catch (AppException $e) { unset($balance[$appid]);}
}

echo "Application name, active users, clicks donated, clicks received, balance\r\n";

foreach($donated as $appid => $donated) {
	try {
		$app = App::getApp($appid);
		try {
			echo '"'.AppInfoManager::getName($app->getApiKey())."\", ".AppInfoManager::getActiveUsers($app->getApiKey()).", ".$donated.", ".$received[$appid].", ".($donated - $received[$appid])."\r\n";
		} catch (AppInfoManagerException $ex) {
			// Couldn't retrieve name/icon
		}
	} catch (AppException $e) {}
}

?>
