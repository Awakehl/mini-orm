<?php

/**
 * Field DateTime.
 * @author AwakeHL
 * @license GPL
 */
class FDateTime extends FField {

	public function set($value) {
		$this->_value = is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value;
	}

}