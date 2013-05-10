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

require_once (dirname(__FILE__).'/includes/constants.php');
require_once (dirname(__FILE__).'/includes/uihelper.php');

$codelist = array();

$filelist = scandir((dirname(__FILE__).'/codesamples/'));
foreach($filelist as $filename) {
	if (!is_dir((dirname(__FILE__).'/codesamples/').$filename)) {
		$codelist[]= array("title" => substr($filename, 0, strlen($filename) - 4),
		 	"contents" => file_get_contents((dirname(__FILE__).'/codesamples/').$filename),
			"fullpath" => (dirname(__FILE__).'/codesamples/').$filename
		 );
	}
}
	
if (isset($_REQUEST["selectvalue"])) {
	$selected = $_REQUEST["selectvalue"];
} else {
	$selected = 0;
}

echo RenderCodeSelection();

function RenderCodeSelection() {
global $PAGE;
global $selected;
global $codelist;

$result = "<form method=\"POST\" id=\"selectform\" action='".$PAGE["CODE_SAMPLES"]."'><input type=\"hidden\" id=\"selectvalue\" name=\"selectvalue\" value=\"0\"/>Select a sample: <SELECT id=\"CodeSelection\" onchange=\"document.getElementById('selectvalue').setValue(document.getElementById('CodeSelection').getValue()); document.getElementById('selectform').submit();\">";
foreach ($codelist as $id => $contents) {
	$result .= "<OPTION ".(strcmp($selected, $id) == 0?"selected":"")." VALUE=\"".$id."\">".$contents["title"]."</OPTION>";
}
$result .= "</SELECT></form>";
return $result;
}

?>
<br />
  <div style="clear: both;"/>
<?
	if (strcmp(substr($codelist[$selected]["fullpath"], -4), ".php") == 0) {
?>
<br/>
<h1><u>This code sample in action</u></h1>
<br/>
<?php
	include($codelist[$selected]["fullpath"]); ?>
	<br/>
<?php
	}
?>
	<h1><u>Source code</u></h1>
	<br />
	<?php echo highlight_string($codelist[$selected]["contents"], true);
?>
<br/>
<br/>
<?php echo UIHelper::RenderDiscussion("Discuss this code sample", $PAGE['CODE_SAMPLES']."?selectvalue=".$selected, $userid, $selected); ?>
</div>