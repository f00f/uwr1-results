// Author: Hannes Hofmann
// Url: http://uwr1.de/
// License: BSD
function uwr1ApiError(str, elem) {
	if (!elem) var elem='log';
	var oldstr = jQuery('#log').html();
	if (oldstr) oldstr += '<br />';
	jQuery('#'+elem).html(oldstr + str);
}
function dbg(str) {
	var oldstr = jQuery('#log').html();
	if (oldstr) oldstr += '<br />';
	jQuery('#log').html(oldstr + str);
}