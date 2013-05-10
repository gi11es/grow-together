<?php

$APP_PATH = "http://apps.facebook.com/growtogether/";
$APP_REAL_PATH = "http://grow.darumazone.com/";

require_once (dirname(__FILE__).'/includes/constants.php');

$api_key = 'c8f3f4b796166594516dfc8d3496db27';
$secret  = 'f2e8115e6a95e3cf3fe381af7e8e3b88';

$APP_ABOUT_PAGE = "http://www.facebook.com/apps/application.php?api_key=c8f3f4b796166594516dfc8d3496db27";
$APP_ADD_PAGE = "http://www.facebook.com/add.php?api_key=c8f3f4b796166594516dfc8d3496db27";

$ADMINS = array(500347728 => true);
$BANNED = array(3321677 => true, 1056292217 => true);

$MEMCACHE["HOST"] = 'localhost';
$MEMCACHE["PORT"] = 11211;
$MEMCACHE["PREFIX"] = 'Grow-';

$CURRENT_LOG_LEVEL = $LOG_LEVEL["INFO"];
$LOG_TIME_FORMAT = "Y-m-d H:i:s";

$LOG_FILE_PATH = "/home/daruma/logs/";
$LOG_FILE["CacheManager"] = $LOG_FILE_PATH."CacheManager-".date("Y-m-d").".log";
$LOG_FILE["DBManager"] = $LOG_FILE_PATH."DBManager-".date("Y-m-d").".log";
$LOG_FILE["URLManager"] = $LOG_FILE_PATH."URLManager-".date("Y-m-d").".log";
$LOG_FILE["AppInfoManager"] = $LOG_FILE_PATH."AppInfoManager-".date("Y-m-d").".log";
$LOG_FILE["User"] = $LOG_FILE_PATH."User-".date("Y-m-d").".log";
$LOG_FILE["App"] = $LOG_FILE_PATH."App-".date("Y-m-d").".log";
$LOG_FILE["AppHistory"] = $LOG_FILE_PATH."AppHistory-".date("Y-m-d").".log";
$LOG_FILE["Token"] = $LOG_FILE_PATH."Token-".date("Y-m-d").".log";

$DATABASE_WRITE["HOST"] = "localhost";
$DATABASE_WRITE["USER"] = "grow";
$DATABASE_WRITE["PASSWORD"] = "al7p6aga13";
$DATABASE_WRITE["NAME"] = "grow";
$DATABASE_WRITE["PREFIX"] = "grow_";

$DATABASE_READ["HOST"] = "localhost";
$DATABASE_READ["USER"] = "grow";
$DATABASE_READ["PASSWORD"] = "al7p6aga13";
$DATABASE_READ["NAME"] = "grow";
$DATABASE_READ["PREFIX"] = "grow_";

$APPINFOMANAGER["USER"] = "rob@dubuc.fr";
$APPINFOMANAGER["PASSWORD"] = "cacaprout";

$INSTALL_URL = "http://www.facebook.com/apps/application.php?api_key=".$api_key;

?>