<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_cl4_GroupPermission extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'group_permission';
	public $_table_name_display = 'Group - Permission';
	protected $_primary_val = 'group_id'; // default: name (column used as primary value)

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'group_id' => 'Group',
		'permission_id' => 'Permission',
	);

	// relationships
	protected $_belongs_to = array(
		'permission' => array(),
		'group' => array(),
	);

	// validation rules
	protected $_rules = array();

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
			'display_order' => 10,
			'is_nullable' => FALSE,
		),
		'group_id' => array(
			'field_type' => 'select',
			'display_order' => 20,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'sql',
					'data' => "SELECT id, name FROM `group` ORDER BY name",
				),
			),
		),
		'permission_id' => array(
			'field_type' => 'select',
			'display_order' => 30,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'sql',
					'data' => "SELECT id, permission FROM permission ORDER BY permission",
					'label' => 'permission',
				),
			),
		),
	);
} // class
