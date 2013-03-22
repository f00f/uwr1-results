<?php
/**
 * Insert Header
 * Title: Cached Widget
 * License: GPLv3
 * Author: Hannes Hofmann
 * Author-URL: http://uwr1.de/
 *
 * Description: TODO
 */

if (!class_exists('CachedWidget')) {

abstract class CachedWidget extends WP_Widget {
	//private static $staticCacheKey = 'cache.widget.WIDGETNAME'; // sub-classes must declare this static property
	protected $m_cacheKey=''; // must be set in the derived class's c'tor
	protected $itemKey='';

	function GenerateCacheKey(&$args) {
		$this->itemKey  = serialize($args);
	}
	function GetCacheKey() {
		if (! $this->m_cacheKey) {
			die('CacheKey not set.');
		}
		return $this->m_cacheKey;
	}
	function CacheLookup(&$args) {
		$cache = get_option($this->GetCacheKey());
		$cache = json_decode($cache);
		if (@$cache->expires && time() > $cache->expires) {
			return false;
		}
		$this->GenerateCacheKey($args);
		if (!isset($cache->{$this->itemKey})) {
			return false;
		}
		return $cache->{$this->itemKey};
	}
	function StoreInCache(&$args, $value, $expires=0) {
		$this->GenerateCacheKey($args);
		$cache = get_option($this->GetCacheKey());
		if (!$cache) {
			add_option($this->GetCacheKey(), '', null, 'no');
		}
		$cache = json_decode($cache);
		if (!$cache) {
			$cache = new stdClass();
		}
		$cache->{$this->itemKey} = $value;
		$cache->expires = $expires;
		update_option($this->GetCacheKey(), json_encode($cache));
	}
	/**
	 * ClearCache my be called statically by a WP hook.
	 * Typical implementation:
	 *	static function ClearCache() {
	 *		delete_option(self::$staticCacheKey);
	 *	}
	 */
	abstract static function ClearCache();
	/*function RemoveCachedItem() {}*/
}

}
