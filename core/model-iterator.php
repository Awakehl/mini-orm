<?php

/**
 * Itarator of models.
 * @author AwakeHL
 * @license GPL
 */
class ModelIterator implements Iterator {

	/**
	 * @var string Current query.
	 */
	private $_query;
	/**
	 * @var array Models in the query.
	 */
	private $_models;
	/**
	 * @var array Query conditions.
	 */
	private $_conditions = array('where' => '', 'orderBy' => '', 'limit' => '');
	/**
	 * @var MysqlIterator SQL Iterator.
	 */
	private $_sqlIterator;
	/**
	 * @var Model Current Model in the iteration.
	 */
	private $_currentModel;
	/**
	 * @var int Cached total.
	 */
	private $_countTotal;

	/**
	 * @param array|string $models Model or Related Models Map (array).
	 */
	public function __construct($models) {
		if (!is_array($models)) {
			$models = array($models);
		}
		$this->_models = $models;

		$this->_query = 'SELECT '.Inflector::instance()->tableize(end($models)).'.* '
			.$this->getFrom($models).' '.$this->getWhere().' '.$this->orderBy().' '.$this->getLimit();

		$this->_sqlIterator = new MysqlIterator($this->_query);
	}

	/**
	 * Get instance of the Model iterator.
	 * @param array|string $models
	 * @return ModelIterator
	 */
	public static function get($models) {
		return new ModelIterator($models);
	}

	/**
	 * Get lase model.
	 * @return Model
	 */
	public function last() {
		foreach ($this->orderBy('id desc')->limit(1) as $model) {
			return $model;
		}
		return null;
	}

	/**
	 * Set-up condition.
	 * @param string $where
	 * @return ModelIterator
	 */
	public function findBy($where) {
		$conditions = $this->_conditions;
		$conditions['where'][] = $where;
		return new ModelIterator($this->_models, $conditions);
	}

	/**
	 * Set-up sorting.
	 * @param string $orderBy
	 * @return ModelIterator|string
	 */
	public function orderBy($orderBy = null) {
		if ($orderBy) {
			$conditions = $this->_conditions;
			$conditions['orderBy'][] = $orderBy;
			return new ModelIterator($this->_models, $conditions);
		} else {
			return !empty($this->_conditions['orderBy']) ? 'ORDER BY '.join(',', $this->_conditions['orderBy']) : '';
		}
	}

	/**
	 * Setup Limit, Offset.
	 * @param int $limit
	 * @param int $offset
	 * @return ModelIterator
	 */
	public function limit($limit, $offset = 0) {
		$conditions = $this->_conditions;
		$conditions['limit'][0] = $limit;
		$conditions['limit'][1] = $offset;
		return new ModelIterator($this->_models, $conditions);
	}

	/**
	 * Delete models.
	 */
	public function delete() {
		$table = Inflector::instance()->tableize(end($this->_models));
		$query = 'DELETE '.$table.' '.$this->getFrom($this->_models).' '.$this->getWhere();
		$sqlIterator = new MysqlIterator($query);
		$sqlIterator->query();
	}

	/**
	 * Get count if the models in the iterator.
	 * @return int
	 */
	public function count() {
		if ($this->_countTotal !== null) {
			return $this->_countTotal;
		}
		$sqlIterator = new MysqlIterator('SELECT COUNT(*) '.$this->getFrom($this->_models).' '.$this->getWhere());
		$sqlIterator->rewind();
		$row = $sqlIterator->current();
		$this->_countTotal = reset($row);
		return $this->_countTotal;
	}

	/**
	 * Reload (refresh) iterator from DB.
	 * @return void
	 */
	public function reload() {
		$this->_countTotal = null;
		$this->_sqlIterator->reset();
		$this->_sqlIterator->query();
	}

	/**
	 * Get model. Iterator method "current".
	 * @return Model
	 */
	public function current() {
		$class = end($this->_models);
		$this->_currentModel = new $class;
		$this->_currentModel->parse($this->_sqlIterator->current());
		return $this->_currentModel;
	}

	/**
	 * Get id. Iterator method "key".
	 * @return int
	 */
	public function key() {
		return $this->_currentModel->id->get();
	}

	/**
	 * Check validity. Iterator method "valid".
	 * @return Model
	 */
	public function valid() {
		return $this->_sqlIterator->valid();
	}

	/**
	 * Iterator method "next"..
	 * @return void
	 */
	public function next() {
		$this->_sqlIterator->next();
	}

	/**
	 * Rewinds the iterator. Iterator method "rewind".
	 * @return void
	 */
	public function rewind() {
		$this->_sqlIterator->next();
	}

	/**
	 * Get "FROM" part.
	 * @param array $models
	 * @return string
	 * @throws Exception
	 */
	private function getFrom(array $models) {
		$fromModel = array_shift($models);
		$fromTable = Inflector::instance()->tableize($fromModel);
		$from = 'FROM '.$fromTable;
		foreach ($models as $model) {
			$modelTable = Inflector::instance()->tableize($model);
			if (Relations::hasMany($fromModel, $model)) {
				$from .= ' INNER JOIN '.$modelTable.' ON '.$fromTable.'.id='.$modelTable.'.'
					.Inflector::instance()->foreign_key($fromModel);
			} else if (Relations::belongsTo($fromModel, $model)) {
				$from .= ' INNER JOIN '.$modelTable.' ON '.$modelTable.'.id='.$fromTable.'.'
					.Inflector::instance()->foreign_key($model);
			} else {
			    throw new Exception('Model '.$model.' is not related to '.$fromModel);
			}
			$fromModel = array_shift($models);
		}
		return $from;
	}

	/**
	 * Get "WHERE" part.
	 * @return string
	 */
	private function getWhere() {
		return empty($this->_conditions['where']) ? '' : 'WHERE '.join(' AND ', $this->_conditions['where']);
	}

	/**
	 * Get "ORDER BY" part.
	 * @return string
	 */
	private function getOrderBy() {
		return empty($this->_conditions['where']) ? '' : 'WHERE '.join(' AND ', $this->_conditions['where']);
	}

	/**
	 * Get "LIMIT" part.
	 * @return string
	 */
	private function getLimit() {
		if (empty($conditions['limit'])) {
			return '';
		}
		$limit = 'LIMIT '.$this->_conditions['limit'][0];
		if (!empty($this->_conditions['limit'][1])) {
			$limit .=' OFFSET '.$this->_conditions['limit'][1];
		}
		return $limit;
	}

}