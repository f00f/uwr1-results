<?php
/*
Template Name: uwr1results Edit Result View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

$matchdayId = $_REQUEST['matchday_id'];

$matchday =& Uwr1resultsModelMatchday::instance()->findById( $matchdayId );
if ( !$matchday->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$league =& Uwr1resultsModelLeague::instance()->findById($matchday->leagueId() /*, $season*/);
if ( !$league->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$fixtures =& Uwr1resultsModelFixture::instance()->findByMatchdayId( $matchday->id() );
if ( count($fixtures) < 1 ) {
	new Uwr1resultsException('Keine Spielpaarungen für diesen Spieltag gefunden.');
}



/**
 * Display code for matchday results editing
 */

$matchdayName = '';
if ( $matchday->leagueId() ) {
	$matchdayName .= $matchday->name();
	if ( $league->name() ) {
		$matchdayName .= ' der ' . $league->name();
	}
}

// open form & headline
print '<form name="quoteform" id="quoteform" class="wrap" method="post" action="'
	. $_SERVER['PHP_SELF']
	. '?page='.$_REQUEST['page']
	. '&action='.$_REQUEST['action']
	. '&matchday_id='.$_REQUEST['matchday_id']
	. '">'
	. '<div class="wrap">'
	. '<h2>'.__('Ergebnisse eintragen').' &ndash; '.$matchdayName.'</h2>';

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
			. '<a class="button" href="admin.php?page='.$_REQUEST['page'].'&action=edit_matchday_fixtures&matchday_id='.$matchdayId.'">Spielpaarungen bearbeiten</a>'
			. '<div class="clear"></div>'
		. '</div>'
		. '<div class="misc-pub-section misc-pub-section-last">'
			. '<a class="button" href="admin.php?page='.$_REQUEST['page'].'">Ergebnisdienst&mdash;Startseite</a>'
			. '<div class="clear"></div>'
		. '</div>'
	. '</div>'

	. '</div>' // #minor-publishing

	. '<div id="major-publishing-actions">'
		. '<div id="publishing-action">'
			. '<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="Ergebnisse speichern" />'
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
	. '<th class="manage-column">Kommentar</th>'
	. '</tr></thead>';
//print_r($fixtures);
$fixtureNumberMatchday = 0;
$alternate = false;
foreach ($fixtures as $f) {
	$goalsBlue = $goalsWhite = '';
	if ( !is_null($f->result_goals_b) && $f->result_goals_b >= 0 ) {
		$goalsBlue = $f->result_goals_b;
	}
	if ( !is_null($f->result_goals_w) && $f->result_goals_w >= 0 ) {
		$goalsWhite = $f->result_goals_w;
	}
	$alternate = !$alternate;
	print '<tr'.($alternate ? ' class="alternate"' : '').'>'
		. '<th>Spiel ' . ++$fixtureNumberMatchday . ':</th>'
		. '<td>' . $f->t_b_name . '</td>'
		. '<td>' . $f->t_w_name . '</td>'
		. '<td></td>'
		. '</tr>';
	print '<tr'.($alternate ? ' class="alternate"' : '').'>'
		. '<th></th>'
		. '<td>'
		. '<input size="2" name="fx['.$f->fixture_ID.'][goals_blue]" value="'.$goalsBlue.'" />'
		. '</td>'
		. '<td>'
		. '<input size="2" name="fx['.$f->fixture_ID.'][goals_white]" value="'.$goalsWhite.'" />'
		. '</td>'
		. '<td>'
		. (('' == $f->result_comment)
			? ' <span class="showCommentField"><div class="comments">'
			: '<div>')
		. '<input name="fx['.$f->fixture_ID.'][comment]" value="'.$f->result_comment.'" /></div>'
		. (('' == $f->result_comment)
			? '</span>'
			: '')
		. '<input type="hidden" name="fx['.$f->fixture_ID.'][id]" value="'.$f->fixture_ID.'" />'
		. '</td>'
		. '</tr>';
}
print '</table>'
	. '</div>' // id="post-body-content" class="has-sidebar-content"
	. '</div>'; // id="post-body" class="has-sidebar"
?>
<script type="text/javascript">
// <![CDATA[
// hide comment fields
jQuery(document).ready(function() {
	var dummy = jQuery('<a href="javascript:void(0);" onclick="jQuery(this).siblings().show();jQuery(this).next().children().focus();jQuery(this).hide();">Kommentar hinzufügen</a>');
	jQuery('div.comments').hide();
	jQuery('span.showCommentField').prepend(
		dummy
		);
});
// ]]>
</script>
<?php
print '</div>'; // #poststuff.metabox-holder

print '</form>';
?>


<br class="clear" />
</div>
