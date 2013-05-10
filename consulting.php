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

echo UIHelper::RenderMenu($PAGE_CODE['CONSULTING'], $userid);

if (isset($BANNED[$userid]) && $BANNED[$userid]) {
	echo "<fb:error message=\"You are not authorized to use this application. Sorry.\" />";
	echo UIHelper::RenderDiscussion("Discuss why you're not authorized", $PAGE['YOUR_APPS'], $userid);
	exit(0);
}

?>
<br />
  <div style="clear: both;"/>
    <table>
    <tr><td><div  align="justify">I am offering my services as a consultant, in particular regarding facebook applications development. I am also available
    for other services, including photography, retouching, French localization, as well as offline and online software development in a wide range of programming languages.<br/><br/>To request my services or a quote just send an email to <b>consulting@kouiskas.com</b> or call me on <b>+33630924903</b> between 8am and 5pm GMT.
	</div><td><td><img src="http://grow.darumazone.com/gilles.png"/></td></tr>
 	<table>
    <br/><br/>
    <h1><u>Facebook services</u></h1>
    <div  align="justify">
    <ul>
    <li><b>Full application development</b>: You name it, I'll make it happen. I can help in the design process to give the best virality or facebook appeal to your ideas.</li>
    <li><b>Features extension</b>: I can add new features to an existing application to bring it to the next level.</li>
    <li><b>Scaling</b>: Helping you improve your application's code for it to cope with overnight growth. If you are already in trouble with your app being too slow on your current server I can also help you optimize it.</li>
    <li><b>Bugfixing</b>: Your app doesn't work properly and you don't know how to fix it? I can investigate the issue and repair it.</li>
    <li><b>Project management</b>: Do you already have programmers working for you on facebook applications but you are not satisfied? I can help by giving them direction, teaching them the knowledge gaps they might have and taking the burden of communicating with them to get your ideas done from you.</li>
    </ul>
    </div>
    
    <h1><u>Facebook experience</u></h1>
    <br/>
    I have fully developed two facebook applications, the one you are using right now, along with <a href="http://www.facebook.com/apps/application.php?api_key=3ae82599f92f956d2f9f5f4539086cc5">iBorrow</a>.<br/>
	The source code of both can be accessed on the following CVS repository:<br/>
	<br/>
	<u>CVS server</u>: darumazone.com<br/>
	<u>username</u>: anonymous</br>
	no password</br>
	<u>repository path</u>: /var/lib/cvsroot<br/>
	<br/>
	<div  align="justify">
	I've had the chance to bugfix and extend other facebook applications that were commissioned and the quality of the code I've seen was generally low.
	The above source code should be a proof of the quality I can produce and it would let any programming expert quickly judge my strengths.
	I will continue to improve and document both projects further, so you can expect the quality of this particular code to improve over time.
 	</div>
 	<br/>
 	As a consultant I have bugfixed, extended or optimized these applications:<br/>
 	<a href="http://www.facebook.com/apps/application.php?id=5009963529">My Crew</a><br/>
 	<a href="http://www.facebook.com/apps/application.php?id=4125119719">Nominate Friends</a><br/>
 	<a href="http://www.facebook.com/apps/application.php?id=2399143420">My Weather</a> (full remake in progress)<br/>
 	<a href="http://www.facebook.com/apps/application.php?id=5292458340">Comedian Quotes</a><br/>
 	<a href="http://www.facebook.com/apps/application.php?api_key=1129aaa8640adb6ccfa2c97fafa0257e">My BoxOFun</a><br/>
 	<br/>
     <h1><u>Programming and multimedia experience</u></h1>
    <br/>
    <div  align="justify">
    I've been programming for 9 years, professionally for 5. Former employers include Sun Microsystems and OpenSoft. My last employer was <a href="http://www.smsc.com.au">SMS Central</a>.
 	</div>
    <br/>
    <div  align="justify">
    I've been a VJ, worked in a post office, a bank, been paid to count cars in traffic, worked in a sweatshop (very briefly...), freelanced as a translator and a programmer, been a photographer for weddings and a magazine, created and ran my own premium SMS/MMS service, directed a music video and made a couple of 3D short films.<br/>
 	</div>
    <br/>
	You can see my <a href="http://www.kouiskas.com/cv/">full CV here</a>.<br/>
	<br/>
</div>

<?php

if ($userid)
	echo UIHelper::RenderDiscussion("Discuss my consulting services", $PAGE['CONSULTING'], $userid);

echo Analytics::Page("consulting.html");

?>