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

require_once (dirname(__FILE__) . '/constants.php');
require_once (dirname(__FILE__) . '/dbmanager.php');
require_once (dirname(__FILE__) . '/cachemanager.php');
require_once (dirname(__FILE__) . '/logmanager.php');
require_once (dirname(__FILE__) . '/app.php');
require_once (dirname(__FILE__) . '/apphistory.php');
require_once (dirname(__FILE__) . '/../settings.php');

class AppScore {
	private static $allapps = array ();
	private static $scores = array ();

	public static function getScores($force_update = false) {

		$fromcache = false;
		try {
			AppScore :: $scores = CacheManager :: get("Scores");
			$fromcache = true;
		} catch (CacheManagerException $e) {
		}

		if ($fromcache && isset (AppScore :: $scores["date"]) && AppScore :: $scores["date"] - time() < 60 && !$force_update) {
			// Use the version from the cache
			unset(AppScore :: $scores["date"]);
			return AppScore :: $scores;
		} else {
			// Make a new version and store it in the cache
			if (empty (AppScore :: $allapps))
				AppScore :: $allapps = App :: getAppList();
			foreach (AppScore :: $allapps as $appid => $ownerid) {
				try {

					$donated = App :: getDonated($appid);
					$received = App :: getReceived($appid);
					if ($donated > 0) {
						$balance = ($donated - $received);
						$recent_received = AppHistory :: getReceivedHistory($appid);
						$recent_donated = AppHistory :: getDonatedHistory($appid);
						$recent_balance = $recent_donated - $recent_received;

						if ($recent_balance == 0)
							$recent_sign = 1;
						else
							$recent_sign = $recent_balance / abs($recent_balance);

						if ($balance == 0)
							$sign = 1;
						else
							$sign = $balance / abs($balance);

						if ($recent_received == 0)
							$recent_ratio = floatval($recent_donated);
						else
							$recent_ratio = floatval($recent_donated) / floatval($recent_received);

						if ($received == 0)
							$ratio = floatval($donated);
						else
							$ratio = floatval($donated) / floatval($received);

						$score_recent = 100 * round($recent_sign * ($recent_ratio), 2);
						if ($score_recent > 0)
							$score_recent -= 99;
						$score_global = 100 * round($sign * ($ratio), 2);
						if ($score_global > 0)
							$score_global = ($score_global -99) * 10;

						AppScore :: $scores[$appid] = $score_recent + $score_global;
						if ($balance >= -10 && AppScore :: $scores[$appid] < 0)
							AppScore :: $scores[$appid] = -AppScore :: $scores[$appid];
					}
				} catch (AppException $e) {
					unset (AppScore :: $scores[$appid]);
				}

			}
			AppScore :: $scores["date"] = time();
			try {
				CacheManager :: set("Scores", AppScore :: $scores, false, 60);
			} catch (CacheManagerException $e) {
				CacheManager :: replace("Scores", AppScore :: $scores, false, 60);
			}
			if (!$force_update) unset(AppScore :: $scores["date"]);
			return AppScore :: $scores;
		}
	}

}
?>