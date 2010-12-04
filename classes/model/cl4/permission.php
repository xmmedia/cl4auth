<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default permission
 */
class Model_cl4_Permission extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'permission';
	public $_table_name_display = 'Permission';

	// Relationships
	protected $_has_many = array('group' => array('through' => 'group_permission'));

	// Validation rules
	protected $_rules = array(
		'permission' => array(
			'not_empty'  => NULL,
			'max_length' => array(255),
		),
		'name' => array(
			'max_length' => array(150),
		),
		'description' => array(
			'max_length' => array(500),
		),
	);

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'permission' => 'Permission',
		'name' => 'Name',
		'description' => 'Description',
	);

	// column definitions
	protected $_table_columns = array(
		/**
		* see http://v3.kohanaphp.com/guide/api/Database_MySQL#list_columns for all possible column attributes
		* see the modules/cl4/config/cl4orm.php for a full list of cl4-specific options and documentation on what the options do
		*/
		'id' => array(
			'field_type' => 'hidden',
			'display_order' => 10,
			'list_flag' => FALSE,
			'edit_flag' => TRUE,
			'view_flag' => FALSE,
			'search_flag' => FALSE,
			'is_nullable' => FALSE,
		),
		'permission' => array(
			'field_type' => 'text',
			'display_order' => 20,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'name' => array(
			'field_type' => 'text',
			'display_order' => 30,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'description' => array(
			'field_type' => 'textarea',
			'display_order' => 40,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
	);

	// Filters
	protected $_filters = array(
		TRUE => array('trim' => array()),
	);
} // class