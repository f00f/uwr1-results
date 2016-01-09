<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';

class Uwr1resultsHelper {

	/**
	 * Redirect the user back to his origin.
	 * Uses Wordpress functions to determine the origin and to do the redirect
	 */
	public static function redirectBack() {
		wp_redirect(wp_get_referer());
		exit;
	}

	public static function slugify( $str ) {
		$str = trim($str);
		$str = strtolower($str);
		$str = str_replace(
				array(' ', '/'),
				'-',
				$str);
		$str = str_replace(
				array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'),
				array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss'),
				$str);
		$str = preg_replace('/[^a-zA-Z0-9-]/', '', $str);
		return $str;
	}
	
	public static function sqlEscape( &$str ) {
		global $wpdb;
		$escapedValue = '';
		if (is_numeric($str)) {
			$escapedValue = '' . $str;
		} else if (is_string($str)) {
			$escapedValue = $wpdb->_real_escape($str);
		}
		// ignore other types
		return $escapedValue;
	}
	
	public static function checkPermission( $perm='save' ) {
		switch ( $perm ) {
			case 'add':
				return current_user_can(UWR1RESULTS_CAPABILITIES_ADD);
				break;
			case 'edit':
				return current_user_can(UWR1RESULTS_CAPABILITIES_EDIT);
				break;
			case 'save':
				return current_user_can(UWR1RESULTS_CAPABILITIES);
				break;
		}
	}

	public static function enforcePermission( $perm='save' ) {
		if ( Uwr1resultsHelper::checkPermission( $perm ) ) {
			return true;
		}
		new Uwr1resultsException( 'Du hast nicht die nötigen Rechte um diese Aktion auszuführen.' );
	}

	/**
	 * Add Uwr1results specific values to the query_vars array.
	 *
	 * @param $vars String[]   Global query_vars from Wordpress
	 * @return String[]   Global query_vars for Wordpress
	 */
	public static function addQueryVars($vars) {
		$vars[] = 'league';
		$vars[] = 'tournament';
		$vars[] = 'season';
		$vars[] = 'team';
		// ajaxview, id for ajax
		$vars[] = 'ajaxview';
		$vars[] = 'q';
		//$vars[] = 'id';
		return $vars;
	}

	/**
	 * Add rewrite rules required for Uwr1results.
	 *
	 * @param $rules String[]   Global rewrite_rules from Wordpress
	 * @return String[]   Global rewrite_rules for Wordpress
	 */
	public static function generateRewriteRules() {
		global $wp_rewrite;

		$ep_mask = EP_NONE;
		$paged = false;
		$feed = false;
		$forcomments = false;
		$walk_dirs = false;
		$endpoints = false;

		// add rewrite tokens, order is important!

		$keytag   = '%ajaxview%';
		$querytag = '%query%';
		$wp_rewrite->add_rewrite_tag($keytag, '([^/]+?)', 'ajaxview=');
		$wp_rewrite->add_rewrite_tag($querytag, '([^/]+?)', 'q=');
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/ajax/{$keytag}/{$querytag}/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;

		$keytag = '%ajaxview%';
		$wp_rewrite->add_rewrite_tag($keytag, '([^/]+?)', 'ajaxview=');
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/ajax/{$keytag}/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;

		$keytag = '%team%';
		$wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'team='); // must be matched by an entry in addQueryVars()
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/mannschaft/{$keytag}/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
		
		/*
		$keytag = '%league%';
		$keytag2 = '%matchday%';
		$wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'league=');
		$wp_rewrite->add_rewrite_tag($keytag2, '(.+?)', 'matchday=');
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/ergebnisse/$keytag/spieltag/$keytag2/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
		*/

		/* old structure: /liga/ergebnisse/slug/
		$keytag = '%league%';
		$wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'league=');
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/ergebnisse/{$keytag}/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
		*/

		// Liga Ergebnisse
		// league w/ season: /ergebnisse/liga/l-slug/season/
		$keytagL = '%league%';
		$keytagS = '%season%';
		$wp_rewrite->add_rewrite_tag($keytagL, '([^\/]+)', 'league='); // must be matched by an entry in addQueryVars()
		$wp_rewrite->add_rewrite_tag($keytagS, '(2[0-9\-]+)', 'season='); // must be matched by an entry in addQueryVars()
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/liga/{$keytagL}/{$keytagS}";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;

		// league w/o season: /ergebnisse/liga/l-slug/
		$keytag = '%league%';
		$wp_rewrite->add_rewrite_tag($keytag, '([^\/]+)', 'league='); // must be matched by an entry in addQueryVars()
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/liga/{$keytag}";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;

		// Turnier Ergebnisse
		// new structure: /ergebnisse/turnier/slug/
		$keytag = '%tournament%';
		$wp_rewrite->add_rewrite_tag($keytag, '(.+)', 'tournament='); // must be matched by an entry in addQueryVars()
		$keywords_structure = $wp_rewrite->root . UWR1RESULTS_BASEURL."/turnier/{$keytag}/";
		$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure, $ep_mask, $paged, $feed, $forcomments, $walk_dirs, $endpoints);
		$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;

		return $wp_rewrite->rules;

		//    $my_rw_rules = array();
		/*
		$my_rw_rules = array(
		$wp_rewrite->root . UWR1RESULTS_BASEURL.'/(ergebnisse)/(.*?)/?$' => 'index.php'
		. '?plugin=uwr1results'
		. '&view='.$wp_rewrite->preg_index(1)
		. '&league='.$wp_rewrite->preg_index(2),
		);
		*/
		//		return $my_rw_rules + $wp_rewrite->rules;
	}

} // Uwr1resultsHelper
