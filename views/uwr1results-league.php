<?php
/*
Template Name: uwr1results League View
Plugin URI: http://uwr1.de/
Description: TODO
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

function print_ranking(Uwr1resultsRanking $ranking, $dbgDV = false) {
	global $printDebug;
	$league =& Uwr1resultsModelLeague::instance();

    if (0 == $ranking->numTeams()) {
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
	foreach ($ranking->head2headTeams as $pts => $teams) {
		sort($teams);
		$ranking->head2headTeams[ $pts ] = implode(', ', $teams);
	}
	$head2headComparisons = implode(' sowie ', $ranking->head2headTeams);
	foreach ($ranking->rnk as $rank) {
		$altRowClass = 1 - $altRowClass;
		if (!$rank['matchesPlayed'] && $rank['friendlyMatchesPlayed']) {
			$rank['matchesPlayed'] = '('.$rank['friendlyMatchesPlayed'].'<sup><a href="#fn-F">F</a></sup>)';
		}
		print '<tr'.($altRowClass ? ' class="alt"' : '').'>'
			. '<td>'.sprintf('%02d', ++$r).(@$rank['head2head'] ? '<abbr style="border:none;" title="Direkter Vergleich mit '.$ranking->head2headTeams[$rank['pointsPos']].'">*</abbr>' : '').'</td>'
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
	print '<div class="notes">';
	if ($ranking->hasHead2HeadSituations()) {
		if ($ranking->useDV) {
			print '* <strong>Achtung:</strong> Du benutzt den Direkten Vergleich (beta).'
				.' <a href="/kontakt">Fehler/Probleme melden</a>.'
				.' (<a href="?dv=0">Dir. Vergl. abschalten</a>)<br />';
			print 'Direkter Vergleich berücksichtigt zwischen '.$head2headComparisons.'.<br />';
		} else {
			print '* <strong>Achtung:</strong> Direkter Vergleich zwischen '.$head2headComparisons.' wird in der hier angezeigten Tabelle nicht beachtet!';
			print ' <a href="?dv=1">Probiere den Direkten Vergleich aus</a> (beta).<br />';
		}
	}
	print 'Sortierung: Punkte, direkter Vergleich'
			. ($ranking->hasHead2HeadSituations()
				? ($ranking->useDV ? '' : ' (wird in der hier angezeigten Tabelle nicht beachtet)')
				: '')
			. ', Tordifferenz, positive Tore.<br />'
		. $league->notes().'<br />'
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

global $league;
global $printDebug;

$league =& Uwr1resultsModelLeague::instance();
if ( !$league->found() ) {
	new Uwr1resultsException('Liga nicht gefunden.');
}

$printDebug = (1 == $GLOBALS['current_user']->ID);
//<div class="primary" id="content">
?>
	<div id="league_page" class="uwr1results-view wrap_content has_sidebar">
	<div class="post-v2">
	<?php
	$season = Uwr1resultsController::season();
	$season = $season.'/'.($season+1); // FIXME: use a function to do that

	$title = Uwr1resultsView::title('');
	print '<h1 class="entry-title">'
		.'<a href="'.get_permalink().'" rel="bookmark" title="Permanenter Link zu '.$title.'">'
		.$title.'</a></h1>';

	// breadcrumbs
	print '<div id="breadcrumbs">Du bist hier: <a href="'.Uwr1resultsView::indexUrl().'" title="Unterwasserrugby Ergebnisse">UWR Ergebnisse</a> &raquo; '
//		. Uwr1resultsModelRegion::instance()->name()
//		. ' &raquo; '
		. $league->shortName().'</div><br />';

    $useDV = ! (1 == @$_GET['nodv']);
    print '<p class="notice"><strong>Neu: Direkter Vergleich (beta).</strong>';
    if ($useDV) {
        print ' In den Tabellen wird jetzt bei Punktgleichstand der Direkte Vergleich ausgewertet.'
            . ' Nach einer Testphase wird der Direkte Vergleich ab sofort für alle Benutzer aktiviert.'
            . ' [<a href="?nodv=1">abschalten</a>]';
    } else {
        print ' Du hast den Direkten Vergleich deaktiviert.'
            . ' Ich würde mich freuen <a href="/kontakt">zu hören</a>, warum.'
            . ' Gab es einen Fehler oder ein Problem mit der neuen Funktion?'
            . ' [<a href="?nodv=0">DV wieder aktivieren</a>]';
    }
    print '</p>';

    if ($useDV) {
		// direkter vergleich
		print_ranking($league->rankingDV(), true);
	} else {
		print_ranking($league->ranking());
	}

	global $currentMatchday, $fixtureNumberTotal, $fixtureNumberLocal, $printFriendlyNote;
	$currentMatchday = 0;
	$fixtureNumberTotal = 0;
	$altRowClass = 1;
	$fixtureNumberLocal = 0;
	$printFriendlyNote = false;
	$matchdays =& $league->matchdays();
	if (0 == count($matchdays)) {
		print '<p>Für die '.$league->name().' sind noch keine Spieltage eingetragen.</p>'
			. '<p>';
		if ( ! Uwr1resultsHelper::checkPermission('save') ) {
			print 'Wenn Du Dich <a href="'.wp_login_url().'">'.__('anmeldest').'</a> kannst Du mithelfen und den Spielplan bearbeiten.';
		} else {
			$editLink = Uwr1resultsView::editLink(array(
				'action' => 'edit_league_matchdays',
				'league_id' => $league->id(),
			));
			$editLinkTitle = 'Spielplan bearbeiten';
			print 'Du kannst mithelfen und den <a href="'.$editLink.'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="" /> '.$editLinkTitle.'</a>.';
		}
		print '</p>';
	} else {
		foreach ($league->results() as $m) {
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
			print '<tr'.($altRowClass ? ' class="alt"' : '').' id="fid'.$m->fixture_ID.'">'
				.'<td class="num"><a name="fid'.$m->fixture_ID.'"></a>'.sprintf('%02d', $fixtureNumberTotal).'</td>'
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
<script type="text/javascript">
jQuery(document).ready(function(){
	// find fid parameter
	var url = document.location.toString();
	if (!url.match(/#/)) return; // the URL contains no anchor
	var anchor = url.split('#')[1];
	if (!anchor) return;
	
	var fid = anchor.match(/fid=(\d+)/)[1];
	if (!fid) return; // the anchor contains no fid parameter

	var elemId = '#fid'+fid;
	var ot=jQuery(elemId).offset().top;
	jQuery('html,body').animate({scrollTop: ot - 100}, 1000);
	jQuery(elemId).effect('highlight', null, 7000);
});
</script>
<?php
get_sidebar();
get_footer();
?>