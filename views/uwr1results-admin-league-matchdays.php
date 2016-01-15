<?php
/*
Template Name: uwr1results Edit Matchdays View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

$leagueId = $_GET['league_id']; //Uwr1resultsView::viewVar('leagueId');

$league =& Uwr1resultsModelLeague::instance();
$league->findById($leagueId);
if ( !$league->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}

$matchdays =& Uwr1resultsModelMatchday::instance()->findByLeagueId( Uwr1resultsModelLeague::instance()->id() ); // getMatchdays($league /* [$season,$tournament] */);


/**
 * Display code for matchday results editing
 */

// open form & headline
print '<form name="quoteform" id="quoteform" class="wrap" method="post" action="'
	. $_SERVER['PHP_SELF']
	. '?page='.$_REQUEST['page']
	. '&action='.$_REQUEST['action']
	. '&league_id='.$leagueId
	. '">'
	. '<div class="wrap">'
	. '<h2>'.sprintf( __('Spieltage der %s bearbeiten'), $league->name() ).'</h2>';

// hidden fields
//print '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />'
//	. '<input type="hidden" name="action" value="' . $_REQUEST['action'] . '" />'
//	. '<input type="hidden" name="league_id" value="' . $_REQUEST['league_id'] . '" />';
print '<input type="hidden" name="_wp_http_referer" value="' . @$_SERVER['HTTP_REFERER'] . '" />';

print '	<div id="poststuff" class="metabox-holder has-right-sidebar">';
/*
$matchdayName = '';
if ( $matchday->leagueId() ) {
	if ( $matchday->name() ) {
		$matchdayName .= $matchday->name();
	} elseif ( $matchday->order() ) {
		$matchdayName .= $matchday->order() . '. Spieltag';
	}
	if ( $league->name() ) {
		$matchdayName .= ' der ' . $league->name();
	}
}
if ( $matchdayName ) {
	print '<div>'.$matchdayName.'</div>';
}

$dateTimeLocation = '';
if ( $matchday->date() && '0000-00-00' != $matchday->date() ) {
	$dateTimeLocation .= ' am '.$matchday->date();
}
if ( $matchday->location() ) {
	$dateTimeLocation .= ' in '.$matchday->location();
}
if ( $dateTimeLocation ) {
	print 'Ausgetragen'.$dateTimeLocation;
}
*/
// sidebar
print '<div id="side-info-column" class="inner-sidebar">'
	. '<div id="side-sortables" class="meta-box-sortables">'
		. '<div id="submitdiv" class="postbox">'
			. '<div class="handlediv" title="Klicken zum Umschalten"><br /></div>'
			. '<h3 class="hndle"><span>Speichern</span></h3>'
			. '<div class="inside">'
				. '<div class="submitbox" id="submitlink">'

	. '<div id="minor-publishing">'
	. '<div style="display:none;">'
		. '<input type="submit" name="save" value="'.__('Save').'" />'
	. '</div>'
	. '<div id="minor-publishing-actions">'
		. '<div id="preview-action" class="misc-pub-section">'
			. '<a class="preview button" href="'.Uwr1resultsView::resultsPageUrl($league).'" target="_blank" tabindex="4">'.$league->name().' ansehen</a>'
			. '<div class="clear"></div>'
		. '</div>'
	. '</div>'

	. '<div id="misc-publishing-actions">'
		. '<div class="misc-pub-section misc-pub-section-last">'
			. '<a class="button" href="admin.php?page='.$_REQUEST['page'].'">Ergebnisdienst&mdash;Startseite</a>'
			. '<div class="clear"></div>'
		. '</div>'
	. '</div>'

	. '</div>' // #minor-publishing

	. '<div id="major-publishing-actions">'
		. '<div id="publishing-action">'
			. '<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="Spieltage speichern" />'
		. '</div>'
		. '<div class="clear"></div>'
	. '</div>' // #major-publishing-actions
	. '<div class="clear"></div>'

	. '</div>' // class="submitbox" id="submitlink"
			. '</div>' // class="inside"
		. '</div>' // id="linksubmitdiv" class="postbox"
	. '</div>' // id="side-sortables" class="meta-box-sortables"
. '</div>'; // id="side-info-column" class="inner-sidebar"
/*
print '<div class="submitbox" id="submitlink">'
	. '<p class="submit"><input type="submit" class="button-primary" value="'.__('Save').'"/></p>'
	. '<div class="side-info">'
	. '<h5>'.__('Related').'</h5>'
	. '<ul>'
	. '<li><a href="'.Uwr1resultsView::resultsPageUrl($league).'">'.$league->shortName().' ansehen</a></li>'
//	. '<li><a href="admin.php?page='.$_REQUEST['page'].'&action=edit_matchday_results&matchday_id='.$matchdayId.'">Ergebnisse eintragen</a></li>'
	. '<li><a href="admin.php?page='.$_REQUEST['page'].'">Ergebnisse eintragen &mdash; Startseite</a></li>'
	. '</ul>'
	. '</div>'
	. '</div>';
*/

// form content
print '<div id="post-body" class="has-sidebar">'
	. '<div id="post-body-content" class="has-sidebar-content">'
	. '<table class="widefat">'
	. '<thead><tr>'
	. '<th class="manage-column">Spieltag</th>'
	. '<th class="manage-column">Datum<br>(z.B.&nbsp;2008-12-31)</th>'
	. '<th class="manage-column">Ort</th>'
	. '<th class="manage-column">Aktionen</th>'
	. '</tr></thead>';
$matchdayNumber = 0;
$alternate = false;
foreach ($matchdays as $m) {
	$editLink = Uwr1resultsView::editLink(array(
		'action' => 'edit_matchday_fixtures',
		'matchday_id' => $m->matchday_ID,
	));
	$alternate = !$alternate;
	print '<tr'.($alternate ? ' class="alternate"' : '').'>'
		. '<th>' . ++$matchdayNumber . '.&nbsp;Spieltag:</th>'
		. '<td>'
		. '<input type="hidden" name="data['.$m->matchday_ID.'][id]" value="'.$m->matchday_ID.'" />'
		. '<input type="hidden" name="data['.$m->matchday_ID.'][order]" value="'.$m->matchday_order.'" />'
		. '<input class="auto-team" name="data['.$m->matchday_ID.'][date]" value="'.$m->matchday_date.'" size="10" />'
		. '</td>'
		. '<td>'
		. '<input class="auto-team" name="data['.$m->matchday_ID.'][location]" value="'.$m->matchday_location.'" size="11" />'
		. '</td>'
		. '<td><a style="font-weight:normal;" href="'.$editLink.'"><img src="/bilder/icons/pencil.png" height="16" width="16" alt="" /> Spielpaarungen</a></td>'
		. '</tr>';
}
print '</table>'
	. '<div style="margin-top:1em;">'
		. '<img src="http://'.$_SERVER['HTTP_HOST'].'/bilder/icons/add.png" alt="+" style="vertical-align:middle;margin-bottom:3px;" /> <input class="input" name="numAdd" id="numAdd" value="1" size="1" /> <button class="button" onclick="addInputs(); return false;">Spieltage hinzufügen</button>'
	. '</div>'
	. '</div>' // id="post-body-content" class="has-sidebar-content"
	. '</div>'; // id="post-body" class="has-sidebar"
?>
<script type="text/javascript">
// <![CDATA[
var noticeAdded = false;
function addNotice() {
	if (!noticeAdded) {
		noticeAdded = true;
		jQuery('table.widefat').append(
			jQuery('<tr><td colspan="4" style="background-color:#ffebe8; border-top:2px solid #d54e21;"><strong>Achtung:</strong> Die Einträge unterhalb wurden noch nicht gespeichert.</td></tr>')
			);
	}
}
var newInputId = <?php print $matchdayNumber; ?>;
function addInputs() {
	addNotice();
	var numAdd = jQuery('#numAdd').val();
	if (numAdd < 1) {
		return false;
	}
	var tbl = jQuery('table.widefat');
	for (i=0; i<numAdd; ++i) {
		++newInputId;
		tbl.append(jQuery(
			'<tr><th>Neuer Spieltag (#' + newInputId + '):</th>'
			+ '<td><input type="hidden" name="data[' + newInputId + '][order]" value="' + newInputId + '" />'
			+ '<input name="data[' + newInputId + '][date]" value="" /></td>'
			+ '<td><input name="data[' + newInputId + '][location]" value="" /></td>'
			+ '<td>erst speichern</td>'
			+ '</tr>'
			));
	}
}
// ]]>
</script>
<?php
print '</div>'; // #poststuff.metabox-holder

print '</form>';
?>


<br class="clear" />
</div>