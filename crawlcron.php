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

// This cron job is ran once a day to retrieve potential changes in app icon/name/active users

require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');

$allappsids = App::getAppList();

foreach ($allappsids as $appid => $ownerid) {
	$app =  App::getApp($appid);
	$apikey = $app->getApiKey();
	AppInfoManager::getIcon($apikey, true);
	AppInfoManager::getName($apikey, true);
	AppInfoManager::getActiveUsers($apikey, true);
	//echo "Done with apikey=".$apikey."<br/>";
}

//echo "DONE";

?>
