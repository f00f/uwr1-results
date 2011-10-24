<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';


class Uwr1resultsModelRegion
extends Uwr1resultsModel {
	protected $dbMapping = array(
		'id' => 'region_ID',
		'name' => 'region_name',
		'slug' => 'region_slug',
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
			self::$instance = new Uwr1resultsModelRegion();
		}
		return self::$instance;
	}

	protected function init() {
		//$this->table = UWR1RESULTS_TBL_REGIONS;
	}

	/**
	 * Create the database table.
	 */
	public function createTable() {
		$regionsTable = parent::getTable(get_class($this));
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$regionsTable}` (
	`region_ID`    INT(11) NOT NULL auto_increment,
	`region_name`  VARCHAR(50) collate utf8_general_ci NOT NULL default '',
	`region_slug`  VARCHAR(50) collate utf8_general_ci NOT NULL default '',
	PRIMARY KEY (`region_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
SQL;
		$this->_wpdb->query($sql);
	}

//		return $GLOBALS['RUL_HARDCODED']['region']['region_ID'];
//		return $GLOBALS['RUL_HARDCODED']['region']['region_name'];
//		return $GLOBALS['RUL_HARDCODED']['region']['region_slug'];

	// FIND METHODS
		
	public function findBySeason($season = null) {
		if (is_null($season)) {
			$season = Uwr1resultsController::season();
		}

		$regionsTable   = parent::getTable(get_class($this));
		$leaguesTable   = parent::getTable('Uwr1resultsModelLeague');

 		$this->_wpdb->show_errors();

		$sql = "SELECT `l`.*, `r`.* FROM `{$regionsTable}` AS `r`"
			. " LEFT JOIN `{$leaguesTable}` AS `l` ON `l`.`region_ID` = `r`.`region_ID`"
			. " ORDER BY `r`.`region_ID` < 0, `r`.`region_name` = 'Jugend', `r`.`region_name` = 'Damen', `r`.`region_name`,"
			. " `l`.`league_level`, CASE WHEN `r`.`region_ID` <0 THEN `l`.`league_ID` END DESC, `l`.`league_name`";

		return $this->_wpdb->get_results($sql);
	}
} // Uwr1resultsModelRegion
Uwr1resultsModelRegion::initTable('Uwr1resultsModelRegion', UWR1RESULTS_TBL_REGIONS);
?>