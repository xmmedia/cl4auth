<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default permission
 */
class Model_cl4_UserGroup extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'user_group';
	public $_table_name_display = 'User - Group';
	protected $_primary_val = 'user_id'; // default: name (column used as primary value)

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'user_id' => 'User',
		'group_id' => 'Group',
	);

	// relationships
	protected $_belongs_to = array(
		'user' => array(),
		'group' => array(),
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
			'search_flag' => FALSE,
			'view_flag' => FALSE,
			'is_nullable' => FALSE,
		),
		'user_id' => array(
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
					'data' => "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM user ORDER BY first_name, last_name",
				),
			),
		),
		'group_id' => array(
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
					'data' => "SELECT id, name FROM `group` ORDER BY name",
				),
			),
		),
	);
} // class