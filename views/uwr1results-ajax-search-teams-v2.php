<?php
/*
Template Name: uwr1results Ajax Search Teams View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

header('Content-type: application/json');

// this should be in the controller, but it's more convenient to have it here - for now.
$query=$GLOBALS['wp_query']->query_vars['q'];
$teams = Uwr1resultsModelTeam::instance()->findAll( array('fields' => 'team_name', 'where' => "`team_name` LIKE '%".Uwr1resultsHelper::sqlEscape($query)."%'") );

$my_teams = array();
foreach ($teams as $t) {
	$my_teams[] = $t->team_name;
}

$jsonp1 = $jsonp2 = '';
if (@$_GET['jsonp']) {
	$jsonp1 = @$_GET['jsonp'].'(';
	$jsonp2 = ')';
}
print $jsonp1.'{'
	. implode(',', $my_teams)
	. '}'.$jsonp2; // close anon object

exit;
?>