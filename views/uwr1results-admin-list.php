<?php
/*
Template Name: uwr1results Admin Index View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

print '<style>'
	. 'a.disfunct { text-decoration:line-through; }'
	. '</style>';



// this should be in the controller, but it's more convenient to have it here - for now.
//Uwr1resultsModelRegion::instance()->season(/*$season*/);
$regions = Uwr1resultsModelRegion::instance()->findBySeason(/*$season*/);


/**
 * Display code for regions & leagues listing
 */

print '<div class="wrap">';
print '<h2>'.__('UWR Ligen und Ergebnisse').'</h2>';

$currentRegion = 0;
foreach($regions as $r) {
	if ($currentRegion != $r->region_ID) {
		if (0 != $currentRegion) {
			print '</ul>'; // close previous
		}
		$editLink = Uwr1resultsView::editLink(array(
			'action' => 'edit_region',
			'region_id' => $r->region_ID,
//			'season' => Uwr1resultsCont...,
			));
		print '<h3>'
			. (('Turniere' != $r->region_name && 'Jugend' != $r->region_name) ? 'SB ' : '')
			. $r->region_name
//			. ' <small style="font-weight:normal;">[<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>]</small>'
			. '</h3>'
			.'<ul>';
		$currentRegion = $r->region_ID;
	}

	$matchdaysLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_matchdays',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$teamsLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league_teams',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$editLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_league',
		'league_id' => $r->league_ID,
//		'season' => Uwr1resultsCont...,
		));
	$viewLink = Uwr1resultsView::resultsPageUrl($r->league_slug, $r->region_ID);
	print '<li>'
		. $r->league_name
//		. '<br />'
		. ' ['
		. '<a href="'.$matchdaysLink.'">'.__('Spieltage bearbeiten').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$teamsLink.'">'.__('Mannschaften').'</a>'
//		. ', '
//		. '<a class="disfunct" href="'.$editLink.'">'.__('Edit').'</a>'
		. ', '
		. '<a href="'.$viewLink.'">'.( (UWR1RESULTS_TOURNAMENT_REGION == $r->region_ID) ? __('Turnier anzeigen') : __('Liga anzeigen') ).'</a>'
		. ']'
		. '</li>';
}
if (0 != $currentRegion) {
	print '</ul>'; // close last
}
?>

<br class="clear" />
<?php
// TODO print '<h2>'.__('Manage Tournament Results').'</h2>';
/*
?>
<table class="widefat" width="100%" cellpadding="3" cellspacing="3">
<thead>
	<tr>
		<th scope="col"><?php _e('ID') ?></th>
		<th scope="col"><?php _e('Title') ?></th>
		<th scope="col"><?php _e('Location') ?></th>
		<th scope="col"><?php _e('Homepage') ?></th>
		<th scope="col"><?php _e('Date') ?></th>
		<th scope="col"><?php _e('Owner') ?></th>
		<th scope="col" colspan="2" style="text-align:center;"><?php _e('Action') ?></th>
	</tr>
</thead>
<tbody id="the-list">
<?php
$class = '';
foreach ($events as $event) {
	$class = ($class == 'alternate') ? '' : 'alternate';
	if ($event->event_end == $event->event_begin) {
		$event->event_end = '';
	}

	$expired = false;
	if ('' != $event->event_end)  {
		$expired = ($event->event_end < $now);
	} else {
		$expired = ($event->event_begin < $now);
	}

	$author = get_userdata($event->event_author);
?>
	<tr<?php print ($class ? ' class="'.$class.'"' : ''); ?>>
		<th scope="row"><?php print $event->event_id; ?></th>
		<td><?php print htmlentities(str_replace('-', '- ', utf8_decode($event->event_title))); ?></td>
		<td><?php print htmlentities(utf8_decode($event->event_location)); ?></td>
		<td><?php
		if ($event->event_url) {
			$linkTitle = (strlen($event->event_url) > $linkTitleLength+7 ? substr($event->event_url, 7, $linkTitleLength).' ...' : substr($event->event_url, 7));
			print '<a href="'.htmlentities($event->event_url).'" target="_blank">'.htmlentities($linkTitle).'</a>';
		} ?></td>
		<td><?php
		if ($expired) {
			print '(';
		}
		if (!$event->event_end || $event->event_end == $event->event_begin) {
			print mysql2date( __('d.m.Y'), $event->event_begin);
		} else {
			print mysql2date( __('d.m.'), $event->event_begin).'&nbsp;-&nbsp;'.mysql2date( __('d.m.Y'), $event->event_end);
		}
		if ($expired) {
			print ')';
		} ?></td>
		<td><?php print htmlentities($author->display_name); ?></td>
		<?php
		$editLink = false;
		$deleteLink = false;
		if ( current_user_can('edit_post', $event->event_id) ) {
			$editLink = "admin.php?page=kalenter&amp;action=edit&amp;event_id={$event->event_id}";
		}
		if ( current_user_can('delete_post', $event->event_id) ) {
			$deleteLink = "admin.php?page=kalenter&amp;action=delete&amp;event_id={$event->event_id}";
			if ( function_exists('wp_nonce_url') ) {
				$deleteLink = wp_nonce_url($deleteLink, 'kalenter-delete_' . $event->event_id);
			}
		}
		print '<td>'.(false !== $editLink ? '<a href="'.$editLink.'" class="edit">'.__('Edit').'</a>' : '').'</td>';
		print '<td>'.(false !== $deleteLink ? '<a href="'.$deleteLink.'" class="delete">'.__('Delete').'</a>' : '').'</td>';
		?>
	</tr>
<?php
}
?>
</tbody>
</table>
<br class="clear" />
*/ ?>
</div>
