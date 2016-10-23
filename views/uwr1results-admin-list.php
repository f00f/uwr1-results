<?php
/*
Template Name: uwr1results Admin Index View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

print '<style>'
	. 'a.disfunct { text-decoration:line-through; }'
	. '</style>';



// this should be in the controller, but it's more convenient to have it here - for now.
//Uwr1resultsModelRegion::instance()->season(/*$season*/);
$leagues = Uwr1resultsModelRegion::instance()->findBySeason(/*$season*/);


/**
 * Display code for regions & leagues listing
 */

print '<div class="wrap">';
print '<h2>'.__('UWR Ergebnisse').'</h2>';

$currentLevel = 0;
foreach($leagues as $l) {
	if ($l->league_level != $currentLevel) {
			// begin new level
		if (0 != $currentLevel) {
			print '</ul>'; // close previous
		}
		$levelName = Uwr1resultsModelLeague::levelName($l->league_name);
		$editLink = Uwr1resultsView::editLink(array(
			'action' => 'edit_region',
			'region_id' => $l->region_ID,
//			'season' => Uwr1resultsCont...,
			));
		print '<h3>'
			//. (('Turniere' != $l->region_name && 'Jugend' != $l->region_name) ? 'SB ' : '')
			//. $l->region_name
			. $levelName
//			. ' <small style="font-weight:normal;">[<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>]</small>'
			. '</h3>'
			.'<ul>';
		$currentLevel = $l->league_level;
	}

	$matchdaysLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_matchdays',
		'league_id' => $l->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$teamsLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_teams',
		'league_id' => $l->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$editLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league',
		'league_id' => $l->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$viewLink = Uwr1resultsView::resultsPageUrl($l->league_slug, $l->region_ID);
	print '<li>'
		. $l->league_name
//		. '<br />'
		. ' ['
		. '<a href="'.$matchdaysLink.'">'.__('Spieltage bearbeiten').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$teamsLink.'">'.__('Mannschaften').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>'
		. ', '
		. '<a href="'.$viewLink.'">'.( (UWR1RESULTS_TOURNAMENT_REGION == $l->region_ID) ? __('Turnier anzeigen') : __('Liga anzeigen') ).'</a>'
		. ']'
		. '</li>';
}
if (0 != $currentLevel) {
	print '</ul>'; // close last
}
?>

<br class="clear" />

</div>
