<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Default permission
 */
class Model_cl4_AuthType extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'auth_type';
	public $_table_name_display = 'Auth Type';

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'name' => 'Name',
		'display_order' => 'Display Order',
	);

	// sorting
	protected $_sorting = array(
		'display_order' => 'ASC',
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
			'display_order' => 10,
			'is_nullable' => FALSE,
		),
		'name' => array(
			'field_type' => 'text',
			'display_order' => 20,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 30,
			),
		),
		'display_order' => array(
			'field_type' => 'text',
			'display_order' => 30,
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 6,
				'size' => 6,
			),
		),
	);

	protected $_belongs_to = array('auth_log' => array('model' => 'authlog'));
}