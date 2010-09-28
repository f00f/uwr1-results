// Author: Hannes Hofmann, URL: http://uwr1.de/, License: BSD
function loadJS(u){document.write('<script type="text/javascript" src="'+u+'"></script>');}
loadJS('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
// load actual uwr1 API scripts
document.write('<link href="http://uwr1.de/css/uwr1-api-liga.css" rel="stylesheet" type="text/css" />');
loadJS('http://uwr1.de/js/uwr1-api.js');
loadJS('http://uwr1.de/js/uwr1-api-liga.js');