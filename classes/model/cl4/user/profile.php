<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_User_Profile extends Model_User {
	protected $_override_properties = array(
		'_table_columns' => array(
			'id' => array(
				'edit_flag' => FALSE,
			),
			'password' => array(
				'edit_flag' => FALSE,
			),
			'active_flag' => array(
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
			'force_update_profile_flag' => array(
				'edit_flag' => FALSE,
			),
			'force_update_password_flag' => array(
				'edit_flag' => FALSE,
			),
		),
	);
} // class