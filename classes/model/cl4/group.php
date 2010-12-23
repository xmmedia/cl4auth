<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default permission
 */
class Model_cl4_Group extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'group';
	public $_table_name_display = 'Group';

	// Relationships
	protected $_has_many = array(
		'user' => array('through' => 'user_group', /*'foreign_key' => 'user_id', 'far_key' => 'group_id'*/),
		'permission' => array('through' => 'group_permission', /*'foreign_key' => 'permission_id', 'far_key' => 'group_id'*/),
	);

	// Validation rules
	protected $_rules = array(
		'name' => array(
			'not_empty'  => NULL,
			'max_length' => array(100),
		),
	);

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'name' => 'Name',
		'description' => 'Description',
	);

	protected $_sorting = array(
		'name' => 'ASC',
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
		'name' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'description' => array(
			'field_type' => 'textarea',
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

	/**
	 * @var array $_display_order The order to display columns in, if different from as listed in $_table_columns.
	 * Columns not listed here will be added beneath these columns, in the order they are listed in $_table_columns.
	 */
	protected $_display_order = array(
		10 => 'id',
		20 => 'name',
		30 => 'display_order',
	);
} // class