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
$regions = Uwr1resultsModelRegion::instance()->findBySeason(/*$season*/);


/**
 * Display code for regions & leagues listing
 */

print '<div class="wrap">';
print '<h2>'.__('UWR Ligen und Ergebnisse').'</h2>';

$currentRegion = 0;
foreach($regions as $r) {
	if ($currentRegion != $r->region_ID) {
		if (0 != $currentRegion) {
			print '</ul>'; // close previous
		}
		$editLink = Uwr1resultsView::editLink(array(
			'action' => 'edit_region',
			'region_id' => $r->region_ID,
//			'season' => Uwr1resultsCont...,
			));
		print '<h3>'
			. (('Turniere' != $r->region_name && 'Jugend' != $r->region_name) ? 'SB ' : '')
			. $r->region_name
//			. ' <small style="font-weight:normal;">[<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>]</small>'
			. '</h3>'
			.'<ul>';
		$currentRegion = $r->region_ID;
	}

	$matchdaysLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_matchdays',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$teamsLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_teams',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$editLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$viewLink = Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID);
	print '<li>'
		. $r->league_name
//		. '<br />'
		. ' ['
		. '<a href="'.$matchdaysLink.'">'.__('Spieltage bearbeiten').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$teamsLink.'">'.__('Mannschaften').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>'
		. ', '
		. '<a href="'.$viewLink.'">'.( (UWR1RESULTS_TOURNAMENT_REGION == $r->region_ID) ? __('Turnier anzeigen') : __('Liga anzeigen') ).'</a>'
		. ']'
		. '</li>';
}
if (0 != $currentRegion) {
	print '</ul>'; // close last
}
?>

<br class="clear" />

</div>
