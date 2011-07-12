<?php
/*
Template Name: uwr1results Edit Single Result View
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

$fixtureId = $_REQUEST['fixture_id'];

$fixture =& Uwr1resultsModelFixture::instance()->findById( $fixtureId );
if ( !$fixture->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$matchday =& Uwr1resultsModelMatchday::instance()->findById( $fixture->matchdayId() );
if ( !$matchday->found() ) {
	new Uwr1resultsException('Spieltag nicht gefunden.');
}
$league =& Uwr1resultsModelLeague::instance()->findById( $matchday->leagueId() );
if ( !$league->found() ) {
	new Uwr1resultsException('Liga nicht gefunden.');
}

$matchdayId = $matchday->id();



/**
 * Display code for single result editing
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
	. '&fixture_id='.$_REQUEST['fixture_id']
	. '">'
	. '<div class="wrap">'
	. '<h2>'.__('Ergebnis des Spiels ') . $fixture->blueName() . __(' vs. ') . $fixture->whiteName() . __(' eintragen') . '</h2>';

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
if ($fixture->date() && '0000-00-00' != $fixture->date()) {
	$dateTimeLocation .= ' am '.$fixture->date();
}
if ($fixture->time() && '00:00:00' != $fixture->time()) {
	$dateTimeLocation .= ' um '.$fixture->time();
}
if ($fixture->location()) {
	$dateTimeLocation .= ' in '.$fixture->location();
}
if ($dateTimeLocation) {
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
			. '<a class="preview button" href="' . Uwr1resultsView::resultsPageUrl($league) . '" target="_blank" tabindex="4">'.$league->name().' ansehen</a>'
			. '<div class="clear"></div>'
		. '</div>'
	. '</div>'

	. '<div id="misc-publishing-actions">'
		. '<div class="misc-pub-section misc-pub-section-last">'
			. '<a class="button" href="admin.php?page='.$_REQUEST['page'].'&action=edit_matchday_results&matchday_id='.$matchdayId.'">Ergebnisse des Spieltages bearbeiten</a>'
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
			. '<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="Ergebnis speichern" />'
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
	. '<p class="submit"><input type="submit" class="button" value="'.__('Save').'"/></p>'
	. '<div class="side-info">'
	. '<h5>'.__('Related').'</h5>'
	. '<ul>'
//	. '<li><a href="'.Uwr1resultsView::resultsPageUrl($league).'">'.$league->shortName().' ansehen</a></li>'
	. '<li><a href="admin.php?page='.$_REQUEST['page'].'">Ergebnisse eintragen &mdash; Startseite</a></li>'
	. '</ul>'
	. '</div>'
	. '</div>';
*/

// form content
print '<div id="post-body" class="has-sidebar">'
	. '<div id="post-body-content" class="has-sidebar-content">'
	. '<table class="widefat fixed">'
	. '<thead><tr>'
	. '<th class="manage-column">Blau</th>'
	. '<th class="manage-column">Weiß</th>'
	. '<th class="manage-column">Kommentar</th>'
	. '</tr></thead>';
	$r =& $fixture->result();
	$comment = $goalsBlue = $goalsWhite = '';
	if ( $r->hasProperty('goalsBlue') && $r->goalsBlue() >= 0 ) {
		$goalsBlue =& $r->goalsBlue();
	}
	if ( $r->hasProperty('goalsWhite') && $r->goalsWhite() >= 0 ) {
		$goalsWhite =& $r->goalsWhite();
	}
	if ( $r->hasProperty('comment') ) {
		$comment = $r->comment();
	}
	print '<tr class="alternate">'
		. '<td>' . $fixture->blueName() . '</td>'
		. '<td>' . $fixture->whiteName() . '</td>'
		. '<td></td>'
		. '</tr>';
	print '<tr class="alternate">'
		. '<td><input size="2" name="goalsBlue" value="'.$goalsBlue.'" /></td>'
		. '<td><input size="2" name="goalsWhite" value="'.$goalsWhite.'" /></td>'
		. '<td><input name="comment" value="'.$comment.'" /></td>'
		. '</tr>';
print '</table>'
	. '</div>' // id="post-body-content" class="has-sidebar-content"
	. '</div>'; // id="post-body" class="has-sidebar"

print '</div>'; // #poststuff.metabox-holder

print '</form>';
?>


<br class="clear" />
</div>