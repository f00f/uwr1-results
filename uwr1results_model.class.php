<?php
/*
Plugin URI: http://uwr1.de/
Author: Hannes Hofmann
Author URI: http://uwr1.de/
*/

require_once 'uwr1res_config.inc.php';

class Uwr1resultsModel {
	/**
	* mapping must be propertyName => dbField  
	*/  
	protected $dbMapping = array();
	protected $externalProperties = array();
	
	protected $properties = array();

	/**
	 * The table that belongs to this model.
	 *
	 * @static
	 * @access private
	 */
	protected static $tables=array();
	
	/**
	 * The instance of this object.
	 * Static will act like a global variabile
	 *
	 * @static
	 * @access private
	 */
//	private static $instance=NULL;

	/**
	 * Database connection wrapper (from Wordpress).
	 * Initialized in the c'tor
	 *
	 * @static
	 * @access private
	 */
	protected static $_wpdb;

	/**
	 * Constructor.
	 * Init private member variables.
	 * Make sure that no one can build this object.
	 *
	 * @access private
	 * @return void
	 */
	protected function __construct() {
		global $wpdb;

		$this->_wpdb =& $wpdb;
		$this->_wpdb->show_errors();

		foreach ($this->dbMapping as $prop => $dbField) {
			$this->properties[ $prop ] = '';
		}
		foreach ($this->externalProperties as $prop => $dbField) {
			$this->properties[ $prop ] = '';
		}

		if (method_exists($this, 'init')) {
			$this->init();
		}
	}
	
	/**
	 * Empty clone function.
	 * Make sure that no one will get a copy of this object
	 *
	 * @access private
	 * @return void
	 */
	private function __clone() {  }

	/**
	 * Return the single instance of this class.
	 * The instance is created if neccessary
	 *
	 * @return Object   The singleton instance
	 */
/*
	public static function instance() {
		if (!self::$instance) {
			self::$instance = new Uwr1resultsModel();
		}
		return self::$instance;
	}
*/
	public function __call($method, $params) {
		//print get_class($this).'::__call';
		
		// universal get/set method
		if (isset($this->properties[ $method ])) {
			if (0 == count($params)) {
				//print 'returning '.$method;
				return $this->properties[ $method ];
			} else {
				trigger_error('To many arguments for method ' . get_class() . "::{$method}()", E_USER_ERROR);
			}
		}
		$stack=debug_backtrace();
		//$stackEntry =& $stack[2];
		//print_r($stack);
		//trigger_error('Call to undefined method ' . get_class() . "::{$method}()  FN:{$stackEntry['function']}(), FILE:{$stackEntry['file']}, LINE:{$stackEntry['line']}", E_USER_ERROR);
		trigger_error('Call to undefined method ' . $stack[1]['class'] . "::{$stack[1]['function']}() in {$stack[2]['class']}::{$stack[2]['function']}() [Line <b>{$stack[1]['line']}</b> in <b>{$stack[1]['file']}</b>]", E_USER_ERROR);
		exit;
	}

	public function hasProperty($propName) {
		return isset($this->properties[ $propName ]);
	}

	protected function set($propName, $value) {
		if ( !isset($this->properties[ $propName ]) ) {
			return false;
		}
		$this->properties[ $propName ] = $value;
		return true;
	}

	public static function initTable($classname, $table) {
        self::$tables[$classname] = $table;
    }
	public static function getTable($classname) {
		return @self::$tables[$classname];
	}

	/**
	 * Create the database table.
	 * TODO: implement
	 */
	public function createTable() {
		trigger_error('You have to overwrite Uwr1resultsModel::createTable()', E_USER_ERROR);
		exit;
	}

	/**
	 * Check whether an ID is a valid event ID.
	 * The current implementation just checks if the ID is a number
	 *
	 * @param $id Mixed   ID under test
	 * @return Boolean   Validity
	 */
	public static function isValidId($id) {
		$id = (int)$id;
		return !empty($id);
	}

	public function found() {
		return $this->isValidId( $this->properties['id'] );
	}


	// generic implementation returns emtpy string
	protected function leagueSlug() {
		return '';
	}
	
	// FIND METHODS
		
	public function findById( $id = null ) {
		if (is_null($id)) {
			exit;
		}
		
		$idDbField = @$this->dbMapping['id'];
		if ('' == $idDbField) {
			exit;
		}

 		$sql = "SELECT * FROM `".self::getTable(get_class($this))."`"
			. " WHERE `{$idDbField}` = '".intval($id)."'";

		return $this->findFirst($sql);
	}

	public function findAll( $params=array() ) {
		$fields = '*';
		$where = '';
		$order = '';

		if ( is_string(@$params['fields']) ) {
			$fields = Uwr1resultsHelper::sqlEscape( $params['fields'] );
		}
		if ( is_string(@$params['where']) ) {
			$where = ' WHERE '.$params['where'];
		}
 		$sql = "SELECT {$fields} FROM `".self::getTable(get_class($this))."`"
		 	. $where
			. $order;

		global $wpdb;
		return $wpdb->get_results($sql);
	}

	protected function findFirst($sql) {
		global $wpdb;
		$result =& $wpdb->get_row($sql);

		foreach ($this->dbMapping as $prop => $dbField) {
			$this->properties[ $prop ] = $result->$dbField;
		}
		foreach ($this->externalProperties as $prop => $dbField) {
			$this->properties[ $prop ] = @$result->$dbField;
		}

		return $this;
	}

	public function notifyJsonCache($leagueSlug = '', $caller = 'undefined') {
		if (!$leagueSlug) {
			$subject = "JsonCache Debug Message ---";
			$mail = "notifyJsonCache wurde ohne \$leagueSlug aufgerufen von {$caller}";
			//mail('hannes@uwr1.de', $subject, $mail);
			return;
		}

		$wgetUrl = 'not set';
		$doWget  = 'nein';
		if (defined('UWR1RESULTS_JSON_CACHE_UPDATE_URL') && UWR1RESULTS_JSON_CACHE_UPDATE_URL != '') {
			//wget(UWR1RESULTS_JSON_CACHE_UPDATE_URL . );
			$doWget = 'ja';
			$wgetUrl = UWR1RESULTS_JSON_CACHE_UPDATE_URL . '?' . $leagueSlug;
			file_get_contents($wgetUrl);
		}

		/*
		$subject = "[JsonCache] Debug Message (notifyJsonCache) +++";
		$mail = "notifyJsonCache wurde aufgerufen von {$caller}\n"
			. "leagueSlug: {$leagueSlug}\n"
			. "doWget....: {$doWget}\n"
			. "wgetUrl...: {$wgetUrl}\n"
			;
		mail('hannes@uwr1.de', $subject, $mail, "From: [JsonCache] Debug <jsoncache@uwr1.de>\n\n");
		*/
		return;
	}

	public function populate($values) {
		foreach ($values as $prop => $value) {
			$this->set($prop, $value);
		}
	}

	// @param bool notifyJsonCache: default is to send notification to JsonCache (App Engine)
	public function save($notifyJsonCache = true) {
		// TODO: check permissions
		Uwr1resultsHelper::enforcePermission( 'save' );

		$fields = array();
		$values = array();
		foreach ($this->dbMapping as $prop => $dbField) {
			$fields[] = $dbField;
			$values[] = (is_string($this->$prop()) ? "'".trim($this->$prop())."'" : trim($this->$prop()));
		}
		$fieldsStr = "`" . implode("`, `", $fields) . "`";
		$valuesStr = implode(", ", $values);

		$sql = 'REPLACE INTO `'.self::getTable(get_class($this)).'`'
			. " ({$fieldsStr})"
			. ' VALUES'
			. " ({$valuesStr})";
		//print $sql . ' (Uwr1resultsModel::save)'; exit;
		global $wpdb;
		$res = $wpdb->query($sql);

		if ($notifyJsonCache) {
			$this->notifyJsonCache($this->leagueSlug(), __CLASS__ . ' -- ' . self::getTable(get_class($this)));
		}
		return $res;
	}
} // Uwr1resultsModel
