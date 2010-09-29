<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/


require_once 'uwr1res_config.inc.php';
require_once 'uwr1results_view.class.php';

// The Uwr1resultsController class handles all interacion with Wordpress
// It defines and registers hooks and filters.
class Uwr1resultsController {
	private $view = false;

	/**
	 * The instance of this object.
	 * Static will act like a global variabile
	 *
	 * @static
	 * @access private
	 */
	private static $instance=NULL;

	/**
	 * Constructor.
	 * Add filter and action to Wordpress.
	 * Make sure that no one can build this object.
	 *
	 * @access private
	 * @return void
	 */
	private function __construct() {
		if (Uwr1resultsController::isUwr1resultsUrl()) {
			add_filter('the_posts', array(&$this, 'addFakePostToPosts'));
			// Display Uwr1results pages
			add_action('template_redirect', array('Uwr1resultsController', 'templateRedirect'));
		}
	}
	
	/**
	 * Empty clone function.
	 * Make sure that no one will get a copy of this object
	 *
	 * @access private
	 * @return void
	 */
	private function __clone() {  }

	/**
	 * Return the single instance of this class.
	 * The instance is created if neccessary
	 *
	 * @access public
	 * @return Object   The singleton instance
	 */
	public static function instance() {
		if (!self::$instance) {
			self::$instance = new Uwr1resultsController();
		}
		return self::$instance;
	}

	// Plugin activation/deactivation
	/**
	 * Plugin activation.
	 * Create database table.
	 */
	function activatePlugin() {
		Uwr1resultsModelRegion::instance()->createTable();
		Uwr1resultsModelLeague::instance()->createTable();
		Uwr1resultsModelMatchday::instance()->createTable();
		Uwr1resultsModelFixture::instance()->createTable();
		Uwr1resultsModelResult::instance()->createTable();
		Uwr1resultsModelTeam::instance()->createTable();
	}

	/**
	 * Plugin deactivation.
	 * Maybe: Drop database table.
	 */
	function deactivatePlugin() {
		// empty
	}

	/**
	 * // TODO: write better comment
	 * // TODO: give better name
	 * // TODO: act only when uwr1results page is active
	 */
	// only handle save/delete actions here
	// TODO: This does not only create the menu, it also (only?) *does* sth. with the entries.
	//       But those actions must be done before the menu is created (due to the redirects).
	//       !Split the function!
	function adminDoAction() {
		if (!Uwr1resultsController::isUwr1resultsAdminUrl()) {
			return;
		}

		$action   = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';

		switch ($action) {
			case 'edit_result':
				// store result
				if ( @count($_POST) > 0 && !empty($_REQUEST['fixture_id']) ) {
					$r =& Uwr1resultsModelResult::instance();
					$r->populateAndSave();
					// TODO: redirect after successful save!
					Uwr1resultsHelper::redirectBack();
					// else: show error message
				}
				break;
			case 'edit_matchday_results':
				// store results
				if ( @count($_POST) > 0 && !empty($_REQUEST['matchday_id']) ) {
					//print_r($_POST);
					$r =& Uwr1resultsModelResult::instance();
					$r->saveMany();
					Uwr1resultsHelper::redirectBack();
					/*
					// TODO: redirect after successful save!
					// else: show error message
					*/
				}
				break;
			case 'edit_matchday_fixtures':
				// store fixtures
				if ( @count($_POST) > 0 && @count($_POST['data']) > 0 && !empty($_REQUEST['matchday_id']) ) {
					//print_r($_POST);
					$f =& Uwr1resultsModelFixture::instance();
					$f->saveMany( (int) $_REQUEST['matchday_id'] );
					//Uwr1resultsHelper::redirectBack();
					// TODO: redirect after successful save! And show message/highlight
					// else: show error message
				}
				break;
			case 'edit_league_matchdays':
				// store matchdays
				if ( @count($_POST) > 0 && !empty($_REQUEST['league_id']) ) {
					$m =& Uwr1resultsModelMatchday::instance();
					$m->saveMany( (int) $_REQUEST['league_id'] );
				}
				break;
		}
	}

	/**
	 * // TODO: write better comment
	 * // TODO: give better name
	 * // TODO: act only when uwr1results page is active
	 */
	// TODO: This does not only create the menu, it also (only?) *does* sth. with the entries.
	//       But those actions must be done before the menu is created (due to the redirects).
	//       !Split the function!
	function adminAction() {
		$title = __('Uwr1results');
		$parent_file = 'edit.php';
		
		$action   = !empty($_REQUEST['action'])
				? $_REQUEST['action']
				: '';
		
		switch ($action)
		{
/*
			case '': // Initial page on admin interface. Lists regions and leagues therein
				define('IS_UWR1RESULTS_VIEW', true);

				Uwr1resultsView::instance()->show('admin-list');
				break;
*/
			case 'edit_league_matchdays': // Edit the matchdays of a league
				if (empty($_REQUEST['league_id']) || !Uwr1resultsModelLeague::isValidId($_REQUEST['league_id'])) {
					new Uwr1resultsException('Good lord you didn\'t provide a valid league ID to edit, what were you thinking?');
				}

				define('IS_UWR1RESULTS_VIEW', true);

				Uwr1resultsView::instance()->show('admin-league-matchdays');
				break;
/*
			case 'edit_league_teams': // Edit the teams that play in a league
				if (empty($_REQUEST['league_id']) || !Uwr1resultsModelLeague::isValidId($_REQUEST['league_id'])) {
					new Uwr1resultsException('Good lord you didn\'t provide a valid league ID to edit, what were you thinking?');
				}

				define('IS_UWR1RESULTS_VIEW', true);

				Uwr1resultsView::instance()->show('admin-league-teams');
				break;
*/

			case 'edit_result':
				$fixture_id = !empty($_REQUEST['fixture_id'])
						? (int)$_REQUEST['fixture_id']
						: false;
				if ( ! Uwr1resultsModelFixture::isValidId($fixture_id) ) {
					print '<div class="error"><p>Good lord you didn\'t provide a fixture id to edit, what were you thinking?</p></div>';
				} else {
					Uwr1resultsView::instance()->show('admin-result', array('fixtureId' => $fixture_id));
				}
				break;
			case 'edit_matchday_results':
				$matchday_id = !empty($_REQUEST['matchday_id'])
						? (int)$_REQUEST['matchday_id']
						: false;
				if ( ! Uwr1resultsModelMatchday::isValidId($matchday_id) ) {
					print '<div class="error"><p>Good lord you didn\'t provide a matchday id to edit, what were you thinking?</p></div>';
				} else {
					Uwr1resultsView::instance()->show('admin-matchday-results', array('matchdayId' => $matchday_id));
				}
				break;
			case 'edit_matchday_fixtures':
				$matchday_id = !empty($_REQUEST['matchday_id'])
						? (int)$_REQUEST['matchday_id']
						: false;
				if ( ! Uwr1resultsModelMatchday::isValidId($matchday_id) ) {
					print '<div class="error"><p>Good lord you didn\'t provide a matchday id to edit, what were you thinking?</p></div>';
				} else {
					Uwr1resultsView::instance()->show('admin-matchday-fixtures', array('matchdayId' => $matchday_id));
				}
				break;
			default:
				Uwr1resultsView::instance()->show('admin-list');
				//Uwr1resultsView::instance()->show( 'error', array('errorMessage' => __('Invalid action!')) );
				break;
		}
	}

	/**
	 * // TODO: write better comment
	 * What we are going to do here, is create a fake post.  A post
	 * that doesn't actually exist. We're gonna fill it up with
	 * whatever values you want.  The content of the post will be
	 * the output from your plugin.
	 * @return Object   Fake post
	 */
	// TODO: put correct post_author on single event view (from event)
	// TODO: put correct post_name (slug from event / view type and view props [year etc.])
	// TODO: put correct ID (slug from event / view type and view props [year etc.])
	// TODO: put correct Title, Content, Date
	private static function createFakePost() {
		global $post;
		$post = new stdClass;

		$post->post_type = 'page';
		
		/**
		 * The author ID for the post.  Usually 1 is the sys admin.  Your
		 * plugin can find out the real author ID without any trouble.
		 */
		$post->post_author = 1;

//		Not sure if this is even important.  But gonna fill it up anyway.
//		$post->guid = get_bloginfo('wpurl') . '/' . $this->page_slug;
		
		$post->post_title = Uwr1resultsView::title(__('Ergebnisse')); // FIXME: translate

		/**
		 * This is the content of the post.  This is where the output of
		 * your plugin should go.  Just store the output from all your
		 * plugin function calls, and put the output into this var.
		 */
		$post->post_content = $post->post_title;//$this->getContent();
		
		// Fake post ID to prevent WP from trying to show comments for
		// a post that doesn't really exist.
		// post_name is used in get_permalink()
		$view = Uwr1resultsController::whichView();
		switch ($view) {
		case 'index':
			$post->ID = UWR1RESULTS_PAGE_ID_INDEX;
			$post->post_name = 'ergebnisse';
			break;
		case 'league':
			$l =& Uwr1resultsModelLeague::instance();
			$leagueId = $l->id();
			$post->ID = UWR1RESULTS_PAGE_ID_LEAGUE_PX.sprintf('%03d', $leagueId);
			$post->post_name = 'ergebnisse/liga/'.$l->slug();
		case 'tournament':
			$t =& Uwr1resultsModelLeague::instance();
			$tournamentId = $t->id();
			$post->ID = UWR1RESULTS_PAGE_ID_TOURNAMENT_PX.sprintf('%03d', $tournamentId);
			$post->post_name = 'ergebnisse/turnier/'.$t->slug();
			break;
		case 'ajax-ranking':
		case 'ajax-ranking-v2':
			$l =& Uwr1resultsModelLeague::instance();
			$leagueId = $l->id();
			break;
		default:
			$post->ID = -1;
			$post->post_name = 'ergebnisse';
			break;
		}

		// Static means a page, not a post.
		$post->post_status = 'static';
		
		// Turning off comments for the post.
		$post->comment_status = 'closed';
		
		/**
		 * Let people ping the post?  Probably doesn't matter since
		 * comments are turned off, so not sure if WP would even
		 * show the pings.
		 */
		// disable pings
		$post->ping_status = 'closed';
		// set comment count to zero
		$post->comment_count = 0;
		
		/**
		 * You can pretty much fill these up with anything you want.  The
		 * current date is fine.  It's a fake post right?  Maybe the date
		 * the plugin was activated?
		 */
		$post->post_date = current_time('mysql');
		// TODO: get offset dynamically
		$post->post_date_gmt = current_time('mysql', 2);
		return($post);
	}

	/**
	 * Add a fake post to $posts array.
	 *
	 * @access public   Because called by plugin API
	 * @param $posts Array
	 * @return Array
	 * // TODO: write better comment
	 */
	public function addFakePostToPosts( $posts ) {
		if (false === $this->whichView()) {
			return $posts;
		}
		global $wp_query;
		$posts = array();
		$posts[] = $this->createFakePost();
		$wp_query->is_page     = true;
		$wp_query->is_singular = false;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
		$wp_query->is_category = false;
		$wp_query->is_404      = false;
		unset($wp_query->query['error']);
		$wp_query->query_vars['error'] = '';
		return $posts;
	}

	/**
	 * // TODO: write better comment
	 * Handles the inclusion of templates, when appropriate.
	 * index.php?archive=tag (or equivalent) will try and use the template tag_all.php
	 * index.php?tag={tag name} (or equivalent) will try and use the template tag.php
	 */
	function templateRedirect() {
		$view = Uwr1resultsController::whichView();
		if (false === $view || 'feed' == $view) {
			return false;
		}
		$templateFile = TEMPLATEPATH . '/uwr1results-'.$view.'.php';
		if (file_exists($templateFile) && is_readable($templateFile)) {
//			require_once $templateFile;
			load_template($templateFile);
			exit;
		}

		$templateFile = dirname(__FILE__) . '/views/uwr1results-'.$view.'.php';
		if (file_exists($templateFile) && is_readable($templateFile)) {
//			require_once $templateFile;
			load_template($templateFile);
			exit;
		}

		trigger_error('Error in templateRedirect: Template file does not exist');
		return false;
	}

	public static function season() {
		// TODO: look for season in URL
		return UWR1RESULTS_SEASON;
	}

/* deprecated
	static function getMatchdays() {
		// TODO: look for season in URL
		//return $GLOBALS['RUL_HARDCODED']['matchdays'];
	}

	static function getTeams() {
		//return $GLOBALS['RUL_HARDCODED']['teams'];
	}
*/

	/**
	 * // TODO: write better comment
	 */
	static function isUwr1resultsUrl() {
		return false !== strpos('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], UWR1RESULTS_URL);
		//return false !== strpos($_SERVER['REQUEST_URI'], '/'.UWR1RESULTS_BASEURL);
	}

	/**
	 * // TODO: write better comment
	 */
	static function isUwr1resultsAdminUrl() {
		return ('edit.php' == basename($_SERVER['SCRIPT_NAME']) && 'uwr1results' == $_GET['page']);
	}

	/**
	 * // TODO: write better comment
	 * Decides which page (view) to show
	 * Available pages are
	 * * viewIndex - show a list of regions
	 * * viewRegion - show a list of divisions
	 * * viewDivision - show a list of fixtures (grouped by "spieltage")
	 * * ... viewTeam, viewTable, ...
	 */
	static function whichView() {
		if (false === Uwr1resultsController::isUwr1resultsUrl()) {
			return false;
		}

		global $wp_query;
		
		define('IS_UWR1RESULTS_VIEW', true);

		if ($wp_query->query_vars['league']) {
			Uwr1resultsModelLeague::instance()->findBySlug( $wp_query->query_vars['league'] );
			if ( !Uwr1resultsModelLeague::instance()->found() ) {
				new Uwr1resultsException('Diese Liga wurde nicht gefunden.');
			}
			return 'league';
		}

		if ($wp_query->query_vars['tournament']) {
			Uwr1resultsModelLeague::instance()->findBySlug( $wp_query->query_vars['tournament'] );
			if ( !Uwr1resultsModelLeague::instance()->found() ) {
				new Uwr1resultsException('Dieses Turnier wurde nicht gefunden.');
			}
			return 'tournament';
		}

		if ($wp_query->query_vars['ajaxview']) {
			$av =& $wp_query->query_vars['ajaxview'];
			if ('ranking' == $av) {
				Uwr1resultsModelLeague::instance()->findBySlug( $wp_query->query_vars['q'] );
				if ( !Uwr1resultsModelLeague::instance()->found() ) {
					new Uwr1resultsException('Diese Liga wurde nicht gefunden.');
				}
				if (2 == @$_GET['v']) {
					$av .= '-v2'; // use version 2
				}
			}
			return 'ajax-'.$av;
		}

		// fallback / default
		return 'index';

		if (is_feed() || !empty($_GET['feed'])) {
			trigger_error('Error in whichView: Feed');
			return 'feed';
		}
		return 'current';
	}

/* not yet
	static function shortcode($atts) {
		$shortcode = $atts[0];
		$defaults = array();
		switch($shortcode) {
			case 'tabelle':
				$defaults = array('id' => null);
				break;
			case 'spieltag':
				$defaults = array('id' => null);
				break;
		}
		extract( shortcode_atts( $defaults, $atts ) );
		
		return $content;
	}
*/
} // Uwr1resultsController

// Uwr1results URL rewriting
add_filter('generate_rewrite_rules', array('Uwr1resultsHelper', 'generateRewriteRules'));

// Note: This filter is not neccessary, because 'year' and 'monthnum' are already in the query_vars array.
//       If that should change in future versions, activate this filter.
add_filter('query_vars', array('Uwr1resultsHelper', 'addQueryVars'));

// Perform Uwr1results actions before everything else (might redirect)
add_action('init', array('Uwr1resultsController', 'adminDoAction'));

/* not yet
// Add Shortcode 'tabelle'
add_shortcode('tabelle', array('Uwr1resultsController', 'shortcodeRanking'));
*/

$dummy=Uwr1resultsController::instance();
/*
function addIcalRewriteTag() {
	add_rewrite_tag('%ical%', '(ical)');
}
add_action('init', 'addIcalRewriteTag');
*/
?>
