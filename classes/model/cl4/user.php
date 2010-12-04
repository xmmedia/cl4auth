<?php defined('SYSPATH') or die ('No direct script access.');

class Model_cl4_User extends Model_Auth_User {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'user';
	public $_table_name_display = 'User';
	protected $_primary_val = 'username'; // default: name (column used as primary value)
	// see http://v3.kohanaphp.com/guide/api/Database_MySQL#list_columns for all possible column attributes

	// Validation rules
	protected $_rules = array(
		'username' => array(
			'not_empty'  => NULL,
			'min_length' => array(6),
			'max_length' => array(250),
			'email'      => NULL,
		),
		'first_name' => array(
			'not_empty'  => NULL,
			'max_length' => array(100),
		),
		'last_name' => array(
			'not_empty'  => NULL,
			'max_length' => array(100),
		),
		'password' => array(
			// these rules are also in check_password()
			'not_empty'  => NULL,
			'min_length' => array(5),
			'max_length' => array(42),
		),
		'password_confirm' => array(
			// these rules are also in check_password()
			'matches'    => array('password'),
		),
	);

	// as our username is our email address, this removes the callback to check if the email is unique
	protected $_callbacks = array(
		'username' => array('username_available'),
	);

	// column labels
	protected $_labels = array(
		'id' => 'ID',
		'date_expired' => 'Date Expired',
		'username' => 'Email (Username)',
		'password' => 'Password',
		'password_confirm' => 'Password Confirm',
		'first_name' => 'First Name',
		'last_name' => 'Last Name',
		'active_flag' => 'Active',
		'login_count' => 'Login Count',
		'last_login' => 'Last Login',
		'failed_login_count' => 'Failed Login Count',
		'last_failed_login' => 'Last Failed Login',
		'reset_token' => 'Reset Password Token',
	);

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
		'expiry_date' => array(
			'field_type' => 'datetime',
			'list_flag' => FALSE,
			'edit_flag' => TRUE,
			'search_flag' => FALSE,
			'view_flag' => FALSE,
			'is_nullable' => FALSE,
			'display_order' => 20,
		),
		'username' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 30,
			'is_nullable' => FALSE,
		),
		'password' => array(
			'field_type' => 'password_confirm',
			'list_flag' => FALSE,
			'edit_flag' => TRUE,
			'search_flag' => FALSE,
			'view_flag' => FALSE,
			'display_order' => 40,
			'is_nullable' => FALSE,
		),
		'first_name' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 50,
			'is_nullable' => FALSE,
		),
		'last_name' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 60,
			'is_nullable' => FALSE,
		),
		'active_flag' => array(
			'field_type' => 'checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 70,
			'is_nullable' => FALSE,
			'field_options' => array(
				'default_value' => 1,
			),
		),
		'login_count' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => FALSE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 80,
			'is_nullable' => FALSE,
		),
		'last_login' => array(
			'field_type' => 'datetime',
			'list_flag' => TRUE,
			'edit_flag' => FALSE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 90,
			'is_nullable' => FALSE,
		),
		'failed_login_count' => array(
			'field_type' => 'text',
			'list_flag' => TRUE,
			'edit_flag' => FALSE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 100,
			'is_nullable' => FALSE,
		),
		'last_failed_login' => array(
			'field_type' => 'datetime',
			'list_flag' => TRUE,
			'edit_flag' => FALSE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'display_order' => 110,
			'is_nullable' => FALSE,
		),
		'reset_token' => array(
			'field_type' => 'text',
			'list_flag' => FALSE,
			'edit_flag' => FALSE,
			'search_flag' => FALSE,
			'view_flag' => FALSE,
			'display_order' => 120,
			'is_nullable' => FALSE,
		),
	);

	// relationships
	protected $_has_many = array(
		'user_token' => array('model' => 'user_token'), // todo: source model shouldn't be needed
		'group'      => array('model' => 'group', 'through' => 'user_group', 'foreign_key' => 'user_id'),
		'auth_log'   => array('model' => 'authlog', 'through' => 'auth_log', 'foreign_key' => 'user_id', 'far_key' => 'id'),
	);
	protected $_has_one = array();

	// Columns to ignore
	protected $_ignored_columns = array('password_confirm');

	protected $_expires_column = array(
		'column' 	=> 'expiry_date',
		'default'	=> 0,
	);

	/**
	* Validates login information from an array, and optionally redirects
	* after a successful login.
	*
	* @param   array    values to check (passed by reference)
	* @return  boolean
	*/
	public function login(array & $login_details, $redirect = FALSE) {
		$login_details = Validate::factory($login_details)
			->label('username', $this->_labels['username'])
			->label('password', $this->_labels['password'])
			->filter('username', 'trim')
			->rules('username', $this->_rules['username'])
			->rules('password', $this->_rules['password']);

		// Get the remember login option
		$remember = ! empty($login_details['remember']);

		$auth_types = Kohana::config('cl4login.auth_type');

		// Login starts out invalid
		$status = FALSE;

		if ($login_details->check()) {
			// Attempt to load the user, adding the where clause
			$this->add_login_where($login_details['username'])
				->find();

			// if there are too many recent failed logins, fail now
			if ($this->_loaded && $this->failed_login_count > 5 && strtotime($this->last_failed_login) > strtotime('-5 minutes') ) {
				// fail (too many failed logins within 5 minutes).
				$this->failed_login_count = DB::expr('failed_login_count + 1');
				$this->last_failed_login = DB::expr('NOW()');
				$this->save();

				$login_details->error('username', 'too_many_attempts');
				$auth_type = $auth_types['too_many_attempts'];

			} else {
				if ($this->_loaded && Auth::instance()->login($this, $login_details['password'], $remember)) {
					// Login is successful
					$status = TRUE;
					$auth_type = $auth_types['logged_in'];
				} else {
					// there was a problem logging them in, but set failed counts and date/time or set type to unknown username/password if user doesn't exist
					if ($this->_loaded && is_numeric($this->id) && $this->id != 0) {
						// only save if the user exists
						$this->failed_login_count = DB::expr('failed_login_count + 1');
						$this->last_failed_login = DB::expr('NOW()');
						$this->save();

						$auth_type = $auth_types['invalid_password'];
					} else {
						$auth_type = $auth_types['invalid_username_password'];
					}

					// add a custom message found in message/login.php
					$login_details->error('username', 'invalid');
				} // if
			} // if
		} else {
			$auth_type = $auth_types['invalid_username_password'];
		} // if

		if ($this->_loaded) {
			$this->add('auth_log', ORM::factory('authlog'), array(
				'username' => $this->username,
				'access_time' => DB::expr('NOW()'),
				'auth_type_id' => $auth_type,
				'browser' => ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
				'ip_address' => ! empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
			));
		} else {
			// the user is not valid, so the object doesn't contain an information and screws up because it can't set the user_id
			DB::insert('auth_log')
				->columns(array('username', 'access_time', 'auth_type_id', 'browser', 'ip_address'))
				->values(array(
					$login_details['username'],
					DB::expr('NOW()'),
					'auth_type_id' => $auth_type,
					'browser' => ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
					'ip_address' => ! empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
				))
				->execute($this->_db);
		}

		return $status;
	} // function login

	/**
	* Adds the where clause to the object for login checking
	*
	* @chainable
	* @param  string  $username  The username to check with
	* @return ORM
	*/
	public function add_login_where($username) {
		$this->where('username', '=', $username)
			->where('active_flag', '=', 1);

		return $this;
	} // function add_login_where

	/**
	* Logout the user
	* Records an auth_log record with type logged_out
	* Performs Auth::logout()
	*
	*/
	public function logout() {
		$auth_types = Kohana::config('cl4login.auth_type');

		$this->add('auth_log', ORM::factory('authlog'), array(
			'username' => $this->username,
			'access_time' => DB::expr('NOW()'),
			'auth_type_id' => $auth_types['logged_out'],
			'browser' => ! empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'ip_address' => ! empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
		));

		// Sign out the user
		// The TRUE parameter triggers the logout to delete everything in the session
		return Auth::instance()->logout();
	} // function

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

		$this->failed_login_count = 0;

		// Update the number of logins
		$this->login_count = DB::expr('login_count + 1');

		// Set the last login date
		$this->last_login = DB::expr('NOW()');

		// Save the user
		$this->save();

		return $this;
	} // function

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
	} // function

	/**
	* Add the rules to validate the profile page
	*
	* @param 	array	$array	The POST
	* @return 	object	The validation object
	*/
	public function validate_profile_edit() {
		unset($this->_rules['password'], $this->_rules['password_confirm']);

		return $this->check();
	} // function

	/**
	* Add the rules to validate the password change page
	*
	* @param 	array	$array	The POST
	* @return 	object	The validation object
	*/
	public function validate_change_password($post) {
		$validation = Validate::factory($post)
			->label('current_password', 'Current Password')
			->label('new_password', 'New Password')
			->label('new_password_confirm', 'Confirm New Password')
			->rules('current_password', $this->_rules['password'])
			->rules('new_password', $this->_rules['password'])
			->rules('new_password_confirm', array(
				'matches' => array('new_password')
			));

		$validation->check();

		if ( ! Auth::instance()->check_password($post['current_password'])) {
			$validation->error('current_password', 'not_the_same');
		}

		return $validation;
	} // function

	/**
	* Checks the password for an admin page
	* On admin, there will be 2 fields (likely password and password_confirm) although they do not need to be entered
	* This will check to see if either of the fields are not empty
	* If both the fields are empty or not set, then the field will be removed from the _changed array if it's set (ORM_Password sets the field even if it's empty)
	* If either of the fields have values, then it will create a validation object for these 2 fields, add rules and validate
	* If there are errors, then it will add the errors to the passed validation object
	* This function has customized rules that are also in this object
	*
	* @param Validate $array
	* @param string $field
	*/
	public function check_password(Validate $array, $field) {
		if ( ! empty($array[$field]) || ! empty($array[$field . '_confirm'])) {
			$validation = Validate::factory(array(
					$field => isset($array[$field]) ? $array[$field] : NULL,
					$field . '_confirm' => isset($array[$field . '_confirm']) ? $array[$field . '_confirm'] : NULL,
				))
				->label('password', $this->_labels['password'])
				->label($field . '_confirm', $this->_labels[$field . '_confirm'])
				->rules('password', array(
					'not_empty'  => NULL,
					'min_length' => array(5),
					'max_length' => array(42),
				))
				->rules($field . '_confirm', array(
					'matches' => array('password')
				));

			if ( ! $validation->check()) {
				foreach ($validation->errors() as $field => $error) {
					$array->error($field, $error[0], $error[1]);
				}
			}

		} else {
			if (isset($this->_changed[$field])) {
				unset($this->_changed[$field]);
			}
		}
	} // function check_password
} // class