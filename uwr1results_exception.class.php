<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';

class Uwr1resultsException {
	/**
	 * Constructor.
	 * Init private member variables.
	 * Make sure that no one can build this object.
	 *
	 * @access private
	 * @return void
	 */
	public function __construct($msg) {
		print '<div class="error"><p>'.$msg.'</p></div>';
		print '<p>Zur&uuml;ck zur <a href="http://uwr1.de/ergebnisse" title="Unterwasserrugby Liga Ergebnisse">Startseite des Ergebnisdienstes</a>.</p>';
		print '<p>Zur&uuml;ck zur <a href="http://uwr1.de/" title="Unterwasserrugby Forum, Blog, Liga">Startseite von uwr1.de</a>.</p>';
		exit;
	}
} // Uwr1resultsException
