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
require_once (dirname(__FILE__).'/includes/uihelper.php');
require_once (dirname(__FILE__).'/settings.php');

include $TEMPLATE["GROW_STYLE"];

$facebook = new Facebook($api_key, $secret);
$facebook->require_frame();
$userid = $facebook->get_loggedin_user();

echo UIHelper::RenderMenu($PAGE_CODE['API'], $userid);

if (isset($BANNED[$userid]) && $BANNED[$userid]) {
	echo "<fb:error message=\"You are not authorized to use this application. Sorry.\" />";
	echo UIHelper::RenderDiscussion("Discuss why you're not authorized", $PAGE['YOUR_APPS'], $userid);
	exit(0);
}

// - <i><b>userid</b></i> -> the facebook user id of the user <b>viewing</b> the ad (not your own userid!)<br/>

?>
<br />
  <div style="clear: both;"/>
  In contrast to the existing money-based facebook advertisement networks, Grow Together offers an API for you to retrieve the contents of the ads and display them in your own way. By doing so you are completely free of how and where you display the ads. You can make a "cools apps" page, a banner, anything you like. Just retrieve the information about the ad from the API. To save you some time there are some <a href="<?php echo $PAGE["CODE_SAMPLES"]; ?>">code samples available</a> for the API.<br/>
<br/>
  <h1><u>The growth API is based on a simple HTTP request</u></h1><br/>
  
	To retrieve the content of advertisements just query <i><b>http://grow.darumazone.com/serve2.php</b></i> with the following HTTP GET or POST parameters (all are mandatory except quantity):<br/>
	- <i><b>growthid</b></i> -> the application's growth id, available on <a href="<?php echo $PAGE['YOUR_APPS']; ?>">your apps page</a><br/>
	- <i><b>format</b></i> -> the only correct values are <b><i>xml</i></b> and <b><i>json</i></b><br/>
	- <i><b>quantity</b></i> -> the amount of ads you want to retrieve (default value = 1)<br/>
	<br/>
	<i>Example request:</i><br/>
	<i>http://grow.darumazone.com/serve2.php?growthid=1234acbd&quantity=3&format=xml</i><br/>
	<br/>
	Results of the requests shouldn't be cached as it would break the click count. If you intend to display banners (as opposed to "cool apps" pages), I highly recommend that you use AJAX to do so, <a href="<?php echo $PAGE['CODE_SAMPLES']; ?>">see the code examples</a>.<br/>
	<br/>
	<h1><u>Example XML data returned by the request</u></h1><br/>
	This is the syntax of the XML information you will receive when performing the above query.<br/>
	<br/>
	&lt;growth&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;result&gt;0&lt;/result&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;app&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;name&gt;iBorrow&lt;/name&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;icon&gt;http://somewhere.facebook.com/somepath/app_2_5009.gif&lt;/icon&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;text&gt;The easiest way to borrow stuff from your friends&lt;/text&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;link&gt;http://grow.darumazone.com/link.php?token=12345abcdef&lt;/link&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;/app&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;app&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;name&gt;My Questions&lt;/name&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;icon&gt;http://somewhere.facebook.com/somepath/app_2_5009.gif&lt;/icon&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;text>Ask questions, get answers&lt;/text&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;link>http://grow.darumazone.com/link.php?token=12345abcdef&lt;/link&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;/app&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;app&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;name&gt;Where I've been&lt;/name&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;icon&gt;http://somewhere.facebook.com/somepath/app_2_5009.gif&lt;/icon&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;text>Like you know, we need more users&lt;/text&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;link>http://grow.darumazone.com/link.php?token=12345abcdef&lt;/link&gt;<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;&lt;app&gt;<br/>
	&nbsp;&nbsp;&lt;processtime&gt;0.200361013412&lt;/processtime&gt;<br/>
	&lt;/growth&gt;<br/>
	<br/>
	<h1><u>Example JSON data returned by the request</u></h1><br/>
	{"growth": {<br/>
	&nbsp;&nbsp;"result": 0,<br/>
	&nbsp;&nbsp;"apps": [<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;{"name": "My Crew",<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;"icon": "http://somewhere.facebook.com/somepath/app_2_5009.gif",<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;"text": "Organize your friends into crews, no need to search for friends anymore, just click on a crew",<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;"link": "http://grow.darumazone.com/link.php?token=e75fe5bc99fa0c6545b92db61111b4a8e831a78c",<br/>
	&nbsp;&nbsp;&nbsp;&nbsp;}<br/>
	&nbsp;&nbsp;],<br/>
	&nbsp;&nbsp;"processtime" : 0.158285140991<br/>
	}}<br/>
	<br/>

    <h1><u>Click balance algorithm</u></h1><br/>
  <div align="justify">I have tried to design an algorithm which naturally rewards the most generous applications. It is based on the click balance (clicks donated minus clicks received).<br />
<br />
A given app will display ads of apps with a greater click balance than itself. This share of page views will be distributed depending on how generous the concerned apps were. The objective being to bring down the apps which are the most generous (gave more clicks than they received) from the top, so that they receive as many clicks as they donated.<br />
<br />
At any given time the top most generous app (the one with the biggest click balance) will give clicks to the apps that have received the least clicks. This is aimed at bringing up all the smallest apps with low traffic.<br />
<br />
I will potentially attempt to tweak the above algorithm once a decent amount of applications have joined, but the objective will always remain to fairly distribute clicks across the applications so that everyone can benefit from the combined forces.<br />
<br />
If you look at the ads displayed within your app and think that there are only big apps that don't need help, remember that each of these ads are there to compensate for the generosity of these apps to the growth community.<br /></div>
<br/>
  <h1><u>Fraud detection</u></h1>
<ul>
  <li>I have set up a protection against leeching to prevent users from only receiving and not giving clicks. So don't think you can freeload, you are expected to advertise other apps to be part of the growth community.</li><br/>
  <li>If you think of fraud detection techniques, please let me know (you can leave a message at the bottom of this page).</li></ul>
<br/>
  <h1><u>When does my app start receiving clicks?</u></h1><br/>
<div align="justify">As soon as your app donates its first click to another app. This is just to make sure that you've correctly deployed the ad display on your own app.</div>
</div>

<?php

//if ($userid)
echo UIHelper::RenderDiscussion("Discuss how it works", $PAGE['HOW_IT_WORKS'], $userid);

echo Analytics::Page("api.html");

?>