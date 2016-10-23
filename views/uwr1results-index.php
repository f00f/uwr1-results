<?php
/*
Template Name: uwr1results Index View
Plugin URI: http://uwr1.de/
Description: TODO
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

$season = Uwr1resultsController::season();
$leagues =& Uwr1resultsModelRegion::instance()->findBySeason( $season );

get_header();
//<div class="primary" id="content">
?>
	<div id="league_page" class="uwr1results-view wrap_content has_sidebar">
	<div class="post-v2">
	<?php
	$title = Uwr1resultsView::title('');
	print '<h1 class="entry-title">'
		.'<a href="'.Uwr1resultsView::indexUrl().'" rel="bookmark" title="Permanenter Link zu '.$title.'">'
		.$title.'</a></h1>';
	// todo: use get_permalink (plug it)

	// breadcrumbs
	print '<div id="breadcrumbs">Du bist hier: <a href="'.Uwr1resultsView::indexUrl().'">UWR Ergebnisse</a></div><br />';

	$currentLevel = 0;
	$currentLevelName = '';
	foreach ($leagues as $l) {
		if ($l->league_level != $currentLevel) {
			// begin new level
			if (0 != $currentLevel) {
				// end previous region
				print '<br class="clear">';
				print '</div>';
			}
			$currentLevelName = Uwr1resultsModelLeague::levelName($l->league_name);
			if ($currentLevel * $l->region_ID < 0) {
				//print '<br style="clear:both;" /><hr style="margin-top:1.3em; margin-bottom:1.3em;" />';
			}
			print '<div class="uwr1results index league-level">';
			print '<h2 class="entry-title">' . $currentLevelName . '</h2>';

			$currentLevel = $l->league_level;
		}

		// print league
		print '<div class="league">'
		      . '<a href="'.Uwr1resultsView::resultsPageUrl($l->league_slug, $l->region_ID).'" title="Unterwasser Rugby Ergebnisse '.$l->league_name.'">'
		      . str_replace($currentLevelName, '', $l->league_name)
		      . '</a>'
		      . '</div>';
	}
	if (0 != $currentLevel) {
		// end last region
		print '<br class="clear">';
		print '</div>';
	}
	?>
	</div>
	<?php Uwr1resultsView::poweredBy(); ?>
</div>
<?php
get_sidebar();
get_footer();
?>