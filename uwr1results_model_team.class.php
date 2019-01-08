<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


class Uwr1resultsModelTeam
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id' => 'team_ID',
		'country' => 'team_country',
		'name' => 'team_name',
		'slug' => 'team_slug',
	);

	/**
	 * The singleton instance of this object.
	 *
	 * @static
	 * @access private
	 */
	private static $instance=NULL;

	/**
	 * Return the single instance of this class.
	 * The instance is created if neccessary
	 *
	 * @return Object   The singleton instance
	 */
	public static function &instance() {
		if (!self::$instance) {
			self::$instance = new Uwr1resultsModelTeam();
		}
		return self::$instance;
	}

	protected function init() {
		//$this->table = UWR1RESULTS_TBL_TEAMS;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$teamsTable =& parent::getTable(get_class($this));
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$teamsTable}` (
	`team_ID`    INT(11) NOT NULL auto_increment,
	`team_country`  VARCHAR(10) collate utf8_general_ci NOT NULL,
	`team_name`  VARCHAR(50) collate utf8_general_ci NOT NULL,
	`team_slug`  VARCHAR(50) collate utf8_general_ci NOT NULL,
	PRIMARY KEY (`team_ID`),
	UNIQUE KEY `team_name` (`team_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		parent::$_wpdb->query($sql);
	}


	// FIND METHODS
		
	public function findByName($name) {
		if (empty($name)) {
			return false;
		}

		$teamsTable = parent::getTable(get_class($this));

		$sql = "SELECT * FROM `{$teamsTable}`"
			. " WHERE `team_name` = '{$name}'";

		$team = parent::$_wpdb->get_row($sql);

		if (empty($team)) {
			return false;
		}
		return $team;
		// FIXME: use findFirst
	}

	public function findByLeagueId($leagueId, $season = null) {
		$leagueId = intval($leagueId);

		$teamsTable = parent::getTable(get_class($this));
		$leaguesTeamsTable = UWR1RESULTS_TBL_LEAGUES_TEAMS;

		$sql = "SELECT `t`.* FROM `{$leaguesTeamsTable}` AS `lt`"
			. " LEFT JOIN `{$teamsTable}` AS `t` ON `lt`.`team_ID` = `t`.`team_ID`"
			. " WHERE `lt`.`league_ID` = {$leagueId}";

		return parent::$_wpdb->get_results($sql);
	}
} // Uwr1resultsModelTeam
Uwr1resultsModelTeam::initTable('Uwr1resultsModelTeam', UWR1RESULTS_TBL_TEAMS);
