<?php

/**
 * Model relations storage and map.
 * @author AwakeHL
 * @license GPL
 */
class Relations {
	private static $hasMany = array();
	private static $belongsTo = array();

	/**
	 * Has One-to-Many relation?
	 * @param string $model Class Name.
	 * @param string $child Class Name.
	 * @return bool
	 */
	public static function hasMany($model, $child) {
		return isset(self::$hasMany[$model][$child]);
	}

	/**
	 * Has Many-to-One relation?
	 * @param string $model
	 * @param string $parent
	 * @return bool
	 */
	public static function belongsTo($model, $parent) {
		return isset(self::$belongsTo[$model][$parent]);
	}

	/**
	 * Add relation "Belongs To".
	 * @param $model
	 * @param $parentModel
	 */
	public static function addBelongsTo($model, $parentModel) {
		if(!isset(self::$belongsTo[$model])) {
			self::$belongsTo[$model] = array();
		}
		self::$belongsTo[$model][$parentModel] = $parentModel;
	}

	/**
	 * Get all Parent Models through "Belongs To".
	 * @param $model
	 * @return array
	 */
	public static function getBelongsTo($model) {
		if(isset(self::$belongsTo[$model])) {
			return self::$belongsTo[$model];
		}
		return array();
	}

	/**
	 * Add "Has Many" relation.
	 * @param $model
	 * @param $child
	 */
	public static function addHasMany($model, $child) {
		if(!isset(self::$hasMany[$model])) {
			self::$hasMany[$model] = array();
		}
		self::$hasMany[$model][$child] = $child;
	}
}