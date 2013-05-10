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
require_once (dirname(__FILE__).'/includes/analytics.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');
require_once (dirname(__FILE__).'/includes/user.php');
require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/uihelper.php');
require_once (dirname(__FILE__).'/includes/cachemanager.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');
require_once (dirname(__FILE__).'/settings.php');

include $TEMPLATE["GROW_STYLE"];

$facebook = new Facebook($api_key, $secret);
$facebook->require_frame();
$userid = $facebook->require_install();

$user = User::getUser($userid, $facebook->api_client);

$oldkey = $user->getSessionKey();
if ($facebook->api_client->session_key != $oldkey && isset($_REQUEST["fb_sig_expires"]) && $_REQUEST["fb_sig_expires"] == 0) {
	$user->setSessionKey($facebook->api_client->session_key);
}

if (isset($_REQUEST["selectvalue"]))
	echo UIHelper::RenderMenu($PAGE_CODE['YOUR_STATS'], $userid, $_REQUEST["selectvalue"]);
else
	echo UIHelper::RenderMenu($PAGE_CODE['YOUR_STATS'], $userid);

if (isset($BANNED[$userid]) && $BANNED[$userid]) {
	echo "<fb:error message=\"You are not authorized to use this application. Sorry.\" />";
	echo UIHelper::RenderDiscussion("Discuss why you're not authorized", $PAGE['YOUR_STATS'], $userid);
	exit(0);
}

?>
<br />
  
  <div style="clear: both;">
	   <div id="appstatscontrol">
<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog 1.3                                                               |
// +---------------------------------------------------------------------------+
// | date picker                                                               |
// | adapated by webmaster@phpkitchen.com from functions in lib-common.php     |
// |                                                                           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001 by the following authors:                         |
// |                                                                           |
// | Authors: Tony Bibbs, tony@tonybibbs.com                                   |
// |          Jason Whitttenburg, jwhitten@securitygeeks.com               |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

function getMonthFormOptions($selected = '') 
{
    $month_options = '';
    for ($i = 1; $i <= 12; $i++) {
        if ($i < 10) {
            $mval = '0' . $i;
        } else {
            $mval = $i;
        }
        $month_options .= '<option value="' . $mval . '" ';
        if ($i == $selected) {
            $month_options .= 'selected="SELECTED"';
        }
        $month_options .= '>' . $mval . '</option>';
    }
    return $month_options;
}

function getDayFormOptions($selected = '')
{
    $day_options = '';
    for ($i = 1; $i <= 31; $i++) {
        if ($i < 10) {
            $dval = '0' . $i;
        } else {
            $dval = $i;
        }
        $day_options .= '<option value="' . $dval . '" ';
        if ($i == $selected) {
            $day_options .= 'selected="SELECTED"';
        }
        $day_options .= '>' . $dval . '</option>';
    }
    return $day_options;
}

function getYearFormOptions($selected = '')
{
    $year_options = '';
    $cur_year = date('Y',time());
    $start_year = 2007;
    if (!empty($selected)) {
        if ($selected < $cur_year) {
            $start_year = $selected;
        }
    }
    for ($i = $start_year; $i <= $cur_year; $i++) {
        $year_options .= '<option value="' . $i . '" ';
        if ($i == $selected) {
            $year_options .= 'selected="SELECTED"';
        }
        $year_options .= '>' . $i . '</option>';
    }
    return $year_options;
}

function outputDateSelector($controlid, $day='', $month='', $year='')
{
    $html = '';
    $html .= '<select id="publish_day'.$controlid.'">' . getDayFormOptions($day) . '</select> / ';
    $html .= '<select id="publish_month'.$controlid.'">' . getMonthFormOptions($month) . '</select> / ';
    $html .= '<select id="publish_year'.$controlid.'">' . getYearFormOptions($year) . '</select>&nbsp;&nbsp;&nbsp;&nbsp;';
    return $html;
}

$facebook = new Facebook($api_key, $secret);
$userid = $_REQUEST["fb_sig_user"];
$user = User::getUser($_REQUEST["fb_sig_user"], true);

$api_keys = array();
if (!isset($ADMINS[$userid]) || !$ADMINS[$userid]) {
	$apps = $user->getApps();
	foreach ($apps as $app)
	$api_keys[$app->getApiKey()] = $app->getId();
} else {
	$apps = App::getAppList();
	foreach ($apps as $appid => $owner) {
		try {
			$app = App::getApp($appid);
			$api_keys[$app->getApiKey()] = $app->getId();
			$apps[$appid] = $app;
		} catch (AppException $e) { unset ($apps[$appid]); } // If the app was deleted  no need to add it to the list
	}
}
	
if (count($api_keys) > 0) {
	if (isset($_REQUEST["selectvalue"])) {
		$selected = $_REQUEST["selectvalue"];
	} elseif (isset($debugappid)) {
		$selected = $debugappid;
	} else {
		$selected = false;
		foreach ($api_keys as $api_key => $appid)
			if (!$selected) $selected = $appid;
	}
	
	foreach ($apps as $app)
		if ($app->getId() == $selected) $selectedapp = $app;
	
	echo "<form method=\"POST\" onsubmit=\"".$PAGE["YOUR_STATS"]."\" > <img src=\"".AppInfoManager::getIcon($selectedapp->getApiKey())."\"> ".RenderAppSelection($api_keys)."</form>";
	
	$timestamp = mktime();
    $lastweektimestamp = strtotime("-1 week");

    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = date('Y', $timestamp);
    
    $lastweekday = date('d', $lastweektimestamp);
    $lastweekmonth = date('m', $lastweektimestamp);
    $lastweekyear = date('Y', $lastweektimestamp);

	echo "<form id=\"csvdate\" method=\"POST\" action=\"".$PAGE["CSV_APPSTATS"]."\">";
	echo "<br/>You can download this app's stats in CSV format. The timezone used in the CSV is GMT+10, which corresponds to the server. The oldest stats you can get are from November 1st.";
    echo "<br/></br>Start date: ".outputDateSelector("_start", $lastweekday, $lastweekmonth, $lastweekyear)." End date: ".outputDateSelector("_end", $day, $month, $year);
    echo "<input value=\"Download CSV\" type=\"button\" class=\"inputbutton\" onclick=\"document.getElementById('csvdate').setAction('".$PAGE['CSV_APPSTATS']."?appid=".$selected."&startday=' + document.getElementById('publish_day_start').getValue() + '&startmonth=' + document.getElementById('publish_month_start').getValue() + '&startyear=' + document.getElementById('publish_year_start').getValue() + '&endday=' + document.getElementById('publish_day_end').getValue() + '&endmonth=' + document.getElementById('publish_month_end').getValue() + '&endyear=' + document.getElementById('publish_year_end').getValue()); document.getElementById('csvdate').submit();\"/>";
	echo "</form>";
	echo "<hr/>";
	
	try {
		$statdata = CacheManager::get("YourStats-".$selected);
		echo $statdata;
	} catch (CacheManagerException $e) {
		include (dirname(__FILE__).'/buildyourstats.php');
		echo $stats;
	}

}

function RenderAppSelection($applist) {
global $PAGE;
global $selected;

$result = "<SELECT id=\"AppSelection\" onchange=\"document.setLocation('".$PAGE["YOUR_STATS"]."?selectvalue=' + document.getElementById('AppSelection').getValue());\">";
foreach ($applist as $api_key => $appid) {
	try {
		$name = AppInfoManager::getName($api_key);
		$result .= "<OPTION ".(strcmp($selected, $appid) == 0?"selected":"")." VALUE=\"".urlencode(htmlentities($appid))."\">".$name."</OPTION>";
	} catch (AppInfoManagerException $e) {
		// If the app name can't be fetched, just don't display the app
	}
}
$result .= "</SELECT>";
return $result;
}

?>
	   </div>
</div>

<?php

echo Analytics::Page("yourstats.html?userid=".$userid);

?>