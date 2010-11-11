<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_UserProfile extends Model_User {
	protected $_override_properties = array(
		'_table_columns' => array(
			'id' => array(
				'edit_flag' => FALSE,
			),
			'password' => array(
				'edit_flag' => FALSE,
			),
			'inactive_flag' => array(
				'edit_flag' => FALSE,
			),
			'login_count' => array(
				'edit_flag' => FALSE,
			),
			'last_login' => array(
				'edit_flag' => FALSE,
			),
			'failed_login_count' => array(
				'edit_flag' => FALSE,
			),
			'last_failed_login' => array(
				'edit_flag' => FALSE,
			),
			'reset_token' => array(
				'edit_flag' => FALSE,
			),
		),
	);
} // class