<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'cached_widget.class.php';
require_once 'uwr1res_config.inc.php';
require_once 'uwr1results_view.class.php';

class Uwr1resultsWidget extends CachedWidget {
	private static $staticCacheKey = 'cache.widget.uwr1results';

	function Uwr1resultsWidget() {
		$this->WP_Widget('Uwr1results', __('Uwr1results'), array());
		$this->m_cacheKey = self::$staticCacheKey;
	}

	/*
	static function Init() {
		if (!function_exists('register_sidebar_widget')) {
			return;
		}
		register_sidebar_widget(__('UWR Ergebnisse'), array('Uwr1resultsWidget', 'show'), 'widget-uwr1results');
		//register_widget_control(__('Kalenter'), array('Uwr1resultsWidget', 'controlPanel'), 300, 90);
	}
	*/

	/* ClearCache my be called statically by a WP hook */
	static function ClearCache() {
		delete_option(self::$staticCacheKey);
	}

	function widget($instance = '', $args = '') {
		Uwr1resultsWidget::ClearCache();
		$this->show($args, $instance);
	}
	
	function show($args, $instance) {
		extract($args);
		extract($instance);
		// load global configuration or default values
		$options = get_option('uwr1results_widget');
		$title = empty($options['title']) ? __('Letzte Ergebnisse') : $options['title'];
		$days  = empty($options['days'])  ? 0 : $options['days'];
		$num   = empty($options['num'])   ? 5 : $options['num'];
		$detailed = false;
		// local overrides
		if (@$args['days']) $days = $args['days'];
		if (@$args['num']) $num = $args['num'];
		if (@$instance['title']) $title = $instance['title'];
		if (@$instance['detailed']) $detailed = $instance['detailed'];

		// data args, known ones cleaned
		$args['days']     = $days;
		$args['num']      = $num;

		// display args, known ones cleaned
		$instance['title']    = $title;
		$instance['detailed'] = $detailed;

		$resultsData = $this->CacheLookup($args);

		if (!$resultsData) {
			$resultsData = (object) Uwr1resultsModelResult::instance()->findRecentResults2(array('days' => $days, 'num' => $num));

			$expires = 0;
			if (FALSE && 'days' == $resultsData['limit']) {
				list($y, $m, $d) = explode('-', date('Y-m-d'));
				$d++;
				$exp_date = "{$y}-{$m}-{$d} 00:00:00";
				$expires = strtotime($exp_date);
			}
			$this->StoreInCache($args, $resultsData, $expires);
		}

		print $this->GenerateOutput($resultsData, $args, $instance);
	} // show

	function GenerateOutput(&$resultsData, $args, $instance) {
		extract($args);
		extract($instance);
		$linkInTitle = @$instance['link_in_title'];

		if ($linkInTitle) {
			if ('days' == $resultsData->limit) { $title .= ' (letzte '.$days.' Tage)'; }
			if ('num' == $resultsData->limit ) { $title .= ' (letzte '.$num.' Ergebnisse)'; }
			$title .= ' &mdash; <a class="uwr1results-icon" href="'.Uwr1resultsView::indexUrl().'">'.__('Alle Liga-Ergebnisse').'&nbsp;&raquo;</a>';
		}

		print str_replace('id="uwr1results"', 'id="uwr1results-widget"', $before_widget);
		print $before_title . $title . $after_title;

		if ('OK' != $resultsData->status) {
			// 'OK" != $resultsData['status']
			print $this->NoDataAvailable();
		} else {
			// 'OK" == $resultsData['status']
			if ($detailed) {
				print '<div class="widget_recent_results">';
			} else {
				print '<table cellspacing="0" class="widget_recent_results">';
			}
			//$b1 = ($detailed ? '<b>' : '');
			//$b2 = ($detailed ? '</b>' : '');
			foreach($resultsData->result as $r) {
				if ($detailed) {
					$user_info = get_userdata($r->user_ID);
					print '<div class="res_entry">'
							//.'<div class="date">'.date("d.m.", strtotime($r->result_modified)) . '</div>'
							. '<div class="teams">'
								. '<a href="'.Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID).'#fid='.$r->fixture_ID.'" class="uwr1results_widget uwr1results-icon">'
								. $r->team_b_name . ' &mdash; ' . $r->team_w_name
								. '</a>'
							. '</div>'
							. '<div class="goals">'
								.'<b>'.$r->result_goals_b.'</b>'.' : '.'<b>'.$r->result_goals_w.'</b>'
							.'</div>'
							.'<div class="league">'
								.str_replace(' ', '&nbsp;', /*str_replace(array('Nord', 'Süd', 'West'), array('N', 'S', 'W'),*/ $r->league_short_name/*)*/)
							.'</div>'
							.'<div class="user">von '
							// TODO: create profile link
							.self::GetAuthorLink($r->user_ID)
							//.$user_info->display_name
							.' am '
							.date("d.m.", strtotime($r->result_modified))
							.'</div>'
						. '</div>';
				} else {
					print '<tr>'//.print_r($r, true)
						. '<td>'
						. '<a href="'.Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID).'#fid='.$r->fixture_ID.'" class="uwr1results_widget uwr1results-icon">'
						. $r->team_b_name . ' &mdash; ' . $r->team_w_name
						. '</a>&nbsp;'
						. '</td>'
						. '<td align="center">'.$b1.$r->result_goals_b.$b2.'</td>'
						. '<td>:</td>'
						. '<td align="center">'.$b1.$r->result_goals_w.$b2.'</td>' 
						. '</tr>';
					}
			}
			if ($detailed) {
				// TODO: Add link if !$linkInTitle
				print '</div>';
			} else {
				if (!$linkInTitle) {
					print '<tr>'
						. '<td colspan="3">'
						. '<a class="uwr1results-icon" href="'.Uwr1resultsView::indexUrl().'">'.__('Alle Liga-Ergebnisse').'</a>'
						. '</td>'
						. '</tr>';
				}
				print '</table>';
			}
		}
		print $after_widget;
	}
	
	function NoDataAvailable() {
		$season = Uwr1resultsController::season();
		$season = $season.'/'.($season+1); // TODO: make a function for that
		return '<table><tr><td>'
			. 'Es sind noch keine Ergebnisse f&uuml;r die Saison ' . $season . ' vorhanden.<br />'
			. 'Falls Du welche weisst kannst Du sie <a href="http://uwr1.de/ergebnisse">hier eintragen</a>.'
			. '</td></tr></table>';
	}

	/**
	* Wrapper for WP author data
	* TODO: Make it work for anon comment-posters, too
	*/
	function GetAuthorLink( $user_id ) {
		if (!absint($user_id)) {
			return '';
		}
		global $authordata;
		$authordata = get_userdata( $user_id );
		return get_the_author();

		/* from: author-template.php::the_author_posts_link() */
/*
		return sprintf(
			'<a href="%1$s" title="%2$s">%3$s</a>',
			get_author_posts_url( $authordata->ID, $authordata->user_	),
			esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ),
			get_the_author()
		);
*/
	}

	function controlPanel() {
/*
		$options = $newoptions = get_option('uwr1results_widget');
		if ( $_POST['uwr1results-submit'] ) {
			$newoptions['num'] = strip_tags(stripslashes((int)$_POST['uwr1results-num']));
			$newoptions['title'] = strip_tags(stripslashes($_POST['uwr1results-title']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('uwr1results_widget', $options);
		}
		$title = attribute_escape($options['title']);
		$num = attribute_escape((int)$options['num']);
		?>
				<p><label for="uwr1results-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="uwr1results-title" name="uwr1results-title" type="text" value="<?php echo $title; ?>" /></label></p>
				<p><label for="uwr1results-num"><?php _e('Number of events:'); ?> <input style="width: 250px;" id="uwr1results-num" name="uwr1results-num" type="text" value="<?php echo $num; ?>" /></label></p>
				<input type="hidden" id="uwr1results-submit" name="uwr1results-submit" value="1" />
		<?php
*/
	}
}

// Register widget
add_action('widgets_init', create_function('', 'return register_widget("Uwr1resultsWidget");'));

// Invalidate cache whenever a match result has been changed
    // the action 'uwr1results_cache_invalidated' whenever a match result was added/edited/deleted.
	add_action('uwr1results_cache_invalidated', array('Uwr1resultsWidget', 'ClearCache'));
