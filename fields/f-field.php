<?php

/**
 * Fields Base class.
 * @author AwakeHL
 * @license GPL
 */
class FField {

	/**
	 * @var mixed Fild value.
	 */
	protected $_value;
	/**
	 * @var string Property title,
	 */
	protected $_label;

	public function __construct($label) {
		$this->_label = $label;
	}

	public function set($value) {
		$this->_value = $value;
	}

	public function get() {
		return $this->_value;
	}

	public function label() {
		return $this->_label;
	}

	public function __toString() {
		return $this->_label.':'.$this->_value;
	}
}