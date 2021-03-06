<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


class Uwr1resultsModelResult
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id' => 'result_ID',
		'fixtureId' => 'fixture_ID',
		'userId' => 'user_ID',
		'modified' => 'result_modified',
		'goalsBlue' => 'result_goals_b',
		'goalsWhite' => 'result_goals_w',
		'pointsBlue' => 'result_points_b',
		'pointsWhite' => 'result_points_w',
		'goalsHalfBlue' => 'result_goals_half_b',
		'goalsHalfWhite' => 'result_goals_half_w',
		'goalsRegularBlue' => 'result_goals_reg_b',
		'goalsRegularWhite' => 'result_goals_reg_w',
		'comment' => 'result_comment',
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
			self::$instance = new Uwr1resultsModelResult();
		}
		return self::$instance;
	}

	protected function init() {
		//$this->table = UWR1RESULTS_TBL_RESULTS;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$resultsTable = parent::getTable(get_class($this));
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$resultsTable}` (
	`result_ID` int(11) NOT NULL auto_increment,
	`fixture_ID` int(11) NOT NULL default '0',
	`user_ID` int(11) NOT NULL default '0',
	`result_modified` datetime NOT NULL default '0000-00-00 00:00:00',
	`result_goals_b` tinyint(4) NOT NULL default '0',
	`result_goals_w` tinyint(4) NOT NULL default '0',
	`result_points_b` tinyint(4) NOT NULL default '0',
	`result_points_w` tinyint(4) NOT NULL default '0',
	`result_goals_half_b` tinyint(4) NOT NULL default '0',
	`result_goals_half_w` tinyint(4) NOT NULL default '0',
	`result_goals_reg_b` tinyint(4) NOT NULL default '0',
	`result_goals_reg_w` tinyint(4) NOT NULL default '0',
	`result_comment` varchar(255) collate utf8_general_ci NOT NULL default '',
	PRIMARY KEY  (`result_ID`),
	KEY `result_modified` (`result_modified`),
	KEY `fixture_ID` (`fixture_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		parent::$_wpdb->query($sql);
	}

	public function leagueSlug() {
		$fixture = $this->getFixture();
		return $fixture->leagueSlug();
	}

	public function getFixture() {
		$rmf = Uwr1resultsModelFixture::instance();
		return $rmf->findById($this->fixtureId());
	}

	private function updatePoints() {
		if ( $this->goalsBlue() == $this->goalsWhite() ) {
			$this->set( 'pointsBlue', UWR1RESULTS_PTS_DRAW );
			$this->set( 'pointsWhite', UWR1RESULTS_PTS_DRAW );
		} else {
			if ( $this->goalsBlue() > $this->goalsWhite() ) {
				$this->set( 'pointsBlue', UWR1RESULTS_PTS_WIN );
				$this->set( 'pointsWhite', UWR1RESULTS_PTS_LOSS );
			} else {
				$this->set( 'pointsBlue', UWR1RESULTS_PTS_LOSS );
				$this->set( 'pointsWhite', UWR1RESULTS_PTS_WIN );
			}
		}
	}
	
	public function populateAndSave() {
		global $current_user;

		Uwr1resultsHelper::enforcePermission( 'save' );

		if (!$this->fixtureId()) {
			if (!empty($_REQUEST['fixture_id'])) {
				$this->set( 'fixtureId', intval($_REQUEST['fixture_id']) );
			} else {
				return new Uwr1resultsException('No ID given');
			}
		}
		
		// For now: only one result per game: resultId == fixtureId
		$this->populate(array(
			'id'                => $this->fixtureId(),
			'userId'            => $current_user->ID,
			'modified'          => 'NOW()',
			'goalsBlue'         => intval($_POST['goalsBlue']),
			'goalsWhite'        => intval($_POST['goalsWhite']),
			'goalsHalfBlue'     => -1,
			'goalsHalfWhite'    => -1,
			'goalsRegularBlue'  => -1,
			'goalsRegularWhite' => -1,
			'comment'           => trim($_POST['comment']),
		));

		$rv = $this->save();
		if ($rv) {
			$new_item = $this->findById($this->fixtureId());
			do_action('uwr1results_save_result', $new_item);
		}
		return $rv;
	}
	
	public function saveMany() {
		global $current_user;

		if (!$this->id()) {
			if (!empty($_REQUEST['matchday_id'])) {
				$this->set( 'id', intval($_REQUEST['matchday_id']) );
			} else {
				return new Uwr1resultsException('No ID given');
			}
		}

		$r = Uwr1resultsModelResult::instance();
		$new_items = array();
		$success = true;
		foreach ($_POST['fx'] as $f) {
			if ( '' === $f['goals_blue'] || '' === $f['goals_white']) {
				continue;
			}
			$r->populate(array(
				'id'                => (int)$f['id'],
				'fixtureId'         => (int)$f['id'],
				'userId'            => $current_user->ID,
				'modified'          => 'NOW()',
				'goalsBlue'         => 0 + $f['goals_blue'],
				'goalsWhite'        => 0 + $f['goals_white'],
				'goalsHalfBlue'     => 0 + @$f['goals_half_blue'],
				'goalsHalfWhite'    => 0 + @$f['goals_half_white'],
				'goalsRegularBlue'  => 0 + @$f['goals_regular_blue'],
				'goalsRegularWhite' => 0 + @$f['goals_regular_white'],
				'comment'           => '' . $f['comment'],
			));
			$rv = $this->save(false); // don't notifyJsonCache for each store
			if (false === $rv) {
				$success = false;
				break;
			}

			$new_item = clone $this->findById((int)$f['id']);
			$new_items[] = $new_item;
		}

		// eventually update cache
		$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . parent::getTable(get_class($this)));
		do_action('uwr1results_save_results', $new_items);
		return $success;
	} // saveMany

	// TODO: should this be private?
	// Controller calls populateAndSave() or saveMany()
	// Do not call hook uwr1results_save_result in here
	public function save($notifyJsonCache = true) {
		global $wpdb;
		// TODO: check permissions
		Uwr1resultsHelper::enforcePermission( 'save' );

		// check if a result for this fixture already exists.
		// otherwise, the submitter name get overwritten and that sucks.
		$doSave = false;

		// kludge: use an internal second instance of this class
		$tmpInstance = new Uwr1resultsModelResult();
		$oldResult = $tmpInstance->findById($this->id());

		$this->updatePoints();
		$changes = Uwr1resultsModel::diff($oldResult->properties, $this->properties);
		// ignore all fields which are not relevant for the decission of $doSave
		$ignoreFields = array('userId', 'modified',
							'goalsHalfBlue', 'goalsHalfWhite',
							'goalsRegularBlue', 'goalsRegularWhite');
		foreach ($ignoreFields as $field) {
			unset($changes->$field);
		}

		// all cases where an update is needed:
		// 1) new result (there is no old result)
		if (!$doSave && !@$oldResult->id()) {
			$doSave = true;
		}
		// 2) result changed (we optimistically assume the new one is a correction)
		if (!$doSave && 
			(isset($changes->goalsBlue) || isset($changes->goalsWhite))
			) {
			$doSave = true;
		}
		// 3) comment changed and not set to empty string
		$newComment = trim(@$changes->comment['new']);
		if (!$doSave && !empty($newComment)) {
			$doSave = true;
		}
		unset($newComment);
		
		if (!$doSave) { return; }


		$this->updatePoints();
		
		// re-compute $changes after updatePoints
		$changes = Uwr1resultsModel::diff($oldResult->properties, $this->properties);
		// ignore fields which are not user-settable
		$ignoreFields = array('modified',
							'goalsHalfBlue', 'goalsHalfWhite',
							'goalsRegularBlue', 'goalsRegularWhite');
		foreach ($ignoreFields as $field) {
			unset($changes->$field);
		}

		// escape and quote $comment
		$comment = $this->comment();
		$this->set( 'comment', "'" . Uwr1resultsHelper::sqlEscape( $comment ) . "'" );

		$fields = array();
		$values = array();
		foreach ($this->dbMapping as $prop => $dbField) {
			$fields[] = $dbField;
			$values[] = $this->$prop();
		}
		$fieldsStr = "`" . implode("`, `", $fields) . "`";
		$valuesStr = implode(", ", $values);
/*		
		$sql = 'REPLACE INTO `'.parent::getTable(get_class($this)).'`'
			. ' (`fixture_ID`, `user_ID`, `result_modified`, `result_goals_b`, `result_goals_w`)'
			. ' VALUES'
			. ' (' . $this->fixtureId() . ', ' . intval($current_user) . ', NOW(), ' . intval($_POST['goalsBlue']) . ', ' . intval($_POST['goalsWhite']) . ')';
*/
		// For now: only one result per game: resultId == fixtureId
		$sql = 'REPLACE INTO `'.parent::getTable(get_class($this)).'`'
			. " ({$fieldsStr})"
			. ' VALUES'
			. " ({$valuesStr})";
		//print $sql;exit;
		$res = $wpdb->query($sql);
		
		if ($res && $notifyJsonCache) {
			$this->notifyJsonCache($this->leagueSlug(), __FILE__);
		}

		// TODO: change return type: $result_id on success, false otherwise
		return $res;
	}


	// FIND METHODS

	public function findByFixtureId($fixtureId) {
		$fixtureId = intval( $fixtureId );
		if (!$fixtureId) {
			exit;
		}

		$resultsTable = parent::getTable(get_class($this));

		$sql = "SELECT * FROM `{$resultsTable}` AS `r`"
			. " WHERE `r`.`fixture_ID` = '{$fixtureId}'"
			. " ORDER BY `r`.`result_modified` DESC"
			. " LIMIT 1";

		return $this->findFirst($sql);
	}

	/* Find all results where both teams are in the given list of team IDs.
	 * 
	 * Return value can be used as input for createRanking()
	 * returns the same information as findByLeagueId()
	 */
	public function findByTeamIds($teamIds, $seasonId = 0) {
		foreach ($teamIds as $k => $v) {
			$id = intval( $v );
			if ($id <= 0) {
				unset($teamIds[$k]);
			}
			$teamIds[$k] = $id;
		}

		$resultsTable   = parent::getTable(get_class($this));
		$matchdaysTable = parent::getTable('Uwr1resultsModelMatchday');
		$fixturesTable  = parent::getTable('Uwr1resultsModelFixture');
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');
		if (!$seasonId) $seasonId = Uwr1resultsController::season();

		$team_ids = implode(', ', $teamIds);
 		$sql = "SELECT `r`.*, `f`.*, `m`.`matchday_date`,"
 			. " `t_b`.`team_ID` AS `t_b_ID`, `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_ID` AS `t_w_ID`, `t_w`.`team_name` AS `t_w_name`"
			. " FROM `{$resultsTable}` AS `r`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`fixture_ID` = `r`.`fixture_ID`"
 			. " LEFT JOIN `{$matchdaysTable}` AS `m` ON `m`.`matchday_ID` = `f`.`matchday_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE ( `f`.`fixture_team_blue`  IN ({$team_ids})"
			. "      OR `f`.`fixture_team_white` IN ({$team_ids}) )"
			. "   AND `m`.`season_id` = {$seasonId}"
			. " ORDER BY `matchday_date` DESC"
			//. "          ``"
			;
		return parent::$_wpdb->get_results($sql);
	}
/*
	public function findByMatchdayId( $matchdayId ) {
		$resultsTable   = parent::getTable(get_class($this));
		$matchdaysTable = parent::getTable('Uwr1resultsModelMatchday');
		$fixturesTable  = parent::getTable('Uwr1resultsModelFixture');
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');

		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `r`.*, `f`.*, `m`.*,"
 			. " `t_b`.`team_ID` AS `t_b_ID`, `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_ID` AS `t_w_ID`, `t_w`.`team_name` AS `t_w_name`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " LEFT OUTER JOIN `{$resultsTable}` AS `r` ON `r`.`fixture_ID` = `f`.`fixture_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `m`.`league_id` = '{$leagueId}'"
			. " AND ("
			. " 	ISNULL(`r`.`result_ID`)"
			. " 	OR"
			. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
			. " )"
			. " ORDER BY `m`.`matchday_order`, `f`.`fixture_date`, `f`.`fixture_time`, `f`.`fixture_ID`"
			;

		// TODO: think about using parent::$_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return parent::$_wpdb->get_results($sql);
	}
*/
	public function findByLeagueId( $leagueId, $seasonId = 0 ) {
		$resultsTable   = parent::getTable(get_class($this));
		$matchdaysTable = parent::getTable('Uwr1resultsModelMatchday');
		$fixturesTable  = parent::getTable('Uwr1resultsModelFixture');
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');
		if (!$seasonId) $seasonId = Uwr1resultsController::season();

		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `r`.*, `f`.*, `m`.*,"
 			. " `t_b`.`team_ID` AS `t_b_ID`, `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_ID` AS `t_w_ID`, `t_w`.`team_name` AS `t_w_name`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " LEFT OUTER JOIN `{$resultsTable}` AS `r` ON `r`.`fixture_ID` = `f`.`fixture_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `m`.`league_id` = '{$leagueId}'"
			. " AND `m`.`season_ID` = {$seasonId}"
			. " AND ("
			. " 	ISNULL(`r`.`result_ID`)"
			. " 	OR"
			. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
			. " )"
			. " ORDER BY `m`.`matchday_order`, `f`.`fixture_date`, `f`.`fixture_time`, `f`.`fixture_ID`"
			;

		// TODO: think about using parent::$_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return parent::$_wpdb->get_results($sql);
	}

	public function findByTournamentId( $tournamentId ) {
		$resultsTable   = parent::getTable(get_class($this));
		$matchdaysTable = parent::getTable('Uwr1resultsModelMatchday');
		$fixturesTable  = parent::getTable('Uwr1resultsModelFixture');
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');

		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `r`.*, `f`.*, `m`.*,"
 			. " `t_b`.`team_ID` AS `t_b_ID`, `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_ID` AS `t_w_ID`, `t_w`.`team_name` AS `t_w_name`"
 			. " FROM `{$matchdaysTable}` AS `m`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`matchday_ID` = `m`.`matchday_ID`"
			. " LEFT OUTER JOIN `{$resultsTable}` AS `r` ON `r`.`fixture_ID` = `f`.`fixture_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `m`.`tournament_id` = '{$tournamentId}'"
			. " AND ("
			. " 	ISNULL(`r`.`result_ID`)"
			. " 	OR"
			. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
			. " )"
			. " ORDER BY `m`.`matchday_order`, `f`.`fixture_date`, `f`.`fixture_time`, `f`.`fixture_ID`"
			;

		// TODO: think about using parent::$_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return parent::$_wpdb->get_results($sql);
	}

	// for backward-compatibility: unwrap return value
	public function findRecentResults( $args = null ) {
		$ret = $this->findRecentResults2($args);
		return $ret['result'];
	}

	public function findRecentResults2( $args = null ) {
		// backward compatibility, when the only parameter was an int, $num
		if (!is_array($args)) {
			$newArgs = array();
			if (is_int($args)) {
				$newArgs = array('num' => $args);
			}
			$args = $newArgs;
		}
		
		// set default values
		if (!@$args['num'])  { $args['num']  = 5; }

		$resultsTable   = parent::getTable(get_class($this));
		$matchdaysTable = parent::getTable('Uwr1resultsModelMatchday');
		$fixturesTable  = parent::getTable('Uwr1resultsModelFixture');
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');
		$leaguesTable   = parent::getTable('Uwr1resultsModelLeague');

		$ret = array();
		$ret['status'] = 'empty';
		$ret['limit']  = 'none';
		$ret['result'] = null;

		////////////////////////////////////////////////////////////////
		// find all results from the last N days
		if (@$args['days']) {
			$dateInThePast = date("Y-m-d H:i:s", strtotime("- {$args['days']} days"));
	
			// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
	 		$sql = "SELECT `r`.`result_modified`, `r`.`user_ID`, `r`.`fixture_ID`, `r`.`result_goals_b`, `r`.`result_goals_w`, `m`.`matchday_date`, `l`.`league_short_name`, `l`.`league_slug`, `l`.`region_ID`,"
	 			. " `t_b`.`team_name` AS `team_b_name`,"
	 			. " `t_w`.`team_name` AS `team_w_name`"
	 			. " FROM `{$resultsTable}` AS `r`"
				. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`fixture_ID` = `r`.`fixture_ID`"
				. " LEFT JOIN `{$matchdaysTable}` AS `m` ON `m`.`matchday_ID` = `f`.`matchday_ID`"
				. " LEFT JOIN `{$leaguesTable}` AS `l` ON `l`.`league_ID` = `m`.`league_ID`"
				. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
				. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
//				. " WHERE ("
//				. " 	ISNULL(`r`.`result_ID`)"
//				. " 	OR"
//				. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
//				. " )"
				. " WHERE `r`.`result_modified` > '{$dateInThePast}'"
				. " ORDER BY `r`.`result_modified` DESC"
				;
			parent::$_wpdb->get_results($sql);
	
			if (parent::$_wpdb->num_rows >= $args['num']) {
				$ret['status'] = 'OK';
				$ret['limit']  = 'days';
				$ret['result'] = parent::$_wpdb->last_result;
				return $ret;
			}
		}
		
		////////////////////////////////////////////////////////////////
		// find the last $args['num'] results
		
		$beginOfSeason = UWR1RESULTS_SEASON . '-09-01';
		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `r`.`result_modified`, `r`.`user_ID`, `r`.`fixture_ID`, `r`.`result_goals_b`, `r`.`result_goals_w`, `m`.`matchday_date`, `l`.`league_short_name`, `l`.`league_slug`, `l`.`region_ID`,"
 			. " `t_b`.`team_name` AS `team_b_name`,"
 			. " `t_w`.`team_name` AS `team_w_name`"
 			. " FROM `{$resultsTable}` AS `r`"
			. " LEFT JOIN `{$fixturesTable}` AS `f` ON `f`.`fixture_ID` = `r`.`fixture_ID`"
			. " LEFT JOIN `{$matchdaysTable}` AS `m` ON `m`.`matchday_ID` = `f`.`matchday_ID`"
			. " LEFT JOIN `{$leaguesTable}` AS `l` ON `l`.`league_ID` = `m`.`league_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
//			. " WHERE ("
//			. " 	ISNULL(`r`.`result_ID`)"
//			. " 	OR"
//			. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
//			. " )"
			. " WHERE `r`.`result_modified` >= '{$beginOfSeason}'"
			. " ORDER BY `r`.`result_modified` DESC"
			. " LIMIT 0, {$args['num']}"
			;

		// TODO: think about using parent::$_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		parent::$_wpdb->get_results($sql);

		if (parent::$_wpdb->num_rows > 0) {
			$ret['status'] = 'OK';
			$ret['limit']  = 'num';
			$ret['result'] = parent::$_wpdb->last_result;
			return $ret;
		}

		$ret['status'] = 'ZERO_RESULTS';
		return $ret;
	}
	
	function fillItem() {
		if (! ($this->hasProperty('fixtureId') && $this->fixtureId()) ) {
			if (!empty($_REQUEST['fixture_id'])) {
				$this->set( 'fixtureId', intval($_REQUEST['fixture_id']) );
			} else {
				return new Uwr1resultsException('No ID given');
			}
		}

		$this->populate(array(
			'id'                => $this->fixtureId(),
			'userId'            => $current_user->ID,
			'modified'          => 'NOW()',
			'goalsBlue'         => intval($_POST['goalsBlue']),
			'goalsWhite'        => intval($_POST['goalsWhite']),
			'goalsHalfBlue'     => -1,
			'goalsHalfWhite'    => -1,
			'goalsRegularBlue'  => -1,
			'goalsRegularWhite' => -1,
			'comment'           => trim($_POST['comment']),
		));
	}
} // Uwr1resultsModelResult
Uwr1resultsModelResult::initTable('Uwr1resultsModelResult', UWR1RESULTS_TBL_RESULTS);
