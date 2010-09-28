<?php
/*
Plugin Name: uwr1results
Plugin URI: http://uwr1.de/
Description: This plugin allows management of Underwater Rugby fixtures and results
Author: Hannes Hofmann
Author URI: http://uwr1.de/
Version: 0.3
*/

/*
Compatibility: Tested with wordpress 2.5
Compatibility: This plugin requires mod_rewrite.
Compatibility: This plugin requires PHP5. Go get it!

This file exists, because WP has a bug (confirmed for 2.1) with plugin activation
and deactivation functions when the plugin is in a subfolder.
*/

require_once 'uwr1results_controller.class.php';

// (De-)Activation hooks
register_activation_hook(__FILE__, array('RulesultsController', 'activatePlugin'));
register_deactivation_hook(__FILE__, array('RulesultsController', 'deactivatePlugin'));
?>