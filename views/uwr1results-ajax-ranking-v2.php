<?php
/*
Template Name: uwr1results Ajax Ranking View v2
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

header('Content-type: application/json');

// this should be in the controller, but it's more convenient to have it here - for now.
$league =& Uwr1resultsModelLeague::instance();
$ranking =& $league->ranking();


/**
 * Display code for regions & leagues listing
 */
$rankingFlat = array();
$rank = 0;
foreach ($ranking as $r) {
	if (!is_int($r['goalsDiff'])) { $r['goalsDiff'] = '"' . $r['goalsDiff'] . '"'; }
	if (!is_int($r['pointsPos'])) { $r['pointsPos'] = '"' . $r['pointsPos'] . '"'; }
	$rankingFlat[] = '{'
		// r = rang
		. '"r":'       . ++$rank // '"' . sprintf('%02d', ++$rank) . '"'
		// m = mannschaft
		. ',"m":'      . '"' . htmlentities($r['name'], ENT_COMPAT, 'UTF-8') . '"'
		// t = tore
		. ',"t":'      . '"' . $r['goalsPos'] . ':' . $r['goalsNeg'] . '"'
		// d = (tor)differenz
		. ',"d":'   . $r['goalsDiff']
		// p = punkte
		. ',"p":'    . $r['pointsPos']
		// s = spiele (gespielt)
		. ',"s":'    . $r['matchesPlayed']
		. '}';
}

$count = count($ranking);
if ($count > 0) {
	$status = 'OK';
} else {
	$status = 'Err';
}

$jsonp1 = $jsonp2 = '';
if (@$_GET['jsonp']) {
	$jsonp1 = @$_GET['jsonp'].'(';
	$jsonp2 = ')';
}
print $jsonp1.'{'
	. '"s":"'.$status.'"'
//	. ',"type":"rnk"'
	. ',"cnt":' . $count
	. ',"res":[' // open results array
	. implode(',', $rankingFlat)
	. ']' // close results array
	.'}'.$jsonp2; // close anon object

exit;
?>