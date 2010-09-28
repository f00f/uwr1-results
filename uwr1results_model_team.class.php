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
		$this->_table = UWR1RESULTS_TBL_TEAMS;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$uwr1resultsTable =& $this->table();
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$uwr1resultsTable}` (
	`team_ID`    INT(11) NOT NULL auto_increment,
	`team_name`  VARCHAR(50) collate utf8_general_ci NOT NULL,
	`team_slug`  VARCHAR(50) collate utf8_general_ci NOT NULL,
	PRIMARY KEY (`team_ID`),
	UNIQUE KEY `team_name` (`team_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		$this->_wpdb->query($sql);
	}


	// FIND METHODS
		
	public function findByName($name) {
		if (empty($name)) {
			return false;
		}

		$teamsTable = $this->table();

		$sql = "SELECT * FROM `{$teamsTable}`"
			. " WHERE `team_name` = '{$name}'";

		$team = $this->_wpdb->get_row($sql);

		if (empty($team)) {
			return false;
		}
		return $team;
		// FIXME: use findFirst
	}

	public function findByLeagueId($leagueId, $season = null) {
		$leagueId = intval($leagueId);

		$teamsTable = $this->table();
		$leaguesTeamsTable = UWR1RESULTS_TBL_LEAGUES_TEAMS;

		$sql = "SELECT `t`.* FROM `{$leaguesTeamsTable}` AS `lt`"
			. " LEFT JOIN `{$teamsTable}` AS `t` ON `lt`.`team_ID` = `t`.`team_ID`"
			. " WHERE `lt`.`league_ID` = {$leagueId}";

		return $this->_wpdb->get_results($sql);
	}
} // Uwr1resultsModelTeam
?>