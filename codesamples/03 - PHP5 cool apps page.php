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

require_once (dirname(__FILE__).'/../includes/urlmanager.php');

$growthid = "18c2d9d8ee469a4ee8d6132ceb8d519fee09f30a"; // Replace with yours
$quantity = 3;

// This method is specific to my toolkit, it's a wrapper on curl 
// that retrieves the content of a URL. It's available on the CVS
// in case you don't know how to make your own equivalent
$xmlresult = URLManager::getURL("http://grow.darumazone.com/serve2.php?growthid=$growthid"
									."&quantity=$quantity&format=xml");

$xml = new SimpleXMLElement($xmlresult);

$result = "<table> <tr><th></th><th>App name</th><th>Description</th></tr>";
if (isset($xml->result) && $xml->result == 0 && !empty($xml->app)) {
	foreach ($xml->app as $app) {
		$result .= "<tr><td><a href='".$app->link."'><img src='".$app->icon."'/></a></td>";
		$result .= "<td><a href='".$app->link."'>".$app->name."</a></td>";
		$result .= "<td>".$app->text."</td></tr>";
	}
}
$result .= "</table>";

?>

The content below was fetched at page-render time by PHP<br />
<br />
<?php echo $result; ?>