<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


class Uwr1resultsModelFixture
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id' => 'fixture_ID',
		'matchdayId' => 'matchday_ID',
		'order' => 'fixture_order',
		'friendly' => 'fixture_friendly',
		'date' => 'fixture_date',
		'time' => 'fixture_time',
		'blueId' => 'fixture_team_blue',
		'whiteId' => 'fixture_team_white',
	);
	protected $externalProperties = array(
		'blueName'  => 'team_blue',  // mixed in from teams
		'whiteName' => 'team_white', // mixed in from teams
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
			self::$instance = new Uwr1resultsModelFixture();
		}
		return self::$instance;
	}

	protected function init() {
		//$this->table = UWR1RESULTS_TBL_FIXTURES;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$fixturesTable = parent::getTable(get_class($this));
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$fixturesTable}` (
	`fixture_ID`          int(11) NOT NULL auto_increment,
	`matchday_ID`         int(11) NOT NULL default '0',
	`fixture_order`       tinyint(4) NOT NULL default '0',
	`fixture_friendly`    enum('0', '1') NOT NULL default 0,
	`fixture_date`        date NOT NULL default '0000-00-00',
	`fixture_time`        time NOT NULL default '00:00:00',
	`fixture_team_blue`   int(11) NOT NULL default '0',
	`fixture_team_white`  int(11) NOT NULL default '0',
	PRIMARY KEY (`fixture_ID`),
	KEY `matchday_ID` (`matchday_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		$this->_wpdb->query($sql);
	}


	protected function leagueSlug() {
		$rmm = Uwr1resultsModelMatchday::instance();
		$matchday = $rmm->findById($this->matchdayId());
		return $matchday->leagueSlug();
	}


	public function saveMany($matchdayId) {
		if (!$matchdayId) {
			return new Uwr1resultsException('No ID given');
		}

		// TODO: check permissions
		Uwr1resultsHelper::enforcePermission( 'save' );

		// Set matchdayId here, because it is used by leagueSlug, which in turn is used while saving teams.
		$this->set('matchdayId', (int) $matchdayId);

		$t =& Uwr1resultsModelTeam::instance();
		$new_items = array();
		$success = true;
		foreach ($_POST['data'] as $id => $f) {
			if ( empty($f['team_blue']) || empty($f['team_white']) ) {
				continue;
			}

			// find team IDs
			// findOrAdd resp. make_sure_exists resp. replace_into
			$team =& $t->findByName( $f['team_blue'] );
			if (false === $team) {
				$t->populate(array(
					'id'   => 0,
					'name' => $f['team_blue'],
					'slug' => Uwr1resultsHelper::slugify($f['team_blue']),
				));
				$t->save();
				$team =& $t->findByName( $f['team_blue'] );
				if (false === $team) {
					$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . parent::getTable(get_class($this)));
					die('error creating team '.$f['team_blue']);
				}
			}
			$f['blue_id']  = $team->team_ID;

			$team =& $t->findByName( $f['team_white'] );
			if (false === $team) {
				$t->populate(array(
					'id'   => 0,
					'name' => $f['team_white'],
					'slug' => Uwr1resultsHelper::slugify($f['team_white']),
				));
				$t->save();
				$team =& $t->findByName( $f['team_white'] );
				if (false === $team) {
					$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . parent::getTable(get_class($this)));
					die('error creating team '.$f['team_white']);
				}
			}
			$f['white_id'] = $team->team_ID;

			$f['friendly'] = strtolower(trim(@$f['friendly']));

			// make sure date and time values are defined
			if ( empty( $f['date'] ) ) {
				$f['date'] = '0000-00-00';
			}
			if ( empty( $f['time'] ) ) {
				$f['time'] = '00:00:00';
			}

			$this->populate(array(
				'id'         => (int) $f['id'],
				'matchdayId' => (int) $matchdayId,
				'order'      => (int) $f['order'],
				'friendly'   => '' . ((int) ('' != $f['friendly'] && 'off' != $f['friendly'])),
				'date'       => $f['date'],
				'time'       => $f['time'],
				'blueId'     => $f['blue_id'],
				'whiteId'    => $f['white_id'],
			));
			$rv = $this->save(false); // don't notifyJsonCache for each store
			if (false === $rv) {
				$success = false;
				break;
			}

			if (! (int)$f['id']) {
				global $wpdb;
				$f['id'] = $wpdb->insert_id;
			}
			$new_item = $this->findById((int)$f['id']);
			$new_items[] = $new_item;
		}

		// eventually update cache
		$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . parent::getTable(get_class($this)));

		return $success;
	} // saveMany


	// FIND METHODS
		
	public function findById($id = null) {
		if (is_null($id)) {
			exit;
		}
		
		$id = intval($id);
		
		$fixturesTable  = parent::getTable(get_class($this));
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');
		
		$sql = "SELECT `f`.*, `t_b`.`team_name` AS `team_blue`, `t_w`.`team_name` AS `team_white` FROM `{$fixturesTable}` AS `f`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `f`.`fixture_ID` = {$id}";

		return $this->findFirst($sql);
	}

	public function findByMatchdayId( $matchdayId=0 ) {
		$matchdayId = intval( $matchdayId );
		if (!$matchdayId) {
			exit;
		}

		$fixturesTable  = parent::getTable(get_class($this));
		$teamsTable     = parent::getTable('Uwr1resultsModelTeam');
		$resultsTable   = parent::getTable('Uwr1resultsModelResult');

		// `f`.* MUST be placed after `r`.* otherwise r.fixture_ID (which may be NULL) overwrites f.fixture_ID
 		$sql = "SELECT `r`.*, `f`.*,"
 			. " `t_b`.`team_name` AS `t_b_name`,"
 			. " `t_w`.`team_name` AS `t_w_name`"
 			. " FROM `{$fixturesTable}` AS `f`"
			. " LEFT OUTER JOIN `{$resultsTable}` AS `r` ON `r`.`fixture_ID` = `f`.`fixture_ID`"
			. " LEFT JOIN `{$teamsTable}` AS `t_b` ON `t_b`.`team_ID` = `f`.`fixture_team_blue`"
			. " LEFT JOIN `{$teamsTable}` AS `t_w` ON `t_w`.`team_ID` = `f`.`fixture_team_white`"
			. " WHERE `f`.`matchday_id` = '{$matchdayId}'"
			. " AND ("
			. " 	ISNULL(`r`.`result_ID`)"
			. " 	OR"
			. " 	`r`.`result_ID` = (SELECT `r2`.`result_ID` FROM `{$resultsTable}` AS `r2` WHERE `r2`.`fixture_ID` = `f`.`fixture_ID` ORDER BY `r2`.`result_modified` DESC LIMIT 1)"
			. " )"
			. " ORDER BY `f`.`fixture_date`, `f`.`fixture_time`, `f`.`fixture_ID`"
			;

		// TODO: think about using $this->_wpdb->get_row();
		// OBJECT, ARRAY_A, ARRAY_N

		return $this->_wpdb->get_results($sql);
	}

	public function findFirst($sql) {
		parent::findFirst($sql);
		// load matchday data
		$this->properties['matchday'] =& Uwr1resultsModelMatchday::instance()->findById( $this->matchdayId() );
		$this->properties['location'] =& $this->matchday()->location();
		if ('0000-00-00' == $this->date()) {
			$this->properties['date'] =& $this->matchday()->date();
		}
		// load result data
		$this->properties['result'] =& Uwr1resultsModelResult::instance()->findByFixtureId( $this->id() );
		return $this;
	}
} // Uwr1resultsModelFixture
Uwr1resultsModelFixture::initTable('Uwr1resultsModelFixture', UWR1RESULTS_TBL_FIXTURES);
