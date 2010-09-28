<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


class Uwr1resultsModelMatchday
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id' => 'matchday_ID',
		'leagueId' => 'league_ID',
		'seasonId' => 'season_ID',
		'tournamentId' => 'tournament_ID',
		'name' => 'matchday_name',
		'slug' => 'matchday_slug',
		'order' => 'matchday_order',
		'location' => 'matchday_location',
		'date' => 'matchday_date',
		'begin' => 'matchday_begin',
		'end' => 'matchday_end',
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
			self::$instance = new Uwr1resultsModelMatchday();
		}
		return self::$instance;
	}

	protected function init() {
		$this->_table = UWR1RESULTS_TBL_MATCHDAYS;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$uwr1resultsTable =& $this->table();
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$uwr1resultsTable}` (
	`matchday_ID` int(11) NOT NULL auto_increment,
	`league_ID` int(11) NOT NULL default '0',
	`season_ID` int(11) NOT NULL default '0',
	`tournament_ID` int(11) NOT NULL default '0',
	`matchday_name` varchar(50) collate utf8_general_ci NOT NULL default '',
	`matchday_slug` varchar(50) collate utf8_general_ci NOT NULL default '',
	`matchday_order` tinyint(4) NOT NULL default '0',
	`matchday_location` varchar(50) collate utf8_general_ci NOT NULL default '',
	`matchday_date` date NOT NULL default '0000-00-00',
	`matchday_begin` datetime NOT NULL default '0000-00-00 00:00:00',
	`matchday_end` datetime NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY  (`matchday_ID`),
	KEY `league_ID` (`league_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
	// CHARSET=latin1 COLLATE=latin1_german2_ci
		$this->_wpdb->query($sql);
	}

	// ACCESSORS
	
	public function name() {
		if( isset($this->properties[ 'name' ]) && !$this->properties[ 'name' ]) {
			$this->set('name', $this->order() . '. Spieltag');
		}
		return $this->properties[ 'name' ];
	}

	public function saveMany($leagueId) {
		if (!$leagueId) {
			return new Uwr1resultsException('No ID given');
		}

		Uwr1resultsHelper::enforcePermission( 'save' );

		$seasonId = Uwr1resultsController::season();

		foreach ($_POST['data'] as $order => $md) {
			$this->populate(array(
				'id'           => (int) $md['id'],
				'leagueId'     => (int) $leagueId,
				'seasonId'     => $seasonId,
				'tournamentId' => 0,
				'name'         => '', // $md['name'],
				'slug'         => '', // $md['slug'],
				'order'        => (int) $md['order'],
				'location'     => $md['location'],
				'date'         => $md['date'],
				'begin'        => '0000-00-00 00:00:00', // $md['begin'],
				'end'          => '0000-00-00 00:00:00', // $md['end'],
			));
			$rv = $this->save();
			if (false === $rv) {
				return false;
			}
		}

		return true;
	} // saveMany


	// FIND METHODS
		
	public function findByLeagueId( $leagueId, $seasonId = 0 ) {
		$matchdaysTable = $this->table();
		$fixturesTable  = Uwr1resultsModelFixture::instance()->table();
		$teamsTable     = Uwr1resultsModelTeam::instance()->table();
		if (!$seasonId) $seasonId = Uwr1resultsController::season();

		/*
		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `f`.*, `m`.*,"
 			. " `t_b`.`team_ID` AS `t_b_ID`, `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_ID` AS `t_w_ID`, `t_w`.`team_name` AS `t_w_name`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `m`.`league_id` = '{$leagueId}'"
			. " AND `m`.`season_ID` = {$seasonId}"
			. " ORDER BY `m`.`matchday_order`, `f`.`fixture_date`, `f`.`fixture_time`, `f`.`fixture_ID`"
			;
		*/
		$sql = "SELECT `m`.*, COUNT(`f`.`fixture_ID`) as `fixture_count`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " WHERE `m`.`league_id` = '{$leagueId}'"
			. " AND `m`.`season_ID` = {$seasonId}"
			. " GROUP BY `m`.`matchday_ID`"
			. " ORDER BY `m`.`matchday_order`"
			;

		// TODO: think about using $this->_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return $this->_wpdb->get_results($sql);
	}

	public function findByTournamentId( $tournamentId ) {
		$matchdaysTable = $this->table();
		$fixturesTable  = Uwr1resultsModelFixture::instance()->table();
		$teamsTable     = Uwr1resultsModelTeam::instance()->table();

		$sql = "SELECT `m`.*, COUNT(`f`.`fixture_ID`) as `fixture_count`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " WHERE `m`.`tournament_id` = '{$tournamentId}'"
			. " GROUP BY `m`.`matchday_ID`"
			. " ORDER BY `m`.`matchday_order`"
			;

		// TODO: think about using $this->_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return $this->_wpdb->get_results($sql);
	}

} // Uwr1resultsModelMatchday
?>