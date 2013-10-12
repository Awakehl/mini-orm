<?php

/**
 * Int field.
 * @author AwakeHL
 * @license GPL
 */
class FInt extends FField {

	public function get() {
		return intval($this->_value);
	}

}