<?php
/*
Template Name: uwr1results Index View
Plugin URI: http://uwr1.de/
Description: TODO
Author: Hannes Hofmann
Author URI: http://uwr1.de/
Version: 0.1
*/

$season = Uwr1resultsController::season();
$regions =& Uwr1resultsModelRegion::instance()->findBySeason( $season );

get_header();
//<div class="primary" id="content">
?>
	<div id="league_page" class="wrap_content">
	<div class="post">
	<p class="update"><strong>Achtung:</strong> Dieser Teil von <a href="Unterwasserrugby">uwr1.de</a> befindet sich noch in der Entwicklung. Es kann deshalb passieren, dass noch Fehler auftreten.</p>
	<?php

	$title = Uwr1resultsView::title('');
	print '<h2 class="posttitle">'
		.'<a href="'.Uwr1resultsView::indexUrl().'" rel="bookmark" title="Permanenter Link zu '.$title.'">'
		.$title.'</a></h2>';
	// todo: use get_permalink (plug it)
	
	// breadcrumbs
	print '<div id="breadcrumbs">Du bist hier: <a href="'.Uwr1resultsView::indexUrl().'">UWR Ergebnisse</a></div><br />';

/*
global $wp_query;
print '<hr />';
print_r( getRewriteRules() );
print '<hr />';
print_r( $wp_query->query_vars );
print '<hr />';
*/

	$currentRegion = 0;
	foreach ($regions as $r) {
		if ($r->region_ID != $currentRegion) {
			// begin new region
			if (0 != $currentRegion) {
				// end previous region
				print '</div>';
			}
			if ($currentRegion * $r->region_ID < 0) {
				print '<br style="clear:both;" /><hr style="margin-top:1.3em; margin-bottom:1.3em;" />';
			}
			print '<div class="uwr1results index region">';
			print '<h4>' . $r->region_name . '</h4>';

			$currentRegion = $r->region_ID;
		}

		// print league
		print '<div>'
		      . '<a href="'.Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID).'" title="Unterwasser Rugby Ergebnisse '.$r->league_name.'">'
		      . $r->league_name
		      . '</a>'
		      . '</div>';
	}
	if (0 != $currentRegion) {
		// end last region
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