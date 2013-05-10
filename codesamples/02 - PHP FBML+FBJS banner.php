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

// This sample creates a simple page with a banner dynamically loaded 
// and generated from the API using FBJS

$growthid = "18c2d9d8ee469a4ee8d6132ceb8d519fee09f30a"; // Replace with yours

?>
The ad will automagically appear below once the API request is processed with FBJS:<br/>
<br/>
<div id="growthad" style="display: none;">
<img id="adimage" /> <span id="adname" /> - <a href=""><span id="adtext" /></a>
</div>

<script>
// Thanks to Steve Kanter for the setTimeout code snippet below that acts as an "onload" 
// event. Obviously the setTimeout script must be inserted in your page after the HTML
// elements to be populated
setTimeout(function() {
  var ajax = new Ajax();
  ajax.responseType = Ajax.JSON;
      ajax.ondone = function(data) {
		if (data.growth.result == 0 && data.growth.apps.length == 1) {
			document.getElementById('adname').setTextValue(data.growth.apps[0].name);
			document.getElementById('adtext').setTextValue(data.growth.apps[0].text);
	document.getElementById('adtext').getParentNode().setHref(data.growth.apps[0].link);
			document.getElementById('adimage').setSrc(data.growth.apps[0].icon);
			document.getElementById('growthad').setStyle('display', 'block');
		}
      }
  ajax.requireLogin = false;
  ajax.post('http://grow.darumazone.com/serve2.php?growthid=<?php echo $growthid; ?>'
		+ '&format=json');
},1); //1 millisecond, change as necessary
</script>