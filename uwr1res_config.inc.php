<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

# BEGIN OF CONFIG
//define('UWR1RESULTS_CUR_SEASON', 7); // 7 means "started in 2007", i.e. 2007/2008
define('UWR1RESULTS_BASEURL', 'ergebnisse');
define('UWR1RESULTS_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'.UWR1RESULTS_BASEURL);
//define('UWR1RESULTS_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'.UWR1RESULTS_BASEURL.'/');

// Define the table for the plugin. (no need to change)
global $wpdb;
global $table_prefix;
$prefix = (isset($wpdb) && $wpdb->prefix) ? $wpdb->prefix : @$table_prefix;
define('UWR1RESULTS_TBL_PRE', $prefix . 'uwr_');
//define('UWR1RESULTS_TBL_PRE', $prefix . 'uwr1r_');

define('UWR1RESULTS_PTS_WIN',  3);
define('UWR1RESULTS_PTS_DRAW', 1);
define('UWR1RESULTS_PTS_LOSS', 0);

// Post IDs (PX = prefix?)
define('UWR1RESULTS_PAGE_ID_PX', 82);    // hexdec(bin2hex('R'))
define('UWR1RESULTS_PAGE_ID_INDEX', 21075); // hexdec(bin2hex('RS'))
define('UWR1RESULTS_PAGE_ID_LEAGUE_PX',     UWR1RESULTS_PAGE_ID_PX.'76'); // 76 = hexdec(bin2hex('L'))
define('UWR1RESULTS_PAGE_ID_TOURNAMENT_PX', UWR1RESULTS_PAGE_ID_PX.'84'); // 76 = hexdec(bin2hex('L'))
//define('UWR1RESULTS_PAGE_ID_LEAGUE', UWR1RESULTS_PAGE_ID_PREFIX.'76'.LEAGUE_ID); // 76 = hexdec(bin2hex('L'))

define('UWR1RESULTS_SEASON', 2011);
define('UWR1RESULTS_TOURNAMENT_REGION', -1);

define('UWR1RESULTS_JSON_CACHE_URL', 'http://uwr1cdn.appspot.com/jc/json');
define('UWR1RESULTS_JSON_CACHE_UPDATE_URL', 'http://uwr1cdn.appspot.com/jc/update');

# END OF CONFIG

define('UWR1RESULTS_VERSION', '1.0.4');
define('UWR1RESULTS_AJAX_API_VERSION', '2');

define('UWR1RESULTS_TBL_REGIONS',   UWR1RESULTS_TBL_PRE . 'regions'  );
define('UWR1RESULTS_TBL_LEAGUES',   UWR1RESULTS_TBL_PRE . 'leagues'  );
define('UWR1RESULTS_TBL_MATCHDAYS', UWR1RESULTS_TBL_PRE . 'matchdays');
define('UWR1RESULTS_TBL_FIXTURES',  UWR1RESULTS_TBL_PRE . 'fixtures' );
define('UWR1RESULTS_TBL_RESULTS',   UWR1RESULTS_TBL_PRE . 'results'  );
define('UWR1RESULTS_TBL_TEAMS',     UWR1RESULTS_TBL_PRE . 'teams'    );
//define('UWR1RESULTS_TBL_LEAGUES_TEAMS',     UWR1RESULTS_TBL_PRE . 'leagues_teams'    );

// include models
require_once 'uwr1results_model.class.php'; // base class
require_once 'uwr1results_helper.class.php';
require_once 'uwr1results_exception.class.php';
require_once 'uwr1results_model_region.class.php';
require_once 'uwr1results_model_league.class.php';
require_once 'uwr1results_model_matchday.class.php';
require_once 'uwr1results_model_fixture.class.php';
require_once 'uwr1results_model_result.class.php';
require_once 'uwr1results_model_team.class.php';

$RUL_HARDCODED = array(
//	'view' => 'league',
	'view' => 'index',
	'season' => '2011',//unused?
); // HARDCODED


/*
// DEBUG
function getRewriteRules() {
    global $wp_rewrite; // Global WP_Rewrite class object
    return $wp_rewrite->rules;
//    return $wp_rewrite->rewrite_rules();
}
*/
?>
