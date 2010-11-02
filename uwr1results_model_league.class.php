<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


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
		$this->_table = UWR1RESULTS_TBL_LEAGUES;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$uwr1resultsTable = $this->table();
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$uwr1resultsTable}` (
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

	private function createRanking() {
		$this->ranking = array();

		foreach ($this->results() as $f) {
			// add all teams to the ranking
			if ($f->t_b_name && $f->t_w_name) {
				$this->ranking[ $f->t_b_ID ]['id'] = $f->t_b_ID;
				$this->ranking[ $f->t_b_ID ]['name'] = $f->t_b_name;
				$this->ranking[ $f->t_w_ID ]['id'] = $f->t_w_ID;
				$this->ranking[ $f->t_w_ID ]['name'] = $f->t_w_name;
			}
			if (!$f->result_ID) { continue; } // don't count fixtures that don't have results
			if ($f->fixture_friendly) {
				// don't take friendly games into account for ranking
				$this->ranking[ $f->t_b_ID ]['friendlyMatchesPlayed']++;
				$this->ranking[ $f->t_w_ID ]['friendlyMatchesPlayed']++;
				continue;
			}
	
			$this->ranking[ $f->t_b_ID ]['id'] = $f->t_b_ID;
			$this->ranking[ $f->t_b_ID ]['name'] = $f->t_b_name;
			$this->ranking[ $f->t_b_ID ]['goalsPos'] += $f->result_goals_b;
			$this->ranking[ $f->t_b_ID ]['goalsNeg'] += $f->result_goals_w;
			$this->ranking[ $f->t_b_ID ]['pointsPos'] += $f->result_points_b;
			$this->ranking[ $f->t_b_ID ]['pointsNeg'] += $f->result_points_w;
			$this->ranking[ $f->t_b_ID ]['matchesPlayed']++;
	
			$this->ranking[ $f->t_w_ID ]['id'] = $f->t_w_ID;
			$this->ranking[ $f->t_w_ID ]['name'] = $f->t_w_name;
			$this->ranking[ $f->t_w_ID ]['goalsPos'] += $f->result_goals_w;
			$this->ranking[ $f->t_w_ID ]['goalsNeg'] += $f->result_goals_b;
			$this->ranking[ $f->t_w_ID ]['pointsPos'] += $f->result_points_w;
			$this->ranking[ $f->t_w_ID ]['pointsNeg'] += $f->result_points_b;
			$this->ranking[ $f->t_w_ID ]['matchesPlayed']++;
		}
	
		foreach ($this->ranking as $id => $team) {
			$this->ranking[ $id ]['goalsDiff']  = $team['goalsPos']  - $team['goalsNeg']; 
			$this->ranking[ $id ]['pointsDiff'] = $team['pointsPos'] - $team['pointsNeg']; 
			$this->ranking[ $id ]['head2head'] = false;
			$this->ranking[ $id ]['head2headTeams'] = array();
			if (!$this->ranking[ $id ]['matchesPlayed']) {
				$this->ranking[ $id ]['matchesPlayed'] = 0;
				$this->ranking[ $id ]['pointsPos']     = '&mdash;';
				$this->ranking[ $id ]['goalsPos']      = '&ndash;';
				$this->ranking[ $id ]['goalsNeg']      = '&ndash;';
				$this->ranking[ $id ]['goalsDiff']     = '&mdash;'; 
			}
		}
	}

	private function sortRanking() {
		uasort($this->ranking, 'uwr1resLeagueRanking'); // usort, uasort, uksort
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

	public function &ranking() {
		if (is_null($this->ranking)) {
			$this->createRanking();
			$this->sortRanking();
		}
		return $this->ranking;
	}

	// TODO: move to ModelTeam::saveMany
	public function saveTeams($teams) {
		if (!$this->id || !is_array($teams) || count($teams) < 1) {
			return false;
		}

		$this->_wpdb->show_errors(true);
		$leaguesTeamsTable = Uwr1resultsModelTeam::instance()->table();

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
		$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . $this->table());
	}


	// FIND METHODS
		
	public function findById($id = null, $season = null) {
		if (is_null($id)) {
			exit;
		}

		$id = intval($id);
		$leaguesTable   = $this->table();
		$regionsTable   = Uwr1resultsModelRegion::instance()->table();

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
	
		$leaguesTable   = $this->table();
		$regionsTable   = Uwr1resultsModelRegion::instance()->table();
	
	
		$sql = "SELECT `l`.*, `r`.* FROM `{$leaguesTable}` AS `l`"
			. " LEFT OUTER JOIN `{$regionsTable}` AS `r` ON `r`.`region_ID` = `l`.`region_ID`"
			. " WHERE `l`.`league_slug` = '{$slug}'";	
	
		return $this->findFirst($sql);
	}

	public static function leagueRankingCmp( &$a, &$b ) {
		// -1 : a < b : a before b 
		//  0 : a = b : a equal  b 
		// +1 : a > b : a after  b 
		
		// one team played friendly matches only = worse (ausser Konkurrenz)
		if ($a['friendlyMatchesPlayed'] && ! $a['matchesPlayed'] && $b['matchesPlayed']) {
			return 1;
		}
		if ($b['friendlyMatchesPlayed'] && ! $b['matchesPlayed'] && $a['matchesPlayed']) {
			return -1;
		}
		
		// more pointsPos = better
		if ($a['pointsPos'] <> $b['pointsPos']) {
			return ($a['pointsPos'] > $b['pointsPos']) ? -1 : 1;
		}
	
		else {
			//print '<!-- DV: '.$a['name'].' vs. '.$b['name'].' -->';
			$a['head2head'] = $b['head2head'] = true;
			$a['head2headTeams'][] = $b['name'];
			$b['head2headTeams'][] = $a['name'];
		}
		// HIER FEHLT:
		// direkter vgl pointsPos
		// direkter vgl goalsDiff
		// direkter vgl goalsPos
	
		// equal pointsPos => higher goalsDiff = better
		if ($a['goalsDiff'] <> $b['goalsDiff']) {
			return ($a['goalsDiff'] > $b['goalsDiff']) ? -1 : 1;
		}
	
		// equal goalsDiff => more goalsPos = better
		if ($a['goalsPos'] <> $b['goalsPos']) {
			return ($a['goalsPos'] > $b['goalsPos']) ? -1 : 1;
		}
	
		// Los
	
		return 0; // considered equal
	}
	
} // Uwr1resultsModelLeague

// wrapper funktion
function uwr1resLeagueRanking(&$a, &$b) {
	return Uwr1resultsModelLeague::leagueRankingCmp($a, $b);
}
?>
