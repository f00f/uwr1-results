<?php
/*
Template Name: uwr1results Edit Fixtures View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

$matchdayId = Uwr1resultsView::viewVar('matchdayId');

$matchday =& Uwr1resultsModelMatchday::instance()->findById( $matchdayId );
if ( !$matchday->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$league =& Uwr1resultsModelLeague::instance()->findById($matchday->leagueId() /*, $season*/);
if ( !$league->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$fixtures =& Uwr1resultsModelFixture::instance()->findByMatchdayId( $matchday->id() );
//if ( count($fixtures) <= 1 ) {
//	new Uwr1resultsException('Spieltag nicht gefunden.');
//}



/**
 * Display code for matchday results editing
 */

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

// open form & headline
print '<form name="quoteform" id="quoteform" class="wrap" method="post" action="'
	. $_SERVER['PHP_SELF']
	. '?page='.$_REQUEST['page']
	. '&action='.$_REQUEST['action']
	. '&matchday_id='.$matchdayId
	. '">'
	. '<div class="wrap">'
	. '<h2>'.__('Spielpaarungen bearbeiten').' &ndash; '.$matchdayName.'</h2>';

// hidden fields
//print '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />'
//	. '<input type="hidden" name="action" value="' . $_REQUEST['action'] . '" />'
//	. '<input type="hidden" name="league_id" value="' . $_REQUEST['league_id'] . '" />';
print '<input type="hidden" name="_wp_http_referer" value="' . @$_SERVER['HTTP_REFERER'] . '" />';

print '	<div id="poststuff" class="metabox-holder has-right-sidebar">';

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
			. '<a class="button" href="edit.php?page='.$_REQUEST['page'].'&action=edit_matchday_results&matchday_id='.$matchdayId.'">Ergebnisse eintragen</a>'
			. '<div class="clear"></div>'
		. '</div>'
		. '<div class="misc-pub-section misc-pub-section-last">'
			. '<a class="button" href="edit.php?page='.$_REQUEST['page'].'">Ergebnisdienst&mdash;Startseite</a>'
			. '<div class="clear"></div>'
		. '</div>'
	. '</div>'

	. '</div>' // #minor-publishing

	. '<div id="major-publishing-actions">'
		. '<div id="publishing-action">'
			. '<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="Spielpaarungen speichern" />'
		. '</div>'
		. '<div class="clear"></div>'
	. '</div>' // #major-publishing-actions
	. '<div class="clear"></div>'

	. '</div>' // class="submitbox" id="submitlink"
			. '</div>' // class="inside"
		. '</div>' // id="linksubmitdiv" class="postbox"
	. '</div>' // id="side-sortables" class="meta-box-sortables"
. '</div>'; // id="side-info-column" class="inner-sidebar"

// form content
print '<div id="post-body" class="has-sidebar">'
	. '<div id="post-body-content" class="has-sidebar-content">'
	. '<table class="widefat fixed">'
	. '<thead><tr>'
	. '<th class="manage-column">Spiel Nr.</th>'
	. '<th class="manage-column">Blau</th>'
	. '<th class="manage-column">Weiß</th>'
	. '<th class="manage-column">Freundschaftsspiel</th>'
	. '</tr></thead>';
//print_r($fixtures);
$fixtureNumberMatchday = 0;
$alternate = false;
foreach ($fixtures as $f) {
	$alternate = !$alternate;
	print '<tr'.($alternate ? ' class="alternate"' : '').'>'
		. '<th>Spiel ' . ++$fixtureNumberMatchday . ':</th>'
		. '<td>'
		. '<input type="hidden" name="data['.$f->fixture_ID.'][id]" value="'.$f->fixture_ID.'" />'
		. '<input type="hidden" name="data['.$f->fixture_ID.'][order]" value="'.$f->fixture_order.'" />'
		. '<input class="auto-team" name="data['.$f->fixture_ID.'][team_blue]" value="'.$f->t_b_name.'" />'
		. '</td>'
		. '<td>'
		. '<input class="auto-team" name="data['.$f->fixture_ID.'][team_white]" value="'.$f->t_w_name.'" />'
		. '</td>'
		. '<td>'
		. '<input id="data['.$f->fixture_ID.'][friendly]" name="data['.$f->fixture_ID.'][friendly]" type="checkbox" '.($f->fixture_friendly ? 'checked="checked" ' : '').'/> <label for="data['.$f->fixture_ID.'][friendly]">Freundschaftsspiel</label>'
		. '</td>'
		. '</tr>';
}
print '</table>'
	. '<div style="margin-top:1em;">'
		. '<img src="http://'.$_SERVER['HTTP_HOST'].'/bilder/icons/add.png" alt="+" style="vertical-align:middle;margin-bottom:3px;" /> <button class="button" onclick="addInputs(); return false;">Spielpaarung hinzufügen</button>'
	. '</div>'
	. '</div>' // id="post-body-content" class="has-sidebar-content"
	. '</div>'; // id="post-body" class="has-sidebar"
?>
<script type="text/javascript" src="<?php bloginfo('wpurl'); ?>/wp-includes/js/jquery/suggest.js"></script> 
<script type="text/javascript">
// <![CDATA[
//var newFixtureId = <?php print $fixtureNumberMatchday; ?>;
function addSuggest(elem) {
	elem.suggest( '<?php print Uwr1resultsView::ajaxUrl( 'search-teams' ); ?>', { delay: 200, minchars: 1 } );
}
var noticeAdded = false;
function addNotice() {
	if (!noticeAdded) {
		noticeAdded = true;
		jQuery('table.widefat').append(
			jQuery('<tr><td colspan="4" style="background-color:#ffebe8; border-top:2px solid #d54e21;"><strong>Achtung:</strong> Die Einträge unterhalb wurden noch nicht gespeichert.</td></tr>')
			);
	}
}
var newInputId = <?php print $fixtureNumberMatchday; ?>;
function addInputs() {
	addNotice();
	var tbl = jQuery('table.widefat');
	++newInputId;
	tbl.append(jQuery(
		'<tr><th>Neues Spiel (#' + newInputId + '):</th>'
		+ '<td><input id="fx' + newInputId + 'tb" class="auto-team" name="data[' + newInputId + '][team_blue]" value="" /></td>'
		+ '<td><input id="fx' + newInputId + 'tw" class="auto-team" name="data[' + newInputId + '][team_white]" value="" /></td>'
		+ '<td><input id="fx' + newInputId + 'fs" name="data[' + newInputId + '][friendly]" type="checkbox" /> <label for="fx' + newInputId + 'fs">Freundschaftsspiel</label></td>'
		+ '</tr>'
		));
	addSuggest( jQuery('#fx' + newInputId + 'tb') );
	addSuggest( jQuery('#fx' + newInputId + 'tw') );
}
// enable auto-suggest for existing fixtures
jQuery(document).ready(function() {
	jQuery('input.auto-team').each(function(idx, elem) {
		addSuggest( jQuery(elem) );
	});
});
// ]]>
</script>
<?php
print '</div>'; // #poststuff.metabox-holder

print '</form>';
?>


<br class="clear" />
</div>