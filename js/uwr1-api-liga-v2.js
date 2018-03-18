// Author: Hannes Hofmann
// Url: http://uwr1.de/
// License: BSD
// TODO: Encapsulate in object(s)
//var ajaxBaseUrl = '/ergebnisse/ajax/';
var ajaxBaseUrlAE = 'https://uwr1cdn2.appspot.com/jc/json';
//var path = 'path';
//var host = 'host';
//var view = 'view';
//var liga = 'liga';
//var elemId = 'uwr1-liga';

if(!window.uwr1)window.uwr1={};

/*
jQuery( function() {
	var scriptTags = document.getElementsByTagName("script");
	jQuery(scriptTags).each( function(i, elem) {
		if (!elem.src || !elem.src.match(/uwr1-liga-v2\.js(\?.*)?$/)) {
			return;
		}
		//path = elem.src.replace(/uwr1-liga-v2\.js(\?.*)?$/,'');
		//host = elem.src.replace(/^http:\/\/([^\/]+)\/.*$/,'$1');
		//view = elem.src.match(/\?.*v=([a-z]*)/)[1];
		//liga = elem.src.match(/\?.*l=([0-9a-z-]*)/)[1];
	});
	//ajaxBaseUrl = 'http://uwr1.de' + ajaxBaseUrl;
	ajaxBaseUrl = 'http://uwr1.test' + ajaxBaseUrl;

	// dispatch: ranking, specific matchday, specific matchday and ranking, ranking and all matchdays, ...
});
*/

uwr1.results = {
	defaultElemId : 'uwr1-liga',
	pwdBy : '<div class="uwr1 pwdbyuwr1">Weitere <a href="http://uwr1.de/ergebnisse/">UWR Ergebnisse</a></div>',
	errMsg : function(m, elemId) {
		var html = '<div class="uwr1 ranking error">'+m+'</div>'
			+ '<div class="uwr1 pwdbyuwr1">Alle <a href="http://uwr1.de/ergebnisse/">UWR Ergebnisse</a> dierkt auf uwr1.de ansehen.</div>';
		jQuery('#'+elemId).html( html );
		return false;
	}
};

uwr1.results.Ranking = function(l, elemId) {
	if (!elemId) elemId = uwr1.results.defaultElemId;
	// set up ajax callbacks for 'loading' animation
	jQuery('#'+elemId).ajaxStart(function(){ jQuery(this).addClass('loading'); });
	jQuery('#'+elemId).ajaxStop(function(){ jQuery(this).removeClass('loading'); });
	if (!l) {
		return uwr1.results.errMsg('Konnte Ergebnisdaten nicht laden. [l]', elemId);
	}
	uwr1.results.RankingBE.show(l, elemId);
}

uwr1.results.RankingBE = {
	render : function(d) {
		var html = '<div class="uwr1 ranking">'
			+ '<table cellspacing="0" class="liga">'
			+ '<tr>'
			+ '<th class="pl">Platz</th>'
			+ '<th class="ma">Mannschaft</th>'
			+ '<th class="sp">Spiele</th>'
			+ '<th class="to" colspan="2">Tore</th>'
			+ '<th class="pu">Punkte</th>'
			+ '</tr>';
		jQuery.each(d, function(i, it){
			html += '<tr>'
				+ '<td class="pl">' + it.r + '</td>'
				+ '<td class="ma">' + it.m + '</td>'
				+ '<td class="sp num">' + it.s + '</td>'
				+ '<td class="to r">' + it.d + '</td>'
				+ '<td class="to"> <span class="detail">(' + it.t + ')</span></td>'
				+ '<td class="pu num">' + it.p + '</td>'
				+ '</tr>';
		});
		html += '<tr><td colspan="6">'+uwr1.results.pwdBy+'</td></tr></table></div>';
		return html;
	},
	show : function(l, elemId) {
		var url = ajaxBaseUrlAE + '?k=ranking/'+l+'&v=2&jsonp=?';
		jQuery.getJSON(url, function(data) {
			if ('OK' != data.s) {
				if (0==data.cnt) {
					return uwr1.results.errMsg('Konnte keine Ergebnisdaten laden. [c]', elemId);
				} else {
					return uwr1.results.errMsg('Konnte keine Ergebnisdaten laden. [x]', elemId);
				}
			}
			jQuery('#'+elemId).html( uwr1.results.RankingBE.render(data.res) );
		});
	}
}; /* /Ranking */

//  load matchdays
// display league data
//dbg('loaded uwr-liga-v2.js and foo.js');
