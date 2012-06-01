<?php
/*
Plugin Name: uwr1results
Plugin URI: http://uwr1.de/
Description: This plugin allows management of Underwater Rugby fixtures and results
Author: Hannes Hofmann
Author URI: http://uwr1.de/
Version: 1.0.1
*/

/*
Compatibility: Tested with wordpress 3.0.1
Compatibility: This plugin requires mod_rewrite.
Compatibility: This plugin requires PHP5.
*/

require_once 'uwr1results_controller.class.php';

// (De-)Activation hooks
register_activation_hook(__FILE__, array('RulesultsController', 'activatePlugin'));
register_deactivation_hook(__FILE__, array('RulesultsController', 'deactivatePlugin'));
