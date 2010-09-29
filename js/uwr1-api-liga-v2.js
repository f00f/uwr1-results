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
		if (!elem.src || ! elem.src.match(/uwr1-liga-v2\.js(\?.*)?$/)) {
			return;
		}
		path = elem.src.replace(/uwr1-liga-v2\.js(\?.*)?$/,'');
		host = elem.src.replace(/^http:\/\/([^\/]+)\/.*$/,'$1');
		view = elem.src.match(/\?.*v=([a-z]*)/)[1];
		liga = elem.src.match(/\?.*l=([0-9a-z-]*)/)[1];
	});
	//ajaxBaseUrl = 'http://uwr1.de' + ajaxBaseUrl;
	ajaxBaseUrl = 'http://uwr1.test' + ajaxBaseUrl;
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
	var url = ajaxBaseUrl + 'ranking/' + liga + '?jsonp=?';
	var json = jQuery.getJSON(url, function(data) {
		var tmpl = '<div class="uwr1 ranking">'
			+ '<table cellspacing="0" class="liga">'
			+ '<tr>'
			+ '<th class="pl">Platz</th>'
			+ '<th class="ma">Mannschaft</th>'
			+ '<th class="sp">Spiele</th>'
			+ '<th class="to" colspan="2">Tore</th>'
			+ '<th class="pu">Punkte</th>'
			+ '</tr>';
		tmpl += '{{#res}}'
			+ '{{#t}}'
			+ '<tr>'
			+ '<td class="pl">{{r}}</td>'
			+ '<td class="ma">{{{m}}}</td>'
			+ '<td class="sp num">{{s}}</td>'
			+ '<td class="di r">{{{d}}}</td>'
			+ '<td class="to"> <span class="detail">({{{t}}})</span></td>'
			+ '<td class="pu num">{{{p}}}</td>'
			+ '</tr>'
			+ '{{/t}}'
			+ '{{/res}}';
		tmpl += '<tr><td colspan="6"><div class="poweredbyuwr1">Weitere <a href="http://uwr1.de/ergebnisse/">UWR Ergebnisse</a></div></td></tr></table></div>';
		rnk = Mustache.to_html(tmpl, data);
/*
		var rnk = '<div class="ranking">'
			+ '<table cellspacing="0" class="liga">'
			+ '<tr>'
			+ '<th class="pl">Platz</th>'
			+ '<th class="ma">Mannschaft</th>'
			+ '<th class="sp">Spiele</th>'
			+ '<th class="to" colspan="2">Tore</th>'
			+ '<th class="pu">Punkte</th>'
			+ '</tr>';
		jQuery.each(data.res, function(i, it){
			rnk += '<tr>'
				+ '<td class="pl">' + it.r + '</td>'
				+ '<td class="ma">' + it.t + '</td>'
				+ '<td class="sp num">' + it.m + '</td>'
				+ '<td class="to r">' + it.d + '</td>'
				+ '<td class="to"> <span class="detail">(' + it.g + ')</span></td>'
				+ '<td class="pu num">' + it.p + '</td>'
				+ '</tr>';
		});
		//jQuery('#'+elemId).removeClass('loading');
		rnk += '<tr><td colspan="6"><div class="poweredbyuwr1">Weitere <a href="http://uwr1.de/ergebnisse/">UWR Ergebnisse</a></div></td></tr></table></div>';
*/
		jQuery('#'+elemId).html(rnk);
	});
}
//  load matchdays
// display league data
//dbg('loaded uwr-liga-v2.js and foo.js');