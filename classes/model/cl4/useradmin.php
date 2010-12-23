<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_UserAdmin extends Model_User {
	protected $_override_properties = array(
		'_rules' => array(
			// remove the validation for password and password_confirm as we'll be a callback instead because we need to check if the values have been passed
			'password' => array(),
			'password_confirm' => array(),
		),

		'_callbacks' => array(
			// add a callback to check the password
			'password' => array('check_password'),
		),

		'_table_columns' => array(
			'password' => array(
				'field_type' => 'password',
				'list_flag' => FALSE,
				'edit_flag' => TRUE,
			),
			'password_confirm' => array(
				'field_type' => 'password',
				'edit_flag' => TRUE,
			),
		),
		'_display_order' => array(
			45 => 'password_confirm',
		),
	);
} // class