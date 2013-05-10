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

$LOG_LEVEL["TRACE"] = 0;
$LOG_LEVEL["DEBUG"] = 1;
$LOG_LEVEL["INFO"] = 2;
$LOG_LEVEL["ERROR"] = 3;

require_once (dirname(__FILE__).'/../settings.php');

$TEMPLATES_PATH = "templates/";

$PAGE['YOUR_APPS'] = $APP_PATH."yourapps.php";
$PAGE_CODE['YOUR_APPS'] = 0;
$PAGE['API'] = $APP_PATH."api.php";
$PAGE_CODE['API'] = 1;
$PAGE['ADMIN'] = $APP_PATH."admin.php";
$PAGE_CODE['ADMIN'] = 3;
$PAGE['BANNER'] = $APP_PATH."banner.php";
$PAGE_CODE['BANNER'] = 4;
$PAGE['CODE_SAMPLES'] = $APP_PATH."codesamples.php";
$PAGE_CODE['CODE_SAMPLES'] = 5;
$PAGE['STATS'] = $APP_PATH."index.php";
$PAGE_CODE['STATS'] = 6;
$PAGE['YOUR_STATS'] = $APP_PATH."yourstats.php";
$PAGE_CODE['YOUR_STATS'] = 7;
$PAGE['CONSULTING'] = $APP_PATH."consulting.php";
$PAGE_CODE['CONSULTING'] = 8;

$PAGE['APPCONTROL'] = $APP_REAL_PATH."appcontrol.php";
$PAGE['APPSTATSCONTROL'] = $APP_REAL_PATH."appstatscontrol.php";
$PAGE['CSVCONTROL'] = $APP_REAL_PATH."csvcontrol.php";
$PAGE['CODE_SAMPLE_CONTROL'] = $APP_REAL_PATH."codesamplecontrol.php";
$PAGE['CSV_STATS'] = $APP_REAL_PATH."csv/allstats.php";
$PAGE['CSV_APPSTATS'] = $APP_REAL_PATH."csv/appstats.php";

$PAGE['LINK'] = $APP_REAL_PATH."link.php";

$TEMPLATE["GROW_STYLE"] = $TEMPLATES_PATH."grow_style.inc.php";

$TABLE["USER"] = "user";
$TABLE["APP"] = "app";
$TABLE["APP_HISTORY"] = "app_history";

$COLUMN["USER_ID"] = "user_id";
$COLUMN_TYPE["USER_ID"] = "INT";
$COLUMN_TYPE_ATTRIBUTES["USER_ID"] = " NOT NULL";

$COLUMN["STATUS"] = "status";
$COLUMN_TYPE["STATUS"] = "TINYINT";
$COLUMN_TYPE_ATTRIBUTES["STATUS"] = " NOT NULL DEFAULT 0";

$COLUMN["SESSION_KEY"] = "session_key";
$COLUMN_TYPE["SESSION_KEY"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["SESSION_KEY"] = "";

$COLUMN["API_KEY"] = "api_key";
$COLUMN_TYPE["API_KEY"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["API_KEY"] = "";

$COLUMN["APP_ID"] = "app_id";
$COLUMN_TYPE["APP_ID"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["APP_ID"] = "";

$COLUMN["DONATED"] = "donated";
$COLUMN_TYPE["DONATED"] = "INT";
$COLUMN_TYPE_ATTRIBUTES["DONATED"] = " NOT NULL DEFAULT 0";

$COLUMN["RECEIVED"] = "received";
$COLUMN_TYPE["RECEIVED"] = "INT";
$COLUMN_TYPE_ATTRIBUTES["RECEIVED"] = " NOT NULL DEFAULT 0";

$COLUMN["TEXT"] = "text";
$COLUMN_TYPE["TEXT"] = "VARCHAR(100)";
$COLUMN_TYPE_ATTRIBUTES["TEXT"] = "";

$COLUMN["LINK"] = "link";
$COLUMN_TYPE["LINK"] = "VARCHAR(255)";
$COLUMN_TYPE_ATTRIBUTES["LINK"] = "";

$STATUS["DISABLED"] = 0;
$STATUS["ACTIVE"] = 1;

$COLUMN["QUERIES"] = "queries";
$COLUMN_TYPE["QUERIES"] = "INT";
$COLUMN_TYPE_ATTRIBUTES["QUERIES"] = " NOT NULL DEFAULT 0";

$COLUMN["CLICKS"] = "clicks";
$COLUMN_TYPE["CLICKS"] = "INT";
$COLUMN_TYPE_ATTRIBUTES["CLICKS"] = " NOT NULL DEFAULT 0";

$COLUMN["HISTORY_DAY"] = "history_day";
$COLUMN_TYPE["HISTORY_DAY"] = "DATETIME";
$COLUMN_TYPE_ATTRIBUTES["HISTORY_DAY"] = " NOT NULL";

$COLUMN["DONOR"] = "app_id_donor";
$COLUMN_TYPE["DONOR"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["DONOR"] = "";

$COLUMN["RECEIVER"] = "app_id_receiver";
$COLUMN_TYPE["RECEIVER"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["RECEIVER"] = "";

$COLUMN["TRANSFER_TO"] = "transfer_to";
$COLUMN_TYPE["TRANSFER_TO"] = "VARCHAR(80)";
$COLUMN_TYPE_ATTRIBUTES["TRANSFER_TO"] = "";

?>