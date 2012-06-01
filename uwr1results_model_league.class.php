<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';
require_once 'uwr1results_ranking.class.php';

class Uwr1resultsModelLeague
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id'        => 'league_ID',
		'regionId'  => 'region_ID',
		'name'      => 'league_name',
		'shortName' => 'league_short_name',
		'slug'      => 'league_slug',
		'level'     => 'league_level',
		'notes'     => 'league_notes',
	);
	
	private $matchdays = null;
	private $results = null;
	private $ranking = null;

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
			self::$instance = new Uwr1resultsModelLeague();
		}
		return self::$instance;
	}

	protected function init() {
		//$this->table = UWR1RESULTS_TBL_LEAGUES;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$leaguesTable = parent::getTable(get_class($this));
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$leaguesTable}` (
	`league_ID`         int(11) NOT NULL auto_increment,
	`region_ID`         int(11) NOT NULL default '0',
	`league_name`       varchar(50) collate utf8_general_ci NOT NULL default '',
	`league_short_name` varchar(15) collate utf8_general_ci NOT NULL default '',
	`league_slug`       varchar(50) collate utf8_general_ci NOT NULL default '',
	`league_level`      tinyint(4) NOT NULL default '0',
	`league_notes`      varchar(255) collate utf8_general_ci NOT NULL default '',
	PRIMARY KEY (`league_ID`),
	KEY `league_slug` (`league_slug`),
	KEY `league_level` (`league_level`),
	KEY `region_ID` (`region_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		return $this->_wpdb->query($sql);
	}

	protected function leagueSlug() {
		return $this->slug();
	}

	// try to find league_id automatically from request
	public function autoId() {
		if (!empty($_REQUEST['league_id'])) {
			$id = intval($_REQUEST['league_id']);
			if (Uwr1resultsModelLeague::isValidId($id)) {
				$this->id = $id;
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	// ACCESSORS
	
	public function shortName() {
		if( isset($this->properties[ 'shortName' ]) && !$this->properties[ 'shortName' ] ) {
			$this->set('shortName', $this->name());
		}
		return $this->properties[ 'shortName' ];
	}

	public function &matchdays() {
		if (is_null($this->matchdays)) {
			$this->matchdays = array();
			if ($this->regionId() > 0) {
				$matchdays =& Uwr1resultsModelMatchday::instance()->findByLeagueId( $this->id() ); // getMatchdays($league /* [$season,$tournament] */);
			} else {
				$matchdays =& Uwr1resultsModelMatchday::instance()->findByTournamentId( $this->id() ); // getMatchdays($league /* [$season,$tournament] */);
			}
			foreach ($matchdays as $m) {
				$this->matchdays[ $m->matchday_order ] = $m;
			}
		}
		return $this->matchdays;
	}

	public function &results() {
		if (is_null($this->results)) {
			// load results
			$this->results = array();
			if ($this->regionId() > 0) {
				$this->results =& Uwr1resultsModelResult::instance()->findByLeagueId( $this->id() ); // getMatchdays($league /* [$season,$tournament] */);
			} else {
				$this->results =& Uwr1resultsModelResult::instance()->findByTournamentId( $this->id() ); // getMatchdays($league /* [$season,$tournament] */);
			}
		}
		return $this->results;
	}

	public function &rankingDV() {
        $resolveH2H = true;
		return $this->ranking($resolveH2H);
	}

	public function &ranking($resolveH2H = false) {
		if (is_null($this->ranking)) {
			$this->ranking = new Uwr1resultsRanking($this->results());
            $this->ranking->sort($resolveH2H);
		}
		return $this->ranking;
	}

	// TODO: move to ModelTeam::saveMany
	public function saveTeams($teams) {
		// TODO: check permissions
		if (!$this->id || !is_array($teams) || count($teams) < 1) {
			return false;
		}

		$this->_wpdb->show_errors(true);
		$leaguesTeamsTable = parent::getTable('Uwr1resultsModelTeam');

		// clear table
		$sql = "DELETE FROM `{$leaguesTeamsTable}`"
			. " WHERE `league_ID` = {$this->id}";
			// . " AND `season` = {$SEASON}";
		//$this->_wpdb->query($sql);

		$rmt =& Uwr1resultsModelTeam::instance();
		$numTeams = count($teams);
		for ($t=0; $t<$numTeams; ++$t) {
			$teamName =& $teams[ $t ];
			if (!$teamName) {
				continue;
			}
			$team = $rmt->findByName($teamName);
			$sql = "REPLACE INTO `{$leaguesTeamsTable}`"
				. " (`league_ID`, `team_number`, `team_ID`) VALUES"
				. " ({$this->id}, {$t}, '{$team->team_ID}')";
			$this->_wpdb->query($sql);
		}

		//$this->notifyJsonCache($this->leagueSlug(), __FILE__);
		$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . parent::getTable(get_class($this)));
	}


	// FIND METHODS
		
	public function findById($id = null, $season = null) {
		if (is_null($id)) {
			exit;
		}

		$id = intval($id);
		$leaguesTable   = parent::getTable(get_class($this));
		$regionsTable   = parent::getTable('Uwr1resultsModelRegion');

		$sql = "SELECT `l`.*, `r`.* FROM `{$leaguesTable}` AS `l`"
			. " LEFT OUTER JOIN `{$regionsTable}` AS `r` ON `r`.`region_ID` = `l`.`region_ID`"
			. " WHERE `l`.`league_ID` = {$id}";	

		return $this->findFirst($sql);
	}
	
	public function findBySlug($slug = null, $season = null) {
		if (is_null($slug)) {
			exit;
		}
	
		$slug = Uwr1resultsHelper::slugify($slug);
		$slug = Uwr1resultsHelper::sqlEscape($slug);
		// FIXME: make slug db-safe
	
		$leaguesTable   = parent::getTable(get_class($this));
		$regionsTable   = parent::getTable('Uwr1resultsModelRegion');
	
	
		$sql = "SELECT `l`.*, `r`.* FROM `{$leaguesTable}` AS `l`"
			. " LEFT OUTER JOIN `{$regionsTable}` AS `r` ON `r`.`region_ID` = `l`.`region_ID`"
			. " WHERE `l`.`league_slug` = '{$slug}'";	
	
		return $this->findFirst($sql);
	}
} // Uwr1resultsModelLeague
Uwr1resultsModelLeague::initTable('Uwr1resultsModelLeague', UWR1RESULTS_TBL_LEAGUES);
