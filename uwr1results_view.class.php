<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';

require_once 'uwr1results_widget.class.php';

// The Uwr1resultsView class defines the visible part of Uwr1results
// It has methods to display all kind of pages.
class Uwr1resultsView {
	private $viewVars;

	/**
	 * The instance of this object
	 * Static will act as a global variabile
	 * Private: make sure no one from outside will change this value
	 * @static
	 * @access private
	 */
	private static $instance=NULL;

	/**
	 * Constructor
	 * nothing to do, make sure that no one can build this object
	 * @access private
	 * @return void
	 */
	private function __construct() {
	}
	
	/**
	 * Clone function
	 * Nothing to do
	 * Make sure that no one will get a copy of this object
	 * @access private
	 * @return void
	 */
	private function __clone() {  }

	/**
	 * // TODO: write better comment
	 */
	static function instance() {
		if (!self::$instance) {
			self::$instance = new Uwr1resultsView();
		}
		return self::$instance;
	}

	/**
	 * Prints a link to the Uwr1results stylesheet and javascript if current page is a Uwr1results page.
	 */
	function cssAndJs() {
		$siteurl = get_bloginfo('wpurl');
		// include css in any case. e.g. for widget
		print '<link rel="stylesheet" type="text/css" href="'.$siteurl.'/wp-content/plugins/uwr1results/uwr1results.css" />'."\n";
		if (!defined('IS_UWR1RESULTS_VIEW')) {
			return;
		}
		$siteurlPath = substr($siteurl, strlen('http://'.$_SERVER['HTTP_HOST']));
		if ('/' != $siteurlPath{0}) {
			$siteurlPath = '/'.$siteurlPath;
		}
		//print '<script type="text/javascript" src="'.$siteurl.'/wp-content/plugins/uwr1results/uwr1results.js.php?siteurl='.urlencode($siteurlPath)./*($https?'ssl=1':'').*/'"></script>'."\n";
	}

	/**
	 * // TODO: write better comment
	 */
	function title($title) {
		if (!defined('IS_UWR1RESULTS_VIEW')) {
			return $title;
		}

		$season = Uwr1resultsController::season();
		$season = $season.'/'.($season+1); // TODO: make a function for that

		$view = Uwr1resultsController::WhichView();
		if ('index' == $view) {
			$title = 'Unterwasserrugby Liga Ergebnisse der Saison '.$season;
		} else if ('league' == $view) {
			$title = 'Ergebnisse der ' . Uwr1resultsModelLeague::instance()->name() . ' im UWR (Saison ' . $season . ')';
		} else if ('tournament' == $view) {
			$title = 'Ergebnisse des UWR Turniers ' . Uwr1resultsModelLeague::instance()->name();
		}
		return $title;
	}

	/**
	 * // TODO: write better comment
	 */
	function metaTags() {
		if (!defined('IS_UWR1RESULTS_VIEW')) {
			return false;
		}

		$season = Uwr1resultsController::season();
		$season = $season.'/'.($season+1); // TODO: make a function for that

		$keywords = array('Unterwasserrugby', 'UWR', 'Liga', 'Ergebnisse', 'Unterwasser Rugby');
		$tags = array();

		$view  = Uwr1resultsController::WhichView();
		if ('league' == $view) {
			$keywords[] = Uwr1resultsModelLeague::instance()->name();
			$tags['description'] = 'Ergebnisse der ' . Uwr1resultsModelLeague::instance()->name() . ' im UWR (Saison ' . $season . ') | ' . get_bloginfo('name');
		} else if ('tournament' == $view) {
			$keywords[] = Uwr1resultsModelLeague::instance()->name();
			$keywords[] = 'Turnier';
			$tags['description'] = 'Ergebnisse des UWR Turniers ' . Uwr1resultsModelLeague::instance()->name() . ' | ' . get_bloginfo('name');
		} else if ('index' == $view) {
			$keywords[] = 'Bundesliga';
			$keywords[] = 'Damenliga';
			$keywords[] = 'Landesliga';
			$keywords[] = 'Oberliga';
			$keywords[] = 'Bezirksliga';
			$keywords[] = 'Turniere';
			$keywords[] = 'Damen';
			$keywords[] = 'Jugend';
			$keywords[] = 'Junioren';
			$tags['description'] = 'UWR Ergebnisse aus der 1. und 2. Bundesliga, Damenliga, Landesliga, Oberliga und Bezirksliga im ' . get_bloginfo('name') . '.';
		}
		$tags['keywords'] = ((count($keywords) > 0)
			? implode(', ', $keywords) . ', '
			: '')
			. 'UW Rugby, Jugend, Junioren';
		foreach($tags as $name => $content) {
			print '<meta name="'.$name.'" content="'.$content.'" />'."\n";
		}
		return true;
	}

	/**
	 * // TODO: write better comment
	 * 
	 */
	function replacePermalink($link, $id) {
		/*
		if (!defined('IS_UWR1RESULTS_VIEW')) {
			return $link;
		}
		if (UWR1RESULTS_PAGE_ID_INDEX == $id) {
			$link = UWR1RESULTS_URL;
		} else if (UWR1RESULTS_PAGE_ID_PX == substr($id, 0, 2) && strlen($id) == strlen(UWR1RESULTS_PAGE_ID_PX) + 6) {
			$twoChars = str_split($id, 2);
			$link = UWR1RESULTS_URL.'/'.$twoChars[1].$twoChars[2];
			if ('00' != $twoChars[3]) {
				$link .= '/'.$twoChars[3];
			}
		}
		*/
		return $link;
	}

	function adminScripts() {
		wp_enqueue_script('hoverIntent');
		wp_enqueue_script('common');
		wp_enqueue_script('jquery-color');
		//die('scripts inserted');
	}

	/**
	 * Puts the Uwr1results management link in the manage submenu
	 */
	function adminMenu() {
		// TODO: This does not only create the menu, it also (only?) *does* sth. with the entries.
		//       But those actions must be done before the menu is created (due to the redirects).
		//       !Split the function!
		$page = add_submenu_page('admin.php', __('UWR Ergebnisse'), __('UWR Ergebnisse'), 'edit_posts', 'uwr1results', array('Uwr1resultsController', 'adminAction'));
		add_action( 'admin_print_scripts-' . $page, array('Uwr1resultsView', 'adminScripts') );
	}

	/**
	 * // TODO: write better comment
	 */
	function viewVar($varName=null) {
		if (null === $varName) {
			if (null !== $this->viewVars && !is_array($this->viewVars)) {
				return $this->viewVars;
			}
			return false;
		}
		if (is_array($this->viewVars)) {
			return $this->viewVars[$varName];
		}
		return false;
	}

	/**
	 * // TODO: write better comment
	 */
	function show($view, $viewVars=null) {
		// TODO: db interaction should go into model
		global $wpdb;

		if (is_array($viewVars)) {
			$this->viewVars =& $viewVars;
		}
/*
		if ('admin-edit' == $view) {
			$viewVars['action'] = empty($viewVars['eventId']) ? 'add' : 'edit';
			$this->viewVars['action'] = $viewVars['action'];
		}
*/		
		// TODO: make viewVars globally available?

		// TODO: make shure it is a valid view
		// TODO: look in theme folder!
		$template = 'views/uwr1results-'.$view.'.php';
		$rv = @require_once $template;
		exit;
	}
	
	/**
	 * Convert a (My)sql date format into a prettier one
	 * @param $mysqlDate String   e.g. 2007-01-01 or 2007-01-01 00:00:00
	 * @return String             better human-readable date
	 */
	static function mysqlToDate($mydsqlDate) {
		$date = strtotime($mydsqlDate);
		return date(__('d.m.'), $date);
	}

	/**
	 * Convert a (My)sql date format into a prettier one
	 * @param $mysqlDate String   e.g. 2007-01-01 or 2007-01-01 00:00:00
	 * @return String             better human-readable date
	 */
	static function mysqlToFullDate($mydsqlDate) {
		$date = strtotime($mydsqlDate);
		return date(__('d.m.Y'), $date);
	}

	/**
	 * Convert a (My)sql date format into a prettier one
	 * @param $mysqlDate String   e.g. 2007-01-01 or 2007-01-01 00:00:00
	 * @return String             better human-readable date
	 */
	static function splitMysqlDate($mydsqlDate) {
		$date = strtotime($mydsqlDate);
		return array(
			'year'  => date('Y', $date),
			'month' => date('m', $date),
			'day'   => date('d', $date),
		);
	}

	static function poweredBy() {
		print '<div class="rs-footer" style="clear:both; padding-top:2em;">powered by <a href="'.self::indexUrl().'">uwr1results ' . UWR1RESULTS_VERSION . '</a>.</div>';
	}

	public static function editLink( $params ) {
		$link = get_bloginfo('wpurl') . '/wp-admin/edit.php'
			. '?page=uwr1results';
		foreach ($params as $k => $v) {
			$link .= '&'.urlencode($k).'='.urlencode($v);
		}
		return $link;
	}

	public static function viewLink( $params ) {
		$path = '';
/*
		if ($params['region']) {
			$path .= $params['region'] . '/';
		}
*/
		if ($params['league']) {
			$path .= '/liga/' . $params['league'] . '/';
		}
		if ($params['tournament']) {
			$path .= '/turnier/' . $params['tournament'] . '/';
		}
		$link = self::indexUrl() . $path;
		return $link;
	}

	public static function indexUrl() {
		return UWR1RESULTS_URL;
	}

	private static function leagueUrl(&$slug) {
		return self::indexUrl() . '/liga/'.urlencode($slug);
	}

	private static function tournamentUrl(&$slug) {
		return self::indexUrl() . '/turnier/'.urlencode($slug);
	}

	/**
	 * Params: only $league: League object
	 * Params: $league: League slug; $regionId: regionId
	 */	
	public static function resultsPageUrl(&$league, $regionId = 0) {
		$slug = '';

		if ( is_string($league) ) {
			$slug =& $league;
		} else if ( is_object($league) ) {
			$slug =& $league->slug();
			if ($league->hasProperty('regionId')) {
				$regionId = $league->regionId();
			}
		} else {
			trigger_error(get_class() . '::resultsPageUrl: Invalid parameter format.', E_USER_WARNING);
		}

		if ($regionId < 0) {
			return self::tournamentUrl($slug);
		}
		if ($regionId > 0) {
			return self::leagueUrl($slug);
		} else {
			trigger_error(get_class() . '::resultsPageUrl: Invalid parameter value.', E_USER_WARNING);
		}
	}

	public static function ajaxUrl($view, $param=null) {
		return self::indexUrl() . '/ajax/'.urlencode($view).'/'
			. ( (!is_null($param) && is_string($param)) ? urlencode($param).'/' : '');
	}
} // Uwr1resultsView

// Add pretty title
add_filter('wp_title', array('Uwr1resultsView', 'title'));
// Add Uwr1results stylesheet and javascript
add_action('wp_head', array('Uwr1resultsView', 'cssAndJs'));
// Add Uwr1results meta-description
add_filter('wp_head', array('Uwr1resultsView', 'metaTags'));
// Add custom permalink
add_filter('page_link', array('Uwr1resultsView', 'replacePermalink'), 10, 2);
// Add Uwr1results pages to admin menu
add_action('admin_menu', array('Uwr1resultsView', 'adminMenu'));
?>