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
	. '<h2>'.$matchdayName.' <small>&ndash; '.__('Spielpaarungen bearbeiten').'</small></h2>';

// hidden fields
//print '<input type="hidden" name="page" value="' . $_REQUEST['page'] . '" />'
//	. '<input type="hidden" name="action" value="' . $_REQUEST['action'] . '" />'
//	. '<input type="hidden" name="league_id" value="' . $_REQUEST['league_id'] . '" />';
print '<input type="hidden" name="_wp_http_referer" value="' . @$_SERVER['HTTP_REFERER'] . '" />';

print '	<div id="poststuff" class="metabox-holder has-right-sidebar">';

print '<div id="matchday-meta" style="margin-bottom:0.5em">';
if ( $matchdayName ) {
	print $matchdayName;
}

$dateTimeLocation = '';
if ( $matchday->date() && '0000-00-00' != $matchday->date() ) {
	$dateTimeLocation .= ' am '.$matchday->date();
}
if ( $matchday->location() ) {
	$dateTimeLocation .= ' in '.$matchday->location();
}
if ( $dateTimeLocation ) {
	print ' ausgetragen'.$dateTimeLocation;
}
print '</div>';// matchday-meta

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
			. '<a class="button" href="admin.php?page='.$_REQUEST['page'].'&action=edit_matchday_results&matchday_id='.$matchdayId.'">Ergebnisse eintragen</a>'
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
	. '<table class="widefat">'
	. '<thead><tr>'
	. '<th class="manage-column">Spiel<br>Nr.</th>'
	. '<th class="manage-column">Blau</th>'
	. '<th class="manage-column">Weiß</th>'
	. '<th class="manage-column">Freundschafts<br>spiel</th>'
	. '</tr></thead>';
//print_r($fixtures);
$fixtureNumberOnMatchday = 0;
$alternate = false;
foreach ($fixtures as $f) {
	$alternate = !$alternate;
	print '<tr'.($alternate ? ' class="alternate"' : '').'>'
		. '<th>' . ++$fixtureNumberOnMatchday . ':</th>'
		. '<td>'
		. '<input type="hidden" name="data['.$f->fixture_ID.'][id]" value="'.$f->fixture_ID.'" />'
		. '<input type="hidden" name="data['.$f->fixture_ID.'][order]" value="'.$f->fixture_order.'" />'
		. '<input class="auto-team" name="data['.$f->fixture_ID.'][team_blue]" value="'.$f->t_b_name.'" size="15" />'
		. '</td>'
		. '<td>'
		. '<input class="auto-team" name="data['.$f->fixture_ID.'][team_white]" value="'.$f->t_w_name.'" size="15" />'
		. '</td>'
		. '<td>'
		. '<input id="data['.$f->fixture_ID.'][friendly]" name="data['.$f->fixture_ID.'][friendly]" type="checkbox" '.($f->fixture_friendly ? 'checked="checked" ' : '').'/> <label for="data['.$f->fixture_ID.'][friendly]">Freund.</label>'
		. '</td>'
		. '</tr>';
}
if (0 == $fixtureNumberOnMatchday) {
	print '<tr'.($alternate ? ' class="alternate"' : '').' id="placeholder-no-fixtures">'
		. '<th colspan="4">Bisher sind noch keine Spielpaarungen eingetragen. Du kannst entweder <a onclick="addInputs(); return false;" href="#">einzelne Spielpaarungen hinzufügen</a>, oder <a href="#csv-import">unten</a> mehrere auf einmal als CSV eingeben.</th>'
		. '</tr>';
}
print '</table>'
	. '<div style="margin-top:1em;">'
		. '<img src="http://'.$_SERVER['HTTP_HOST'].'/bilder/icons/add.png" alt="+" style="vertical-align:middle;margin-bottom:3px;" /> <button class="button" onclick="addInputs(); return false;">Spielpaarung hinzufügen</button>'
	. '</div>';
?>

<div id="accordion">

<h2>CSV Import</h2>
<!-- .ui-accordion-header span: show -->
<!-- .ui-accordion-header-active span: hide -->
<div>
<a name="csv-import"></a>

<p>
<strong>Hier kannst Du mehrere Spielpaarungen auf einmal im CSV Format eingeben.</strong><br />
Im einfachsten Fall kannst Du die Begegnungen aus Excel kopieren und hier einfügen.
Diese Funktion ist allerdings neu und noch wenig getestet. Viel Glück ;-) Ich würde mich <a href="/kontakt">über Feedback freuen</a>.
</p>
<p>
<strong>Ablauf</strong><br />
<ol>
<li>Spielpaarungen aus Excel kopieren und links einfügen.</li>
<li>"Vorschau" anklicken und prüfen, ob alle Spiele richtig erkannt wurden (s.a. <a href="#csv-help-format">Hinweise zum Format</a>).</li>
<li>Falls alles korrekt ist, "Übernehmen" anklicken. Die Spiele erscheinen oben in der Liste als neue Spiele.</li>
<li>"Spielpaarungen Speichern" (blauer Knopf in der rechten Seitenleiste).</li>
</ol>
</p>
<div>
	<div class="alignleft" style="width:49%">
		<textarea onchange="disableApplyCSV();" onkeydown="disableApplyCSV();" id="csv" style="width:100%" rows="10" placeholder="Hier Spielpaarungen als CSV einfügen."></textarea>
		<button class="button" onclick="showCSVPreview(); enableApplyCSV(); return false;">Vorschau</button>
	</div>
	<div class="alignright" style="width:49%">
		<div id="preview-csv" style="height:15em;border:1px solid #ddd"><span style="color:#888">Hier erscheint die Vorschau...</span></div>
		<button class="button" onclick="applyCSV(); return false;" disabled type="submit" id="apply-csv">Übernehmen</button>
	</div>
	<br class="clear" />
</div>
<p><a name="csv-help-format"></a>
  <strong>Hinweise zum Format der Eingabedaten</strong>
  <ul style="list-style:disc inside none">
  <li>Pro Spielpaarung eine Zeile.</li>
  <li>Pro Zeile gibt es drei Felder: <tt>blau,weiss,Freundschaftsspiel</tt></li>
  <li>Das dritte Feld (Freundschaftsspiel) ist optional. Nur wenn dort eine <tt>1</tt> steht wird die Paarung als Freundschaftsspiel gespeichert.</li>
  <li>Statt Kommas sind auch Tabs als Trennzeichen erlaubt (so kopiert es Excel), es muss nur einheitlich sein.</li>
  </ul>
</p>
</div><!-- accordion content -->

</div><!-- accordion -->

<script>
jQuery(document).ready(function() {
	jQuery('#accordion').accordion({
		header: "h2",
		collapsible: true,
		active: false,
	});
});
</script>

<script>
function disableApplyCSV() {
	jQuery('#apply-csv').prop('disabled', true);
}
function enableApplyCSV() {
	jQuery('#apply-csv').removeAttr('disabled');
}
var fixturesFromCSV = [];
function applyCSV() {
	while(fixturesFromCSV.length > 0) {
		var fx = fixturesFromCSV.shift();
		addInputs(fx.b, fx.w, fx.f);
	}
}
function showCSVPreview() {
	var data = CSVToArray(jQuery('#csv').val(), "\t");
	var isDataValid = false;
	for (var i=0; i < data.length; i++) {
		if (data[i].length < 2) { continue; }
		isDataValid = true;
		break;
	}
	if (!isDataValid) {
		data = CSVToArray(jQuery('#csv').val(), ",");
		numRows = 0;
		for (var i=0; i < data.length; i++) {
			if (data[i].length < 2) { continue; }
			isDataValid = true;
			break;
		}
	}
	if (!isDataValid) {
		alert("Ich kann diese Eingabedaten nicht verarbeiten.");
		return;
	}
	fixturesFromCSV = [];
	var preview = '';
	var nr = 0;
	for (var i=0; i < data.length; i++) {
		var game = data[i];
		if (game.length < 2) { continue; }
		nr++;
		var b = game[0].trim();
		var w = game[1].trim();
		var f = (1 == game[2]);
		var str = nr + ': ' + b + ' &mdash; ' + w + ' ' + (f ? '[F]' : '[ernst]') + "\n";
		preview += str;
		
		var gameData = { b:b, w:w, f:f };
		fixturesFromCSV.push(gameData);
	}
	console.log(fixturesFromCSV);
	jQuery('#preview-csv').html('<pre>'+preview+'</pre>');
}
    // This will parse a delimited string into an array of
    // arrays. The default delimiter is the comma, but this
    // can be overriden in the second argument.
    function CSVToArray( strData, strDelimiter ){
    	// Check to see if the delimiter is defined. If not,
    	// then default to comma.
    	strDelimiter = (strDelimiter || ",");

    	// Create a regular expression to parse the CSV values.
    	var objPattern = new RegExp(
    		(
    			// Delimiters.
    			"(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

    			// Quoted fields.
    			"(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

    			// Standard fields.
    			"([^\"\\" + strDelimiter + "\\r\\n]*))"
    		),
    		"gi"
    		);


    	// Create an array to hold our data. Give the array
    	// a default empty first row.
    	var arrData = [[]];

    	// Create an array to hold our individual pattern
    	// matching groups.
    	var arrMatches = null;


    	// Keep looping over the regular expression matches
    	// until we can no longer find a match.
    	while (arrMatches = objPattern.exec( strData )){

    		// Get the delimiter that was found.
    		var strMatchedDelimiter = arrMatches[ 1 ];

    		// Check to see if the given delimiter has a length
    		// (is not the start of string) and if it matches
    		// field delimiter. If id does not, then we know
    		// that this delimiter is a row delimiter.
    		if (
    			strMatchedDelimiter.length &&
    			(strMatchedDelimiter != strDelimiter)
    			){

    			// Since we have reached a new row of data,
    			// add an empty row to our data array.
    			arrData.push( [] );

    		}


    		// Now that we have our delimiter out of the way,
    		// let's check to see which kind of value we
    		// captured (quoted or unquoted).
    		if (arrMatches[ 2 ]){

    			// We found a quoted value. When we capture
    			// this value, unescape any double quotes.
    			var strMatchedValue = arrMatches[ 2 ].replace(
    				new RegExp( "\"\"", "g" ),
    				"\""
    				);

    		} else {

    			// We found a non-quoted value.
    			var strMatchedValue = arrMatches[ 3 ];

    		}


    		// Now that we have our value string, let's add
    		// it to the data array.
    		arrData[ arrData.length - 1 ].push( strMatchedValue );
    	}

    	// Return the parsed data.
    	return( arrData );
    }
</script>
<?php
print '</div>' // id="post-body-content" class="has-sidebar-content"
	. '</div>'; // id="post-body" class="has-sidebar"
?>
<script type="text/javascript" src="<?php bloginfo('wpurl'); ?>/wp-includes/js/jquery/suggest.js"></script> 
<script type="text/javascript">
// <![CDATA[
//var newFixtureId = <?php print $fixtureNumberOnMatchday; ?>;
function addSuggest(elem) {
	//elem.suggest( '<?php print Uwr1resultsView::ajaxUrl( 'search-teams' ); ?>', { delay: 100, minchars: 2 } );
	elem.suggest( 'http://uwr1.de/api/?module=teams&view=suche', { delay: 100, minchars: 2 } );
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
var newInputId = <?php print $fixtureNumberOnMatchday; ?>;
function addInputs(b = '', w = '', f = false) {
	addNotice();
	var tbl = jQuery('table.widefat');
	++newInputId;
	tbl.append(jQuery(
		'<tr><th>Neues Spiel (#' + newInputId + '):</th>'
		+ '<td><input id="fx' + newInputId + 'tb" class="auto-team" name="data[' + newInputId + '][team_blue]" value="'+b+'" /></td>'
		+ '<td><input id="fx' + newInputId + 'tw" class="auto-team" name="data[' + newInputId + '][team_white]" value="'+w+'" /></td>'
		+ '<td><input id="fx' + newInputId + 'fs" name="data[' + newInputId + '][friendly]" type="checkbox"'+(f?' checked="checked"':'')+' /> <label for="fx' + newInputId + 'fs">Freundschaftsspiel</label></td>'
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