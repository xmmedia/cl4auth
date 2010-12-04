<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_cl4_AuthLog extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'auth_log';
	public $_table_name_display = 'Auth Log';
	protected $_primary_val = 'username'; // default: name (column used as primary value)

	protected $_belongs_to = array('user' => array('model' => 'user'));

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'user_id' => 'User',
		'username' => 'Username',
		'access_time' => 'Access Time',
		'auth_type_id' => 'Auth Type',
		'browser' => 'Browser',
		'ip_address' => 'IP Address',
	);

	// column definitions
	protected $_table_columns = array(
		/**
		* see http://v3.kohanaphp.com/guide/api/Database_MySQL#list_columns for all possible column attributes
		* see the modules/cl4/config/cl4orm.php for a full list of cl4-specific options and documentation on what the options do
		*/
		'id' => array(
			'field_type' => 'hidden',
			'list_flag' => FALSE,
			'edit_flag' => TRUE,
			'search_flag' => FALSE,
			'view_flag' => FALSE,
			'is_nullable' => FALSE,
		),
		'user_id' => array(
			'field_type' => 'select',
			'list_flag' => TRUE,
			'search_flag' => TRUE,
			'edit_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'sql',
					'data' => "SELECT id, CONCAT_WS('', first_name, ' ', last_name) AS name FROM `user` ORDER BY name",
				),
			),
		),
		'username' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'search_flag' => TRUE,
			'edit_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 100,
			),
		),
		'access_time' => array(
			'field_type' => 'datetime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'auth_type_id' => array(
			'field_type' => 'select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'sql',
					'data' => "SELECT id, name FROM `auth_type` ORDER BY display_order, name",
				),
			),
		),
		'browser' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'ip_address' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'size' => 15,
				'maxlength' => 15,
			),
		),
	);
}