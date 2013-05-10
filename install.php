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

// DO NOT PLAY WITH THIS STUFF!!! YOU COULD POTENTIALLY LOSE ALL THE DATA FOR THE APP
// Installation script, will drop and create the tables and procedures

require_once (dirname(__FILE__).'/includes/user.php');
require_once (dirname(__FILE__).'/includes/cachemanager.php');
require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');

echo "Flushing the cache...<br>";

CacheManager::flush();

echo "DONE<br><br>";

/*echo "Recreating DB schema for the User class...<br>";

User::recreateDBSchema();

echo "DONE<br><br>";


echo "DONE<br><br>";

echo "Recreating DB schema for the App class...<br>";

App::recreateDBSchema();

echo "DONE<br><br>";


echo "DONE<br><br>";

echo "Recreating DB schema for the AppHistory class...<br>";

AppHistory::recreateDBSchema();

echo "DONE<br><br>";*/

?>