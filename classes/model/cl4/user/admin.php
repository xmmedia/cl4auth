<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_User_Admin extends Model_User {
	protected function _initialize() {
		// remove the validation for password and password_confirm as we'll be a callback instead because we need to check if the values have been passed
		//unset($this->_rules['password'], $this->_rules['password_confirm']);

		// add a callback to check the password
		//$this->_callbacks['password'] = array('check_password');

		$this->_table_columns['password']['field_type'] = 'password';
		$this->_table_columns['password']['list_flag'] = FALSE;
		$this->_table_columns['password']['edit_flag'] = TRUE;
		$this->_table_columns['password_confirm'] = array(
			'field_type' => 'password',
			'edit_flag' => TRUE,
		);

		$this->_display_order[45] = 'password_confirm';

		$this->_has_many['group']['edit_flag'] = TRUE;
		$this->_has_many['group']['view_flag'] = TRUE;

		parent::_initialize();
	} // function _initialize

	public function rules() {
		$rules = parent::rules();

		// replace the password rules with the custom password setting method
		$rules['password'] = array(
			array(array($this, 'check_password'), array(':validation', ':field', ':value')),
		);

		return $rules;
	} // function rules
} // class