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

require_once (dirname(__FILE__).'/../includes/constants.php');
require_once (dirname(__FILE__).'/../includes/app.php');
require_once (dirname(__FILE__).'/../includes/appinfomanager.php');
require_once (dirname(__FILE__).'/../includes/apphistory.php');

$appname = array();

if (!isset($_REQUEST["startday"]) || !is_numeric($_REQUEST["startday"]) 
		|| !isset($_REQUEST["startmonth"]) || !is_numeric($_REQUEST["startmonth"]) 
		|| !isset($_REQUEST["startyear"]) || !is_numeric($_REQUEST["startyear"]) 
		|| !isset($_REQUEST["endday"]) || !is_numeric($_REQUEST["endday"]) 
		|| !isset($_REQUEST["endmonth"]) || !is_numeric($_REQUEST["endmonth"]) 
		|| !isset($_REQUEST["endyear"]) || !is_numeric($_REQUEST["endyear"])
		|| !isset($_REQUEST["appid"]))
		{
			echo "<fb:redirect url=\"".$PAGE["CSV"]."\" />";
			exit(0);
		}
		
$startday = $_REQUEST["startday"];
$startmonth = $_REQUEST["startmonth"];
$startyear = $_REQUEST["startyear"];
$start = strtotime($startyear."-".$startmonth."-".$startday);
$endday = $_REQUEST["endday"];
$endmonth = $_REQUEST["endmonth"];
$endyear = $_REQUEST["endyear"];
$end = strtotime($endyear."-".$endmonth."-".$endday);
$appid = $_REQUEST["appid"];

if (!$debug) {
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=stats-".$appid."-".date("Y-m-d", $start)."-".date("Y-m-d", $end).".csv");
}

$history = AppHistory::getAppDetailedHistory($appid, $start, $end);

echo "Time slot (full hour starting at the time below), Application donating, Application receiving, Ads displayed, Ads clicked\r\n";

foreach ($history as $date =>$v) {
	foreach($v as $donor => $w) {
		foreach ($w as $receiver => $x) {
			if (!isset($appname[$donor]))
				$donorname = getName($donor);
			else
				$donorname = $appname[$donor];
				
			if (!isset($appname[$receiver]))
				$receivername = getName($receiver);
			else
				$receivername = $appname[$receiver];
			
			echo $date.', '.$donorname.', '.$receivername.', '.$x["queries"].', '.$x["clicks"]."\r\n";
		}
	}
}

function getName($appid) {
	global $appname;
	
	try {
		$app = App::getApp($appid);
		$appname[$appid] = str_replace(",", "", AppInfoManager::getName($app->getApiKey()));
	} catch (Exception $e) {
		$appname[$appid] = "N/A";
	}
	
	return $appname[$appid];
}

?>

