<?php

/**
 * Base Model.
 * @author AwakeHL
 * @license GPL
 */
class Model {

	/** @var FInt */
	public $id;

	/**
	 * @var array Foreign Relations.
	 */
	private $_foreignKeys = array();

	/**
	 * @param int $id Id from DB.
	 */
	public function __construct($id = null) {
		$this->id = new FInt('Id');
		$this->init();
		if($id) {
			$this->id->set($id);
			$sql = new MysqlIterator('SELECT * FROM '.$this->getTableName().' id='.intval($id));
			$sql->rewind();
			$this->parse($sql->current());
		}
	}

	/**
	 * Set-up of the Model, Relations and Properties.
	 */
	public function init() {

	}

	/**
	 * Record init from DB row.
	 * @param $array
	 */
	public function parse(array $array) {
		foreach ($array as $key => $value) {
			if (isset($this->_foreignKeys[$key])) {
				$this->$key = $value;
			} else {
				$this->$key->set($value);
			}
		}
	}

	/**
	 * Many-to-One relation.
	 * @param string $model
	 */
	public function belongsTo($model) {
		Relations::addBelongsTo(get_class($this), $model);
		$foreignKey = Inflector::instance()->foreign_key($model);
		$this->_foreignKeys[$foreignKey] = $foreignKey;
	}

	/**
	 * Many-toMany relation.
	 * @param string $model
	 */
	public function hasMany($model) {
		Relations::addHasMany(get_class($this), $model);
	}

	/**
	 * Get properties of the Model.
	 * @return array
	 */
	public function getProperties() {
		$props = get_object_vars($this);
		unset($props['_foreignKeys']);
		return $props;
	}

	/**
	 * Add record to DB.
	 * @return int
	 */
	public function insert() {
		$data = $this->getDataForQuery();
		unset($data['id']);
		$fields = array_keys($data);
		$values = array_values($data);
		$query =
			'INSERT INTO '.$this->getTableName().'(`'.implode('`,`',$fields).'`) VALUES(\''.join("','", $values).'\')';
		$sqlIterator = new MysqlIterator($query);
		$id = $sqlIterator->insert();
		$this->id->set($id);
		return $id;
	}

	/**
	 * Update record in DB.
	 */
	public function update() {
		$data = $this->getDataForQuery();
		$fields = array_keys($data);
		$values = array_values($data);
		$parts = array();
		foreach ($fields as $i => $field) {
			$parts[] = "`".$field."`='".$values[$i]."'";
		}
		$query = 'UPDATE '.$this->getTableName().' SET '.join(',', $parts).' WHERE id="'.$this->id->get().'"';
		$sqlIterator = new MysqlIterator($query);
		$sqlIterator->query();
	}

	/**
	 * Save record.
	 */
	public function save() {
		if($this->id->get()) {
			$this->update();
		} else {
			$this->insert();
		}
	}

	/**
	 * Delete record.
	 */
	public function delete() {
		$query = 'DELETE FROM '.$this->getTableName().' WHERE id="'.$this->id->get().'"';
		$sqlIterator = new MysqlIterator($query);
		$sqlIterator->rewind();
	}

	/**
	 * Get table name.
	 * @return string
	 */
	private function getTableName() {
		return Inflector::instance()->tableize($this);
	}

	/**
	 * Prepare data for query.
	 * @return array
	 */
	private function getDataForQuery() {
		$props = $this->getProperties();
		$data = array();
		/** @var $prop FField */
		foreach ($props as $name => $prop) {
			if (isset($this->_foreignKeys[$name])) {
				$data[$name] = $prop;
			} else {
				$data[$name] = $prop->get();
			}
		}
		return $data;
	}

	/**
	 * Get related models as iterator.
	 * @param string $name
	 * @return ModelIterator|Model
	 */
	public function __get($name) {
		$modelClassName = get_class($this);
		$className = Inflector::instance()->camelize(Inflector::instance()->singularize($name));
		if (Relations::hasMany($modelClassName, $className)) {
			return ModelIterator::get(array($modelClassName, $className))->findBy(
				$this->getTableName().'.id = '.$this->id->get()
			);
		} else if (Relations::belongsTo($modelClassName, $className)) {
			$relationField = Inflector::instance()->foreign_key($className);
			return new $className($relationField);
		}
		return null;
	}

	/**
	 * Set parent model, foreign key, etc..
	 * @param string $name
	 * @param $value
	 */
	public function __set($name, $value) {
		$modelClassName = get_class($this);
		$className = Inflector::instance()->camelize($name);
		if (Relations::belongsTo($modelClassName, $className)) {
			$relationField = Inflector::instance()->foreign_key($className);
			$this->$relationField = $value->id->get();
		} else if(isset($this->_foreignKeys[$name])) {
			$this->$name = $value;
		}
	}

}