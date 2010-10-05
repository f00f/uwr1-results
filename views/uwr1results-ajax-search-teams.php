<?php
/*
Template Name: uwr1results Ajax Search Teams View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

// this should be in the controller, but it's more convenient to have it here - for now.
$query=$GLOBALS['wp_query']->query_vars['q'];
$teams = Uwr1resultsModelTeam::instance()->findAll( array('fields' => 'team_name', 'where' => "`team_name` LIKE '%".Uwr1resultsHelper::sqlEscape($query)."%'") );

foreach ($teams as $t) {
	print $t->team_name."\n";
}
exit;
?>