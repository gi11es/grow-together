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

require_once (dirname(__FILE__).'/../includes/constants.php');

?>

<script><!--

function textCounter(field, maxlimit) {
field.setValue(field.getValue().replace(/\r|\n|\r\n/g, ''));
if (field.getValue().length > maxlimit )
field.setValue(field.getValue().substring(0, maxlimit));
}

function do_ajax(pagetogo, divtowrite, divtohide) {
  document.getElementById('loader').setStyle('visibility', 'visible');
  var ajax = new Ajax();
  ajax.responseType = Ajax.FBML;
      ajax.ondone = function(data) {
        document.getElementById(divtowrite).setInnerFBML(data);
        document.getElementById(divtowrite).setStyle('display', 'block');
		document.getElementById('loader').setStyle('visibility', 'hidden');
		if (divtohide != null) {
			document.getElementById(divtohide).setStyle('display', 'none');
		}
      }
  ajax.requireLogin = true;
  ajax.post(pagetogo);
}

function update_date() {
	document.getElementById('csvdate').setAction('<?=$PAGE['CSV_APPSTATS']?> ."?startday=' + document.getElementById('publish_day_start').getValue() + '&startmonth=' + document.getElementById('publish_month_start').getValue() + '&startyear=' + document.getElementById('publish_year_start').getValue() + '&endday=' + document.getElementById('publish_day_end').getValue() + '&endmonth' + document.getElementById('publish_month_end').getValue() + '&endyear=' + document.getElementById('publish_year_end').getValue());
	document.getElementById('csvdate').submit();
}

function do_hide(divtohide) {
  document.getElementById(divtohide).setStyle('display', 'none');
document.getElementById(divtohide).setStyle('visibility', 'hidden');
}
//--></script>

<style>
.titletable th { padding:5px 5px 0px 5px; }
.titletable { width: 100%;}

.statstable {
	border-width: 1px 1px 1px 1px;
	border-spacing: 0px;
	border-style: none none none none;
	border-color: gray gray gray gray;
	border-collapse: collapse;
}
.statstable th {
	border-width: 1px 1px 1px 1px;
	padding: 1px 1px 1px 1px;
	border-style: dotted dotted dotted dotted;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}
.statstable td {
	border-width: 1px 1px 1px 1px;
	padding: 1px 1px 1px 1px;
	border-style: dotted dotted dotted dotted;
	border-color: gray gray gray gray;
	background-color: white;
	-moz-border-radius: 0px 0px 0px 0px;
}

</style>