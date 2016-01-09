<?php
/*
Template Name: uwr1results Ajax Ranking View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

if (@$_GET['dbg']) {
	header('Content-type: text/plain');
} else {
	header('Content-type: application/json');
}

// this should be in the controller, but it's more convenient to have it here - for now.
$league =& Uwr1resultsModelLeague::instance();
$ranking =& $league->ranking();


/**
 * Display code for regions & leagues listing
 */
$rankingFlat = array();
$rank = 0;
foreach ($ranking->rnk as $r) {
	// escape --- (&mdash;)
	if (!is_int($r['goalsDiff'])) { $r['goalsDiff'] = '"' . $r['goalsDiff'] . '"'; }
	if (!is_int($r['pointsPos'])) { $r['pointsPos'] = '"' . $r['pointsPos'] . '"'; }
	$rankingFlat[] = '{'
		. '"rank":'       . '"' . sprintf('%02d', ++$rank) . '"'
		. ',"team":'      . '"' . htmlentities($r['name'], ENT_COMPAT, 'UTF-8') . '"'
		. ',"tore":'      . '"' . $r['goalsPos'] . ':' . $r['goalsNeg'] . '"'
		. ',"tordiff":'   . $r['goalsDiff']
		. ',"punkte":'    . $r['pointsPos']
		. ',"spiele":'    . $r['matchesPlayed']
		. '}';
}

print @$_GET['jsonp'].'({"uwr1results":'
	. '['; // open ranking array
print implode(',', $rankingFlat);
print ']' // close uwr1results array
	.'})'; // close anon object and )

exit;
?>