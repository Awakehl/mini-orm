<?php

/**
 * Class autoloader.
 * @author AwakeHL
 * @license GPL
 */
class Autoload {

	private static $_config = array(
		'ModelIterator' => 'core/model-iterator.php',
		'MysqlIterator' => 'core/db/mysql-iterator.php',
		'Model' => 'core/model.php',
		'Relations' => 'core/relations.php',
		'Inflector' => 'external/inflector/inflector.php',
		'Inflections' => 'external/inflector/inflections.php',
		'FField' => 'fields/f-field.php',
		'FInt' => 'fields/f-int.php',
		'FDateTime' => 'fields/f-date-time.php',
		'FText' => 'fields/f-text.php',
		'FString' => 'fields/f-string.php',
		'FBoolean' => 'fields/f-boolean.php',
		'Config' => 'config.php'
	);

	public static function load($className) {
		if (isset(self::$_config[$className])) {
			require_once self::$_config[$className];
		}
	}
}

spl_autoload_register(array('Autoload', 'load'));