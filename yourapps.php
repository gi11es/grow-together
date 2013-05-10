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

//error_reporting (E_ALL);

header('Content-Type: text/html; charset=utf-8');

$start_time = microtime(true);

require_once (dirname(__FILE__).'/../client/facebook.php');
require_once (dirname(__FILE__).'/settings.php');
require_once (dirname(__FILE__).'/includes/constants.php');
require_once (dirname(__FILE__).'/includes/analytics.php');
require_once (dirname(__FILE__).'/includes/appinfomanager.php');
require_once (dirname(__FILE__).'/includes/user.php');
require_once (dirname(__FILE__).'/includes/app.php');
require_once (dirname(__FILE__).'/includes/uihelper.php');
require_once (dirname(__FILE__).'/includes/apphistory.php');

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
	echo UIHelper::RenderMenu($PAGE_CODE['YOUR_APPS'], $userid, $_REQUEST["selectvalue"]);
else
	echo UIHelper::RenderMenu($PAGE_CODE['YOUR_APPS'], $userid);

if (isset($BANNED[$userid]) && $BANNED[$userid]) {
	echo "<fb:error message=\"You are not authorized to use this application. Sorry.\" />";
	echo UIHelper::RenderDiscussion("Discuss why you're not authorized", $PAGE['YOUR_APPS'], $userid);
	exit(0);
}

if (isset($_REQUEST["delete"])) {
	$app = App::getApp($_REQUEST["delete"]);
	$application_name = AppInfoManager::getName($app->getApiKey());
	$app->delete();
	echo "<fb:success message=\"".$application_name." successfully removed\" />";
}

if (isset($_REQUEST["refreshicon"])) {
	$app = App::getApp($_REQUEST["refreshicon"]);
	AppInfoManager::getIcon($app->getApiKey(), true);
	$application_name = AppInfoManager::getName($app->getApiKey(), true);

	echo "<fb:success message=\"".$application_name."'s icon and name refreshed successfully\" />";
}

if (isset($_REQUEST["app_api_key"])) {
	try {
		$application_name = AppInfoManager::getName($_REQUEST["app_api_key"]);
		try {
			$app = App::createApp($_REQUEST["app_api_key"], $userid);
			$app->publishActionNewApp($facebook->api_client);
			echo "<fb:success message=\"".$application_name." successfully added\" />";
		} catch (Exception $e) {
			echo "<fb:error message=\"".$application_name." is already in your list\" />";
		}
	} catch (AppInfoManagerException $e) {
		echo "<fb:error message=\"Couldn't find that api_key. Make sure that the app is not in developer mode.\" />";
	}
}

?>
<br />
  
  <div style="clear: both;">
	   <div id="appcontrol">
<?php

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
		} catch (AppException $e) { unset($apps[$appid]); }
	}
}
	
if (count($api_keys) > 0) {
	if (isset($_REQUEST["selectvalue"])) {
		$selected = $_REQUEST["selectvalue"];
		if ($apps[$selected]->getUserId() != $userid && (!isset($ADMINS[$userid]) || !$ADMINS[$userid]))
			$selected = false;
	} else {
		$selected = false;
		foreach ($api_keys as $api_key => $appid)
			if (!$selected) $selected = $appid;
	}
		
	foreach ($apps as $app)
		if ($app->getId() == $selected) $selectedapp = $app;
		
	$updated = false;
	if (isset($_REQUEST["adtext"])) {
		if (strcmp($_REQUEST["adtext"], $selectedapp->getText()) != 0) {
			$selectedapp->setText(trim($_REQUEST["adtext"]));
			$updated = true;
		}
	}

	if (isset($_REQUEST["adlink"])) {
		if (strcmp($_REQUEST["adlink"], $selectedapp->getLink()) != 0) {
			$selectedapp->setLink(trim($_REQUEST["adlink"]));	
			$updated = true;
		}
	}
	
	if (isset($_REQUEST["CreditAppSelection"])) {
		if (strcmp($_REQUEST["CreditAppSelection"], $selectedapp->getTransferTo()) != 0) {
			
			if (strcmp($_REQUEST["CreditAppSelection"], "null") == 0)
				$selectedapp->setTransferTo(null);	
			else
				$selectedapp->setTransferTo(trim($_REQUEST["CreditAppSelection"]));	
			$updated = true;
		}
	} 
	
	if ($updated) echo "<fb:success message=\"".AppInfoManager::getName($selectedapp->getApiKey())." updated successfully\" />";
	
	if (strcmp(trim($selectedapp->getText()), "") == 0 || strcmp(trim($selectedapp->getLink()), "") == 0)
	 echo "<fb:explanation message=\"You must fill the ad text and link before your app is advertised\" />";
	
	echo "<form method=\"POST\" onsubmit=\"".$PAGE["YOUR_APPS"]."\" > <img src=\"".AppInfoManager::getIcon($selectedapp->getApiKey())."\"> ".RenderAppSelection($api_keys);
	
	echo " <input id='delete' type='hidden' name='delete' value='".$selected."' /><input value=\"Remove application\" type=\"submit\" class=\"inputbutton\" /></form><br/>";
	
	echo "<form method=\"POST\" onsubmit=\"".$PAGE["YOUR_APPS"]."\" > ";
	echo " <input id='refreshicon' type='hidden' name='refreshicon' value='".$selected."' /><input value=\"Refresh icon and app name\" type=\"submit\" class=\"inputbutton\" /> (Normally refetched once a day from the app's about page)</form><br/>";

	echo "<h1>Growth id <input readonly ='true' size=40 type='text' value='".$selectedapp->getId()."'/> (<a href=\"".$PAGE["API"]."\">see API for details</a>)</h1> <br />";
	echo "<form id='formupdate' onsubmit=\"document.setLocation('".$PAGE['YOUR_APPS']."?adtext=' + escape(document.getElementById('adtext').getValue()) + '&CreditAppSelection=' + document.getElementById('CreditAppSelection').getValue() + '&adlink=' + escape(document.getElementById('adlink').getValue()) + '&selectvalue=' + document.getElementById('AppSelection').getValue()); return false;\"> <h1>Ad text (100 characters max)<br/> <textarea onKeyDown=\"textCounter(document.getElementById('adtext'), 100);\" onKeyUp=\"textCounter(document.getElementById('adtext'), 100);\" cols='80' rows='1' name='adtext' id='adtext'>".htmlentities(stripslashes($selectedapp->getText()))."</textarea></h1>";
	echo "<h1>Ad link<br/> <textarea onKeyDown=\"textCounter(document.getElementById('adlink'), 255);\" onKeyUp=\"textCounter(document.getElementById('adlink'), 255);\" cols='80' rows='1' name='adlink' id='adlink'>".htmlentities(stripslashes($selectedapp->getLink()))."</textarea></h1>";

	if (count($api_keys) > 1) {
		echo "<h1>Transfer clicks credit to: ";
		echo "<SELECT id=\"CreditAppSelection\">";
		echo "<OPTION ".((strcmp($selectedapp->getTransferTo(), "null") == 0)?"SELECTED":"")." VALUE=\"null\">none</OPTION>\n";
		foreach ($api_keys as $api_key => $appid) {
			try {
				$name = AppInfoManager::getName($api_key);
				echo "<OPTION ".((strcmp($selectedapp->getTransferTo(), $appid) == 0)?"SELECTED":"")." VALUE=\"".urlencode(htmlentities($appid))."\">".$name."</OPTION>\n";
			} catch (AppInfoManagerException $e) {
				// If the app name can't be found, don't show inside selection
			}
		}
		echo "</SELECT> when balance is positive</h1>";
	}

	echo "<br/><input value=\"Update ad parameters\" type=\"submit\" class=\"inputbutton\" /></form>";
	echo "<br/><hr/>";
	
}

function RenderAppSelection($applist) {
global $PAGE;
global $selected;

$result = "<SELECT id=\"AppSelection\" onchange=\"document.setLocation('".$PAGE['YOUR_APPS']."?selectvalue=' + document.getElementById('AppSelection').getValue());\">";
foreach ($applist as $api_key => $appid) {
	try {
		$name = AppInfoManager::getName($api_key);
		$result .= "<OPTION ".(strcmp($selected, $appid) == 0?"selected":"")." VALUE=\"".urlencode(htmlentities($appid))."\">".$name."</OPTION>";
	} catch (AppInfoManagerException $e) {
		// If the app name can't be found, don't show inside selection
	}
}
$result .= "</SELECT>";
return $result;
}

?>
	   </div>
	Enter your application's api_key to add it to the growth community:
  <fb:editor action="<?php echo $PAGE['YOUR_APPS']; ?>" labelwidth="150">
	<fb:editor-text label="Application api_key" name="app_api_key" value=""/>
	<fb:editor-buttonset>
	          <fb:editor-button value="Add application"/>
	</fb:editor-buttonset>
  </fb:editor>
  </div>
</div>

<?php

echo Analytics::Page("yourapps.html?userid=".$userid);

?>