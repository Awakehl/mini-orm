<?php

/**
 * MySql records Iterator.
 * @author AwakeHL
 * @license GPL
 */
class MysqlIterator implements Iterator {
	private $_query = null;
	/** @var array */
	private $_row = null;
	/** @var Resource */
	private $_result = null;
	private $_fetched = false;
	protected $_key = 0;

	private static $link;

	public function __construct($query) {
		$this->_query = $query;
	}

   public function key() {
	   return $this->_key;
	}

	public function current() {
		return $this->_row;
	}

	public function next() {
		if (!$this->_fetched) {
			$this->rewind();
			return;
		}
		$this->_row = mysql_fetch_assoc($this->_result);
		$this->_key++;
	}

	public function rewind() {
		if ($this->_fetched) {
			if ($this->_key) {
				mysql_data_seek($this->_result, 0);
			}
		} else {
			$this->fetch();
		}
		$this->_row = mysql_fetch_assoc($this->_result);
		$this->_key = 0;
	}

	public function query() {
		return mysql_query($this->_query, self::$link);
	}

	public function valid() {
		return ($this->_row ? true : false);
	}

	protected function fetch() {
		if ($this->_fetched)
			return;
		if (!self::$link) {
			self::$link = mysql_connect(Config::DB_HOST, Config::DB_LOGIN, Config::DB_PASSWORD);
			if (!self::$link || !mysql_select_db(Config::DB_NAME, self::$link)) {
				throw new Exception('Cannot connect to DB');
			}
		}
		$this->_result = $this->query();
		$this->_fetched = true;
	}

	public function insert() {
		$this->query();
		return mysql_insert_id(self::$link);
	}

	public function reset() {
		$this->_fetched = null;
		$this->_key = null;
		$this->_row = null;
		$this->_result = null;
	}
}