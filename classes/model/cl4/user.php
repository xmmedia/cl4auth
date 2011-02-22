<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_User extends Model_Auth_User {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'user';
	public $_table_name_display = 'User';
	protected $_primary_val = 'username'; // default: name (column used as primary value)

	// don't reload the user object every time it's reloaded from the sessions for 2 reasons: (1) it fails, and (2) it's an extra unnecessary query
	protected $_reload_on_wakeup = FALSE;

	protected $_table_columns = array(
		/**
		* see http://v3.kohanaphp.com/guide/api/Database_MySQL#list_columns for all possible column attributes
		* see the modules/cl4/config/cl4orm.php for a full list of cl4-specific options and documentation on what the options do
		*/
		'id' => array(
			'field_type'     => 'hidden',
			'list_flag'      => FALSE,
			'edit_flag'      => TRUE,
			'search_flag'    => FALSE,
			'view_flag'      => FALSE,
			'is_nullable'    => FALSE,
		),
		'expiry_date' => array(
			'field_type'     => 'datetime',
			'list_flag'      => FALSE,
			'edit_flag'      => FALSE,
			'search_flag'    => FALSE,
			'view_flag'      => FALSE,
			'is_nullable'    => FALSE,
		),
		'username' => array(
			'field_type'     => 'text',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
			'field_attributes' => array(
				'maxlength'  => 100,
			),
		),
		'password' => array(
			'field_type'     => 'password',
			'list_flag'      => FALSE,
			'edit_flag'      => TRUE,
			'search_flag'    => FALSE,
			'view_flag'      => FALSE,
			'is_nullable'    => FALSE,
		),
		'first_name' => array(
			'field_type'     => 'text',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
			'field_attributes' => array(
				'maxlength'  => 100,
			),
		),
		'last_name' => array(
			'field_type'     => 'text',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
			'field_attributes' => array(
				'maxlength'  => 100,
			),
		),
		'active_flag' => array(
			'field_type'     => 'checkbox',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
			'field_options'  => array(
				'default_value' => 1,
			),
		),
		'login_count' => array(
			'field_type'     => 'text',
			'list_flag'      => TRUE,
			'edit_flag'      => FALSE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
		'last_login' => array(
			'field_type'     => 'datetime',
			'list_flag'      => TRUE,
			'edit_flag'      => FALSE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
		'failed_login_count' => array(
			'field_type'     => 'text',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
		'last_failed_login' => array(
			'field_type'     => 'datetime',
			'list_flag'      => TRUE,
			'edit_flag'      => FALSE,
			'search_flag'    => TRUE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
		'reset_token' => array(
			'field_type'     => 'text',
			'list_flag'      => FALSE,
			'edit_flag'      => FALSE,
			'search_flag'    => FALSE,
			'view_flag'      => FALSE,
			'is_nullable'    => FALSE,
		),
		'force_update_password_flag' => array(
			'field_type'     => 'checkbox',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => FALSE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
		'force_update_profile_flag' => array(
			'field_type'     => 'checkbox',
			'list_flag'      => TRUE,
			'edit_flag'      => TRUE,
			'search_flag'    => FALSE,
			'view_flag'      => TRUE,
			'is_nullable'    => FALSE,
		),
	);

	// relationships
	protected $_has_many = array(
		'user_token' => array(
			'model'       => 'user_token',
			'foreign_key' => 'user_id',
		),
		'group' => array(
			'model'       => 'group',
			'through'     => 'user_group',
			'far_key'     => 'group_id',
			'foreign_key' => 'user_id',
			'field_label' => 'Permission Groups',
		),
		'auth_log' => array(
			'model'       => 'auth_log',
			'foreign_key' => 'user_id',
			'through'     => 'auth_log',
			'far_key'     => 'id',
		),
	);

	protected $_expires_column = array(
		'column' 	=> 'expiry_date',
		'default'	=> 0,
	);

	/**
	 * @var array $_display_order The order to display columns in, if different from as listed in $_table_columns.
	 * Columns not listed here will be added beneath these columns, in the order they are listed in $_table_columns.
	 */
	protected $_display_order = array(
		10 => 'id',
		20 => 'expiry_date',
		30 => 'username',
		40 => 'password',
		50 => 'first_name',
		60 => 'last_name',
		70 => 'active_flag',
		80 => 'login_count',
		90 => 'last_login',
		100 => 'failed_login_count',
		110 => 'last_failed_login',
		120 => 'reset_token',
		130 => 'force_update_password_flag',
		140 => 'force_update_profile_flag',
		150 => 'group',
	);

	/**
	* @var  int  Stores the failed login count before a login attempt. Set in login()
	*/
	public $_failed_login_count;

	/**
	* @var  array  The user's current settings. These are only set the first time they are requested for that user.
	*/
	protected $_settings;

	/**
	* @var  array  The default settings. When the user has not set the setting, the default will be used
	*/
	protected $_default_settings = array();

	/**
	 * Rule definitions for validation
	 *
	 * @return array
	 */
	public function rules() {
		return array(
			'username' => array(
				array('not_empty'),
				array('min_length', array(':value', 6)),
				array('max_length', array(':value', 200)),
				array('email'),
				array(array($this, 'username_available'), array(':validation', ':field')),
			),
			'first_name' => array(
				array('not_empty'),
				array('max_length', array(':value', 100)),
			),
			'last_name' => array(
				array('not_empty'),
				array('max_length', array(':value', 100)),
			),
			'password' => array(
				// these rules are also in Model_User_Admin
				array('not_empty'),
				// the min length won't have much an affect anywhere because before the rules are run, the filter to hash the password will already have been run
				// therefore, this is only here so it can be used else where, like the profile edit
				array('min_length', array(':value', 6)),
			),
		);
	} // function rules

	/**
	 * Labels for columns
	 *
	 * @return array
	 */
	public function labels() {
		return array(
			'id'                          => 'ID',
			'expiry_date'                 => 'Date Expired',
			'username'                    => 'Email (Username)',
			'password'                    => 'Password',
			'password_confirm'            => 'Confirm Password',
			'first_name'                  => 'First Name',
			'last_name'                   => 'Last Name',
			'active_flag'                 => 'Active',
			'login_count'                 => 'Login Count',
			'last_login'                  => 'Last Login',
			'failed_login_count'          => 'Failed Login Count',
			'last_failed_login'           => 'Last Failed Login',
			'reset_token'                 => 'Reset Password Token',
			'force_update_password_flag'  => 'Force Password Update',
			'force_update_profile_flag'   => 'Force Profile Update',
		);
	} // function labels

	/**
	 * Filter definitions, run everytime a field is set
	 *
	 * @return array
	 */
	public function filters() {
		return array(
			'username' => array(array('trim')),
			'first_name' => array(array('trim')),
			'last_name' => array(array('trim')),
			'password' => array(array(array($this, 'hash_password'))),
			'password_confirm' => array(array(array($this, 'hash_password'))),
		);
	} // function filters

	/**
	* Increments the number of failed login attempts and sets the last failed attempt date/time.
	* After saving, it retrieves the model again so we now have the new failed attempt count.
	*
	* @return  ORM
	*/
	public function increment_failed_login() {
		$this->_log_next_query = FALSE;

		$this->failed_login_count = DB::expr('failed_login_count + 1');
		$this->last_failed_login = DB::expr('NOW()');
		// save and then retrieve the record so the object is updated with the failed count and date
		$this->save()
			->reload();

		return $this;
	} // function increment_failed_login

	/**
	* Add an auth log
	* If the model is loaded, it will use the relationship to the model
	* If the model is not loaded, it will create a new auth_log model
	*
	* @param   int    $auth_type  The auth type id
	* @param   mixed  $username   The username, if loaded, this will be replaced with the current model's username
	* @return  ORM
	*/
	public function add_auth_log($auth_type, $username = NULL) {
		$auth_log_data = array(
			'username' => ($username !== NULL ? $username : ''),
			'access_time' => DB::expr("NOW()"),
			'auth_type_id' => $auth_type,
			'browser' => ( ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
			'ip_address' => ( ! empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
		);

		if ($this->_loaded) {
			$auth_log_data['user_id'] = $this->pk();
			if (empty($auth_log_data['username'])) {
				$auth_log_data['username'] = $this->username;
			}
		}

		$auth_log = ORM::factory('auth_log')
			->values($auth_log_data)
			->save();

		return $this;
	} // function add_auth_log

	/**
	* Adds the where clause to the object for login checking
	*
	* @chainable
	* @param  string  $username  The username to check with
	* @return ORM
	*/
	public function add_login_where($username) {
		$this->where('username', 'LIKE', $username)
			->where('active_flag', '=', 1);

		return $this;
	} // function add_login_where

	/**
	* Complete the login for a user by incrementing the logins and saving login timestamp
	*
	* @return ORM
	*/
	public function complete_login() {
		if ( ! $this->_loaded) {
			// nothing to do
			return;
		}

		// don't log the setting of the login count changes
		$this->_log_next_query = FALSE;

		$this->failed_login_count = 0;

		// Update the number of logins
		$this->login_count = DB::expr('login_count + 1');

		// Set the last login date
		$this->last_login = DB::expr('NOW()');

		// Save the user
		$this->save();

		return $this;
	} // function complete_login

	/**
	* Checks to see if the user has the permission assigned to them through groups
	*
	* @param 	string	$permission		The permission to check for
	* @return 	bool
	*
	* @todo		is there a way to do this with the ORM?
	*/
	public function permission($permission) {
		$rows = DB::select(array(DB::expr('COUNT(*)'), 'total_count'))
			->from(array('user_group', 'ug'))
			->join(array('group_permission', 'gp'), 'INNER')
			->on('ug.group_id', '=', 'gp.group_id')
			->join(array('permission', 'p'), 'INNER')
			->on('gp.permission_id', '=', 'p.id')
			->where('ug.user_id', '=', ':id')
			->and_where('p.permission', 'LIKE', ':perm')
			->param(':id', $this->id)
			->param(':perm', $permission)
			->execute($this->_db)
			->get('total_count');

		return ($rows > 0);
	} // function permission

	/**
	* Sets or retrieves a setting.
	* To use this function, a "settings" field must be in the user table.
	* To get a value, do the following:
	*
	*     $setting = $user->setting('path.to.setting');
	*
	* To set a value, do the following:
	*
	*     $user->setting('path.to.setting', $value);
	*
	* @param   string  $setting  Dot separated path to the setting
	* @param   mixed   $value    The value to set (not required when getting a setting)
	*
	* @chainable
	* @return  ORM    When setting a value, the function returns the object. When getting a setting, it returns the value.
	* @return  mixed  When retrieving a setting, the setting value is returned.
	*/
	public function setting() {
		// settings have not been unserialized yet
		if ($this->_settings === NULL) {
			$this->_settings = unserialize($this->settings);

			if (empty($this->_settings)) {
				$this->_settings = array();
			}
		} // if

		if (func_num_args() == 2) {
			list($setting, $value) = func_get_args();

			$this->_settings = Arr::set_deep($this->_settings, $setting, $value);

			return $this;

		} else {
			list($setting) = func_get_args();

			// @todo figure out how this works with values that are null when found, specifically when it's not an array that's found
			$found_settings = Arr::path($this->_settings, $setting);
			$default_settings = Arr::path($this->_default_settings, $setting);

			if (is_array($default_settings)) {
				if (is_array($found_settings)) {
					return Arr::merge($default_settings, $found_settings);
				} else {
					return $default_settings;
				}
			} else if ($found_settings !== NULL) {
				return $found_settings;
			} else {
				return $default_settings;
			}
		} // if
	} // function setting

	/**
	* Hash the string, but only when the string is not empty
	* Useful when updating the user, but the password is not being updated even though the field is in the post
	*
	* @param   string  $str  The password
	* @return  string  The hashed password or NULL when no hashing was done
	*/
	public function hash_password($str) {
		if ( ! empty($str)) {
			return Auth::instance()->hash($str);
		}

		return NULL;
	} // function hash_password

	/**
	* Sets the password in the model without hashing it
	* This is useful if you are copying an existing user
	*
	* @param   string  $hash_password  The hash password
	* @return  ORM
	*/
	public function set_hashed_password($hash_password) {
		$this->_object['password'] = $hash_password;
		$this->_changed['password'] = 'password';

		return $this;
	} // function set_hashed_password

	/**
	 * Allows serialization of only the object data and state, to prevent
	 * "stale" objects being unserialized, which also requires less memory.
	 * Also serializes and saves the user's settings.
	 *
	 * The same as Kohana::__sleep() but we also include the _options array
	 *
	 * @return  array
	 */
	public function __sleep() {
		// serialize and save settings
		if ( ! empty($this->_settings)) {
			try {
				$this->settings = serialize($this->_settings);
				$this->_log_next_query = FALSE;
				$this->save();
			} catch (Exception $e) {
				cl4::exception_handler($e);
			}
		} // if

		return parent::__sleep();
	} // function __sleep
} // class