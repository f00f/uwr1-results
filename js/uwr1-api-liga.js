// Author: Hannes Hofmann
// Url: http://uwr1.de/
// License: BSD
var ajaxBaseUrl = '/ergebnisse/ajax/';
var path = 'path';
var host = 'host';
var view = 'view';
var liga = 'liga';
var elemId = 'uwr1-liga';

jQuery( function() {
	var scriptTags = document.getElementsByTagName("script");
	jQuery(scriptTags).each( function(i, elem) {
		if (!elem.src || ! elem.src.match(/uwr1-liga\.js(\?.*)?$/)) {
			return;
		}
		path = elem.src.replace(/uwr1-liga\.js(\?.*)?$/,'');
		host = elem.src.replace(/^http:\/\/([^\/]+)\/.*$/,'$1');
		view = elem.src.match(/\?.*v=([a-z]*)/)[1];
		liga = elem.src.match(/\?.*l=([0-9a-z-]*)/)[1];
	});
	ajaxBaseUrl = 'http://uwr1.de' + ajaxBaseUrl;
	//dbg('host:'+host);
	//dbg('path:'+path);
	//dbg('view:'+view);
	//dbg('liga:'+liga);
	
	// set up ajax callbacks for 'loading' animation
	jQuery('#'+elemId).ajaxStart(function(){ jQuery(this).addClass('loading'); });
	jQuery('#'+elemId).ajaxStop(function(){ jQuery(this).removeClass('loading'); });

	// dispatch: ranking, specific matchday, specific matchday and ranking, ranking and all matchdays, ...
	uwr1ApiLigaDispatch(view, liga);
});

function uwr1ApiLigaDispatch(view, liga) {
	var load = new Array();
	// load league data
	switch(view) {
		case 'rnk': load['rnk'] = true; break;
		case 'md':  load['md'] = true; break;
	}

	//  load ranking
	if (load['rnk']) {
		loadRanking(liga);
	}
}

function loadRanking(liga) {
	if (!liga) {
		uwr1ApiError('Error loading data. (liga=null)', elemId);
		return;
	}
	var url = ajaxBaseUrl + 'ranking/' + liga + '/?jsonp=?';
	var json = jQuery.getJSON(url, function(data) {
		var ranking = '<div class="ranking">'
			+ '<table cellspacing="0" class="liga">'
			+ '<tr>'
			+ '<th class="pl">Platz</th>'
			+ '<th class="ma">Mannschaft</th>'
			+ '<th class="sp">Spiele</th>'
			+ '<th class="to" colspan="2">Tore</th>'
			+ '<th class="pu">Punkte</th>'
			+ '</tr>';
		jQuery.each(data.uwr1results, function(i, item){
			ranking += '<tr>'
				+ '<td class="pl">' + item.rank + '</td>'
				+ '<td class="ma">' + item.team + '</td>'
				+ '<td class="sp num">' + item.spiele + '</td>'
				+ '<td class="to r">' + item.tordiff + '</td>'
				+ '<td class="to"> <span class="detail">(' + item.tore + ')</span></td>'
				+ '<td class="pu num">' + item.punkte + '</td>'
				+ '</tr>';
		});
		//jQuery('#'+elemId).removeClass('loading');
		ranking += '<tr><td colspan="6"><div class="poweredbyuwr1">Weitere <a href="http://uwr1.de/ergebnisse/">UWR Ergebnisse</a></div></td></tr></table></div>';
		jQuery('#'+elemId).html(ranking);
	});
}
//  load matchdays
// display league data
//dbg('loaded uwr-liga.js and foo.js');