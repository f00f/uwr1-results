<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';
require_once 'uwr1results_view.class.php';

class Uwr1resultsWidget {
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
	private function __construct() {  }
	
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
			self::$instance = new Uwr1resultsWidget();
		}
		return self::$instance;
	}

	function Init() {
		if (!function_exists('register_sidebar_widget')) {
			return;
		}
		register_sidebar_widget(__('UWR Ergebnisse'), array('Uwr1resultsWidget', 'show'), 'widget-uwr1results');
		//register_widget_control(__('Kalenter'), array('Uwr1resultsWidget', 'controlPanel'), 300, 90);
	}

	function show($args) {
		extract($args);
		// load global configuration or default values
		$options = get_option('uwr1results_widget');
		$title = empty($options['title']) ? __('Letzte Ergebnisse') : $options['title'];
		$days  = empty($options['days'])  ? 0 : $options['days'];
		$num   = empty($options['num'])   ? 5 : $options['num'];
		$detailed = false;
		// local overrides
		if (@$args['title']) $title = $args['title'];
		if (@$args['days'])  $days  = $args['days'];
		if (@$args['num'])   $num   = $args['num'];
		if (@$args['detailed']) $detailed = $args['detailed'];
		$linkInTitle = @$args['link_in_title'];

		$results = Uwr1resultsModelResult::instance()->findRecentResults(array('days' => $days, 'num' => $num));
		
		if ($linkInTitle) {
			$title .= ' ('.$days.' Tage) &mdash; <a class="uwr1results-icon" href="'.Uwr1resultsView::indexUrl().'">'.__('Alle Liga-Ergebnisse').' &raquo;</a>';
		}

		print str_replace('id="uwr1results"', 'id="uwr1results-widget"', $before_widget);
		print $before_title . $title . $after_title;

		if ($results) {
			print '<table cellspacing="0">';
			$b1 = ($detailed ? '<b>' : '');
			$b2 = ($detailed ? '</b>' : '');
			foreach($results as $r) {
				if ($detailed) {
					$user_info = get_userdata($r->user_ID);
				}
				print '<tr>'//.print_r($r, true)
					. ($detailed ? '<td>'.date("d.m.", strtotime($r->result_modified)) . ':&nbsp;</td>' : '')
					. '<td>'
					. '<a href="'.Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID).'#fid='.$r->fixture_ID.'" class="uwr1results_widget uwr1results-icon">'
					. $r->team_b_name . ' &mdash; ' . $r->team_w_name
					. '</a>&nbsp;'
					. '</td>'
					. '<td align="center">'.$b1.$r->result_goals_b.$b2.'</td>'
					. '<td>:</td>'
					. '<td align="center">'.$b1.$r->result_goals_w.$b2.'</td>' 
					. ($detailed ? 
						'<td>&nbsp;</td><td>('
						. $user_info->display_name
						. ' in '
						. $r->league_short_name
						. ')</td>'
						: '')
					. '</tr>';
			}
			if (!$linkInTitle) {
				print '<tr>'
					. '<td colspan="3">'
					. '<a class="uwr1results-icon" href="'.Uwr1resultsView::indexUrl().'">'.__('Alle Liga-Ergebnisse').'</a>'
					. '</td>'
					. '</tr>';
			}
			print '</table>';
		}
		else
		{
			// ! $results
			$season = Uwr1resultsController::season();
			$season = $season.'/'.($season+1); // TODO: make a function for that
			print '<table><tr><td>'
				. 'Es sind noch keine Ergebnisse f&uuml;r die Saison ' . $season . ' vorhanden.<br />'
				. 'Falls Du welche weisst kannst Du sie <a href="http://uwr1.de/ergebnisse">hier eintragen</a>'
				. '</td></tr></table>';
		}
		
		print $after_widget;
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

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', array('Uwr1resultsWidget', 'Init') );
?>