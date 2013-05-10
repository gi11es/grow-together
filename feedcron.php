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

require_once (dirname(__FILE__).'/../client/facebook.php');
require_once (dirname(__FILE__).'/settings.php');
require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');
require_once (dirname(__FILE__).'/includes/user.php');

error_reporting (E_ALL);

$facebook = new Facebook($api_key, $secret);
$allappsids = App::getAppList();

foreach ($allappsids as $appid => $ownerid) if (rand(1, 14) == 1) {
	try {
	$app =  App::getApp($appid);
	$user = User::getUser($ownerid);
	$session_key = $user->getSessionKey();
	$facebook->set_user($ownerid, $session_key);
	$app->publishActionExistingApp($facebook->api_client);
	} catch (Exception $e) {} // Doesn't matter if it fails for one user
}

echo "DONE";

?>
