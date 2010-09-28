<?php
/*
Template Name: uwr1results Tournament View
Plugin URI: http://uwr1.de/
Description: TODO
Author: Hannes Hofmann
Author URI: http://uwr1.de/
Version: 0.1
*/

function print_ranking(&$ranking) {
	global $printDebug;
	$tournament =& Uwr1resultsModelLeague::instance();

	if (0 == count($ranking)) {
		return;
	}

	print '<div class="ranking">';
	print '<table cellspacing="0" class="spielplan liga">';
	print '<tr>'
		. '<th>Platz</th>'
		. '<th>Mannschaft</th>'
		. '<th>Spiele</th>'
		. '<th colspan="2">Tore</th>'
		. '<th>Punkte</th>'
//		. '<th>???</th>'
		;
	if ($printDebug) {
		print '<th>DEBUG</th>';
	}
	print '</tr>';
	$r=0;
	$altRowClass = 1;
	$head2headUsed = false;
	$head2headTeams = array();
	foreach ($ranking as $rank) {
		$altRowClass = 1 - $altRowClass;
		if ($rank['head2head']) {
			$head2headUsed = true;
			$head2headTeams[] = $rank['name'];
		}
		print '<tr'.($altRowClass ? ' class="alt"' : '').'>'
			. '<td>'.sprintf('%02d', ++$r).(@$rank['head2head'] ? '<abbr style="border:none;" title="Direkter Vergleich mit '.implode(', ', $rank['head2headTeams']).'">*</abbr>' : '').'</td>'
			. '<td>'.$rank['name'].'</td>'
			. '<td class="num">'.$rank['matchesPlayed'].'</td>'
			. '<td class="r">'.$rank['goalsDiff'].'</td>'
			. '<td>'.' <span class="detail">('.$rank['goalsPos'].' : '.$rank['goalsNeg'].')</span></td>'
			. '<td class="num">'.$rank['pointsPos'].'</td>';
//			. '<td>---</td>'
			;
		if ($printDebug) {
			print '<td>DBG</td>';
		}
		print '</tr>';
	}
	print '</table>';
	print '<div class="notes">'
		. ($head2headUsed
			? '* <strong>Achtung:</strong> Direkter Vergleich zwischen '.implode(', ', $head2headTeams).' wird in der hier angezeigten Tabelle nicht beachtet!<br />'
			: '')
		. 'Sortierung: Punkte, direkter Vergleich'
			. ($head2headUsed
				? ''
				: ' (wird in der hier angezeigten Tabelle nicht beachtet)')
			. ', Tordifferenz, positive Tore.<br />'
		. $tournament->notes().'<br />'
		. '</div>'
//	print '<a class="notes-link" href="#anmerkungen">Anmerkungen zur Tabelle</a>'
		. '</div>';
}

function end_previous_matchday() {
	global $currentMatchday;
	if (0 == $currentMatchday) {
		// no previous matchday
		return;
	}
	print '</table>';
	print '</div>'; // #matchday
}

get_header();

global $tournament;
global $printDebug;

$tournament =& Uwr1resultsModelLeague::instance();
if ( !$tournament->found() ) {
	new Uwr1resultsException('Turnier nicht gefunden.');
}

$printDebug = (1 == $GLOBALS['current_user']->ID);
//<div class="primary" id="content">
?>
	<div id="tournament_page" class="wrap_content">
	<div class="post">
	<p class="update"><strong>Achtung:</strong> Dieser Teil von <a href="Unterwasserrugby">uwr1.de</a> befindet sich noch in der Entwicklung. Es kann deshalb passieren, dass noch Fehler auftreten.</p>
	<?php
	$title = Uwr1resultsView::title('');
	print '<h2 class="posttitle">'
		.'<a href="'.get_permalink().'" rel="bookmark" title="Permanenter Link zu '.$title.'">'
		.$title.'</a></h2>';
	
	// breadcrumbs
	print '<div id="breadcrumbs">Du bist hier: <a href="'.Uwr1resultsView::indexUrl().'" title="Unterwasserrugby Ergebnisse">UWR Ergebnisse</a> &raquo; '
//		. Uwr1resultsModelRegion::instance()->name()
//		. ' &raquo; '
		. $tournament->shortName().'</div><br />';

	$matchdays =& $tournament->matchdays();

	if (count($matchdays) > 0) {
		$t_date       = '';
		$t_location   = '';
		$t_first_date = null;
		$t_last_date  = null;
		$t_locations  = array();

		foreach ($matchdays as $m) {
			if (null == $t_first_date || $m->matchday_date < $t_first_date) {
				$t_first_date = $m->matchday_date;
			}
			if (null == $t_last_date || $m->matchday_date > $t_last_date) {
				$t_last_date = $m->matchday_date;
			}
			$t_locations[] = $m->matchday_location;
		}

		if ($t_first_date == $t_last_date) {
			$t_date = 'am ' . Uwr1resultsView::mysqlToFullDate($t_first_date);
		} else {
			$t_date = 'vom ' . Uwr1resultsView::mysqlToFullDate($t_first_date) . ' bis zum ' . Uwr1resultsView::mysqlToFullDate($t_last_date);
		}

		$t_locations = array_unique($t_locations);
		if (1 == count($t_locations)) {
			$t_location = $t_locations[0];
		} else {
			$t_last_loc = array_pop($t_locations);
			$t_location = implode(', ', $t_locations);
			$t_location .= ' und ' . $t_last_loc;
		}

		print '<div>Ausgetragen ' . $t_date . ' in ' . $t_location . '.<br /><br /></div>';
	}

	print_ranking($tournament->ranking());

	global $currentMatchday, $fixtureNumberTotal, $fixtureNumberLocal, $printFriendlyNote;
	$currentMatchday = 0;
	$fixtureNumberTotal = 0;
	$altRowClass = 1;
	$fixtureNumberLocal = 0;
	$printFriendlyNote = false;
	if (0 == count($matchdays)) {
		print '<p>Für die '.$tournament->name().' sind noch keine Spieltage eingetragen.</p>'
			. '<p>';
		if ( ! Uwr1resultsHelper::checkPermission('save') ) {
			print 'Wenn Du Dich <a href="'.wp_login_url().'">'.__('anmeldest').'</a> kannst Du mithelfen und den Spielplan bearbeiten.';
		} else {
			$editLink = Uwr1resultsView::editLink(array(
				'action' => 'edit_league_matchdays', // TODO: tournament?
				'league_id' => $tournament->id(),    // TODO: tournament?
			));
			$editLinkTitle = 'Spielplan bearbeiten';
			print 'Du kannst mithelfen und den <a href="'.$editLink.'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="" /> '.$editLinkTitle.'</a>.';
		}
		print '</p>';
	} else {
		foreach ($tournament->results() as $m) {
			if ($m->matchday_order != $currentMatchday) {
				// begin new matchday
				end_previous_matchday();
	
				$currentMatchday = $m->matchday_order;
	
				print '<div class="matchday">';
				
				print '<div class="md-header">';
					print '<div class="md-title"><h4>'
						. $m->matchday_order . '. Spieltag am ' . Uwr1resultsView::mysqlToFullDate($m->matchday_date) . ' in ' . $m->matchday_location
						. '</h4></div>';
					print ' <div class="md-edit-link">';
					if ( Uwr1resultsHelper::checkPermission('save') ) {
						if (0 == $matchdays[ $currentMatchday ]->fixture_count) {
							$editLink = Uwr1resultsView::editLink(array(
								'action' => 'edit_matchday_fixtures',
								'matchday_id' => $m->matchday_ID,
							));
							$editLinkTitle = 'Spielpaarungen eintragen';
						} else {
							$editLink = Uwr1resultsView::editLink(array(
								'action' => 'edit_matchday_results',
								'matchday_id' => $m->matchday_ID,
							));
							$editLinkTitle = 'Ergebnisse bearbeiten';
						}
						print '<a href="'.$editLink.'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="" /> '.$editLinkTitle.'</a>';
					} else {
						print '<a href="'.wp_login_url().'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="' . __('Log in') . '" /> '.__('Log in').'</a>';
					}
					print '</div>';
					print '<br class="clear" />';
				print '</div>';
	
				print '<table cellspacing="0" class="spielplan liga">';
				$ths = array(
					'Spiel',
					'Blau',
					'Weiß',
					'Ergebnis',
					'Kommentar',
					'von',
				);
				if ( Uwr1resultsHelper::checkPermission('save') ) {
					$ths[] = __('Actions'); // Actions
				}
				if ($printDebug) {
					$ths[] = 'DEBUG';
				}
				print '<tr><th>' . implode('</th><th>', $ths) . '</th></tr>';
	
				$altRowClass = 1;
				$fixtureNumberLocal = 0;
			} // beginning of matchday
	
			if (!$m->fixture_ID) { continue; }
	
			if (is_null($m->result_goals_b) || is_null($m->result_goals_w)) {
				$m->result_goals_b = '--';
				$m->result_goals_w = '--';
			}
			$blue['goals'] =& $m->result_goals_b; 
			$blue['class'] = '';
			$white['goals'] =& $m->result_goals_w;
			$white['class'] = '';
			if ($blue['goals'] > $white['goals']) {
				$blue['class'] = ' class="win"';
			}
			if ($blue['goals'] < $white['goals']) {
				$white['class'] = ' class="win"';
			}
			$author = get_userdata( $m->user_ID );
			// print fixture
			$altRowClass = 1 - $altRowClass;
			++$fixtureNumberLocal;
			++$fixtureNumberTotal;
			if ($m->fixture_friendly) {
				$printFriendlyNote = true;
			}
			print '<tr'.($altRowClass ? ' class="alt"' : '').'>'
				.'<td class="num">'.sprintf('%02d', $fixtureNumberTotal).'</td>'
				.'<td'.$blue['class'].">{$m->t_b_name}</td>"
				.'<td'.$white['class'].">{$m->t_w_name}</td>"
				.'<td class="ergebnis">'
				.($m->fixture_friendly ? '(' : '')
				."{$blue['goals']} : {$white['goals']}"
				.($m->fixture_friendly ? ')<sup><a href="#fn-F">F</a></sup>' : '')
				.'</td>'
				."<td>{$m->result_comment}</td>"
				."<td>{$author->display_name}</td>"
				;
			if ( Uwr1resultsHelper::checkPermission('save') ) {
				$editLink = Uwr1resultsView::editLink(array(
					'action' => 'edit_result',
					'fixture_id' => $m->fixture_ID,
					));
				print '<td align="center">'
					. '<a href="'.$editLink.'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="' . __('Edit') . '" /></a>'
					. '</td>';
			}
			if ($printDebug) {
				$debug = '<td>'
					. '[Fx='.$m->fixture_ID.'] '
					. '[ + - ] '
					. '</td>';
				print $debug;
			}
			print '</tr>';
		}
		end_previous_matchday();

		if ($printFriendlyNote) {
			print '<div class="notes"><a name="fn-F"></a><sup>F</sup>: Freundschaftsspiel</div>';
		}
	}
	?>
	</div>

	<?php Uwr1resultsView::poweredBy(); ?>
</div>
<?php
get_sidebar();
get_footer();
?>