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

error_reporting (E_ALL);

$start = microtime(true);

require_once (dirname(__FILE__) . '/includes/app.php');
require_once (dirname(__FILE__) . '/includes/appscore.php');
require_once (dirname(__FILE__) . '/includes/apphistory.php');
require_once (dirname(__FILE__) . '/includes/appinfomanager.php');
require_once (dirname(__FILE__) . '/includes/token.php');

function weightRandom($weights) {

	/*******************************\
	|   Copyright 2006 Erik Eloff   |
	| This code is free to use only |
	| if you leave this notice here |
	\*******************************/

	// Thank you Mr Eloff, I had to clean up and improve your code, though

	$total = 0;

	// Added the following to handle negative numbers
	$minimum = 0;
	foreach ($weights as $val)
		if ($val < $minimum)
			$minimum = $val;

	foreach ($weights as $val)
		$total += ($val - $minimum);

	if ($total == 0)
		$total = 1;
	$randNum = rand(0, $total -1);
	$total = 0;

	foreach ($weights as $key => $val) {
		$total += ($val - $minimum);
		if (($val - $minimum) > 0 && $total >= $randNum)
			return $key;
	}
}

function array_to_xml($aArr, $sName = "", & $item = null, & $dom = null, & $parent = null) {

	if (strcmp($sName, "apps") == 0)
		$name = "app";
	else
		$name = $sName;

	if ($dom == null) {
		$dom2 = new DOMDocument('1.0');
		array_to_xml($aArr, $name, $item, $dom2);
		return $dom2->saveXML();
	}

	$first = true;
	// Loop thru each element
	foreach ($aArr as $key => $val) {
		if (strcmp($key, "apps") == 0)
			$key = "app";

		if (is_array($val)) {

			if (is_numeric($key)) {

				if ($first) {
					array_to_xml($val, $name, $item, $dom, $parent);
					$first = false;
				} else {
					$sub = $dom->createElement($name);
					$parent->appendChild($sub);
					array_to_xml($val, $name, $sub, $dom, $parent);
				}

			} else {
				$sub = $dom->createElement($key);
				if (is_null($item)) {
					$dom->appendChild($sub);
					array_to_xml($val, $key, $sub, $dom, $sub);
				} else {
					$item->appendChild($sub);
					array_to_xml($val, $key, $sub, $dom, $item);
				}

			}
		} else {
			// Add this item
			$sub = $dom->createElement($key, $val);
			$item->appendChild($sub);
		}
	}

	if ($item != null)
		return $item;
	else
		return $dom;
}

function addApp($app) {
	$result = array ();

	try {
		$link = createLink($app);
		$result = array (
		"name" => AppInfoManager :: getName($app->getApiKey()), "icon" => AppInfoManager :: getIcon($app->getApiKey()), "text" => $app->getText(), "link" => $link);
		return $result;
	} catch (TokenException $e) {

	}
}

function createLink($app /*, $userid*/
) {
	global $PAGE;
	global $requestappid;

	return $PAGE["LINK"] . "?token=" . Token :: createToken($requestappid, $app->getId() /*, $userid*/
	);
}

function outputAndExit($result) {
	global $xml;

	if ($xml) {
		header("Content-Type: application/xml");
		echo array_to_xml($result);
		//print_r($result);
	} else {
		echo html_entity_decode(json_encode($result), ENT_QUOTES);
	}

	exit (0);
}

$xml = true; // XML by default

if (!isset ($_REQUEST["format"])) {
	$result = array (
		"growth" => array (
			"result" => 1200,
			"errormessage" => "format parameter missing"
		)
	);
	outputAndExit($result);
}

$format = strtolower($_REQUEST["format"]);

if (strcmp($format, "xml") != 0 && strcmp($format, "json") != 0) {
	$result = array (
		"growth" => array (
			"result" => 1205,
			"errormessage" => "format parameter must be either \"xml\" or \"json\""
		)
	);
	outputAndExit($result);
}

$xml = (strcmp($format, "xml") == 0);

if (!isset ($_REQUEST["growthid"])) {
	$result = array (
		"growth" => array (
			"result" => 1201,
			"errormessage" => "growthid parameter missing"
		)
	);
	outputAndExit($result);
}

$requestappid = $_REQUEST["growthid"];

$quantity = (isset ($_REQUEST["quantity"]) ? $_REQUEST["quantity"] : 1);

if (!is_numeric($quantity) || $quantity < 1) {
	$result = array (
		"growth" => array (
			"result" => 1203,
			"errormessage" => "quantity must be a positive number"
		)
	);
	outputAndExit($result);
}

try {
	$app = App :: getApp($requestappid);
} catch (Exception $e) {
	$result = array (
		"growth" => array (
			"result" => 1204,
			"errormessage" => "invalid growthid"
		)
	);
	outputAndExit($result);
}

$result = array ();
$result["growth"]["result"] = 0;

$scores = AppScore::getScores();

if (count($scores) <= 1) {
	outputAndExit($result);
}

if (isset ($scores[$requestappid])) {
	$mainscore = $scores[$requestappid];
	unset ($scores[$requestappid]); //unset itself
} else $mainscore = 0;

$higherapps = array ();

foreach ($scores as $app_id => $score) {
	if ($score > $mainscore)
		$higherapps[$app_id] = $scores[$app_id];
}

$resultapps = array ();

if (empty ($higherapps)) { // We are number 1, we give to everyone
	$togive = min(count($scores), $quantity);

	asort($scores);

	for ($i = 0; $i < $togive; $i++) {
		if (!empty ($scores)) {
			$randomkey = weightRandom($scores);
			unset ($scores[$randomkey]);
			$resultapps[] = addApp(App :: getApp($randomkey));
			AppHistory :: incrQueries($requestappid, $randomkey);
		}
	}
} else { // We are not number 1, we give to a randomly selected app that has a positive balance
	$togive = min(count($scores), $quantity);
	$positivebalance = array ();

	foreach ($scores as $app_id => $score) {
		if ($score > 0)
			$positivebalance[$app_id] = $score;
	}

	// If there are more apps with a positive balance than what we need to retrieve, we pull only from these
	if (count($positivebalance) >= $togive)
		$scores = $positivebalance;

	// Multiply the values of the elligible apps by their CTR over the last 24 hours
	foreach ($scores as $app_id => $balance) {
		$ctr = AppHistory :: getCTRHistory($requestappid, $app_id);
		if ($ctr < 0.05)
			$ctr = 0.05;
		$scores[$app_id] *= $ctr;
	}

	for ($i = 0; $i < $togive; $i++) {
		if (!empty ($scores)) {
			$randomkey = weightRandom($scores); // Randomness is weighted by click balance
			unset ($scores[$randomkey]);
			$resultapps[] = addApp(App :: getApp($randomkey));
			AppHistory :: incrQueries($requestappid, $randomkey);
		}
	}
}

if (!empty ($resultapps)) {
	$result["growth"]["apps"] = $resultapps;
}

$result["growth"]["processtime"] = (microtime(true) - $start);
outputAndExit($result);
?>