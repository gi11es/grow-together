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

$applist = App::getAppList();
$balance = array();
foreach ($applist as $appid => $ownerid) {
	try {	
		$donated = App::getDonated($appid);
		if ($donated > 0) {
			$balance[$appid] = ($donated - App::getReceived($appid));
		}
	} catch (AppException $e) { unset($balance[$appid]);}
}

arsort($balance);

echo "<graph caption='Click balance' canvasBgColor='FAFAFA' canvasBaseColor='333333' hovercapbgColor='FFECAA' hovercapborder='F47E00' divlinecolor='000000' xAxisName='Application' yAxisName='Balance' showNames='0' showValues='0' decimalPrecision='0' formatNumberScale='0' connectNullData='1'>\r\n";

foreach($balance as $appid => $balance) {
	try {
		$app = App::getApp($appid);
		try {
			echo "<set name=\"".AppInfoManager::getName($app->getApiKey())."\" value='".($balance == 0?0.01:$balance)."' color='EB008B' />\r\n";
		} catch (AppInfoManagerException $ex) {
			// Couldn't retrieve name/icon
		}
	} catch (AppException $e) {}
}

echo "</graph>";

?>
