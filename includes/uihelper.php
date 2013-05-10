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

require_once (dirname(__FILE__).'/constants.php');
require_once (dirname(__FILE__).'/../settings.php');
require_once (dirname(__FILE__).'/user.php');

class UIHelper {
	public static function RenderDiscussion($title, $page, $userid, $uniqueid="") {
		global $ADMINS;
		
		$id = ereg_replace("[^A-Za-z0-9]", "_", $title);
		
		$result = "<fb:comments xid=\"".$id.$uniqueid."\" canpost=\"true\" candelete=\"".((isset($ADMINS[$userid]) && $ADMINS[$userid])?"true":"false")."\" returnurl=\"".$page."\">";
		$result .= "<fb:title>".$title."</fb:title>";
		$result .= "</fb:comments>";
		
		return $result;
	}
	
	public static function RenderMenu($currentpage, $userid=null, $selectvalue=null) {
		global $ADMINS;
		global $PAGE;
		global $PAGE_CODE;
		global $APP_PATH;
		global $APP_REAL_PATH;

		$result = "<div style=\"padding: 10px;\">";
		//$result .= "<fb:success message=\"Grow together is migrating to a new server. Given the complication of the procedure the stats concerning the 27th and the 28th of February will be all wrong (the servers are in different timezones). Only the stats are affected, the rest of GT should be working fine.\" />";
		$result .= "<table class=\"titletable\" cellspacing=\"0\" border=\"0\"><tr><th><a href=\"".$APP_PATH."\"><img src=\"".$APP_REAL_PATH."logo_small.png\"></a> </th><th>".UIHelper::RandomQuote()."</th><th><div id=\"loader\" style=\"visibility: hidden;\"><img src=\"".$APP_REAL_PATH."ajax-loader.gif\"/></div></th></tr></table>\r\n";
		$result .= "<br/> <fb:tabs><fb:tab-item href='".$PAGE['YOUR_APPS'].(isset($selectvalue)?"?selectvalue=".$selectvalue:"")."' title=\"Your apps\" ".($currentpage == $PAGE_CODE['YOUR_APPS']?"selected='true'":"")." />\r\n";
		$result .= "<fb:tab-item href='".$PAGE['YOUR_STATS'].(isset($selectvalue)?"?selectvalue=".$selectvalue:"")."' title=\"Your stats\" ".($currentpage == $PAGE_CODE['YOUR_STATS']?"selected='true'":"")." />\r\n";
		/*$result .= "<fb:tab-item href='".$PAGE['BANNER']."' title=\"Banner code\" ".($currentpage == $PAGE_CODE['BANNER']?"selected='true'":""). "/>\r\n";*/
		$result .= "<fb:tab-item href='".$PAGE['API']."' title=\"Growth API\" ".($currentpage == $PAGE_CODE['API']?"selected='true'":""). "/>\r\n";
		$result .= "<fb:tab-item href='".$PAGE['CODE_SAMPLES']."' title=\"Code samples\" ".($currentpage == $PAGE_CODE['CODE_SAMPLES']?"selected='true'":""). "/>\r\n";
		$result .= "<fb:tab-item href='".$PAGE['STATS']."' title=\"Stats\" ".($currentpage == $PAGE_CODE['STATS']?"selected='true'":""). "/>\r\n";
		$result .= "<fb:tab-item href='".$PAGE['CONSULTING']."' title=\"Consulting\" ".($currentpage == $PAGE_CODE['CONSULTING']?"selected='true'":""). "/>\r\n";
		if ($userid != null && isset($ADMINS[$userid]) && $ADMINS[$userid])
		$result .= "<fb:tab-item href='".$PAGE['ADMIN']."' title=\"Admin\" ".($currentpage == $PAGE_CODE['ADMIN']?"selected='true'":""). "/>\r\n";
		$result .= "</fb:tabs>";
		return $result;
	}
	
	public static function RandomQuote() {
		srand(time());
		$random = (rand()%9);
		
		switch ($random) {
			case 0:
				$result = "The strongest principle of growth lies in human choice.<br/><i>George Eliot (1819 - 1880)</i>";
				break;
			case 1:
				$result = "The foolish man seeks happiness in the distance, the wise grows it under his feet.<br/><i>James Oppenheim</i>";
				break;
			case 2:
				$result = "Be not afraid of growing slowly, be afraid only of standing still.<br/><i>Chinese proverb</i>";
				break;
			case 3:
				$result = "All growth is a leap in the dark, a spontaneous unpremeditated act without the benefit of experience.<br/><i>Henry Miller (1891 - 1980)</i>";
				break;
			case 4:
				$result = "Small communities grow great through harmony, great ones fall to pieces through discord.<br/><i>Sallust (86 BC - 34 BC)</i>";
				break;
			case 5:
				$result = "Small communities grow great through harmony, great ones fall to pieces through discord.<br/><i>Sallust (86 BC - 34 BC)</i>";
				break;
			case 6:
				$result = "There are no great limits to growth because there are no limits of human intelligence, imagination, and wonder.<br/><i>Ronald Reagan (1911 - 2004)</i>";
				break;
			case 7:
				$result = "Growth demands a temporary surrender of security.<br/><i>Gail Sheehy</i>";
				break;
			case 8:
				$result = "We grow because we struggle, we learn and overcome.<br/><i>R. C. Allen</i>";
				break;
		}
		return $result;
	}
}

?>