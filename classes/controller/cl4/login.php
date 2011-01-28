<?php defined('SYSPATH') or die('No direct script access.');

class Controller_cl4_Login extends Controller_Base {
	public $page = 'login';

	public function before() {
		parent::before();

		$this->add_admin_css();
	} // function before

	/**
	* View: Login form.
	*/
	public function action_index() {
		// set the template title (see Controller_App for implementation)
		$this->template->page_title = 'Login';

		// If user already signed-in
		if (Auth::instance()->logged_in() === TRUE){
			// redirect to the user account
			$this->login_success_redirect();
		}

		// get some variables from the request
		$timed_out = cl4::get_param('timed_out');
		$redirect = cl4::get_param('redirect', '');

		// repare the view
		$login_view = View::factory('cl4/cl4login/login')
			->set('redirect', $redirect);

		$login_config = Kohana::config('cl4login');

		// Get number of login attempts this session
		$attempts = Arr::path($this->session, $login_config['session_key'] . '.attempts', 0);
		$force_captcha = Arr::path($this->session, $login_config['session_key'] . '.force_captcha', FALSE);

		// If more than three login attempts, add a captcha to form
		$captcha_required = ($force_captcha || $attempts > $login_config['failed_login_captcha_display']);
		// Update number of login attempts
		++$attempts;
		$this->session[$login_config['session_key']]['attempts'] = $attempts;

		// load recaptcha
		// do this here because there are likely to be a lot of accesses to this action that will never make it to here
		// loading it here will save server time finding (searching) and loading recaptcha
		require_once(Kohana::find_file('vendor/recaptcha', 'recaptchalib'));

		// put the post in another var so we don't change it to a validate object in login()
		$validate = $_POST;
		// $_POST/$validate is not empty
		if ( ! empty($validate)) {
			// If recaptcha was set and is required
			$captcha_valid = FALSE;
			$captcha_received = FALSE;
			if ($captcha_required && isset($validate['recaptcha_challenge_field']) && isset($validate['recaptcha_response_field'])) {
				$captcha_received = TRUE;
				// Test if recaptcha is valid
				$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $validate['recaptcha_challenge_field'], $validate['recaptcha_response_field']);
				$captcha_valid = $resp->is_valid;
			} // if

			// Instantiate a new user
			$user = ORM::factory('user');

			// Check Auth
			// more specifically, username and password fields need to be set.
			// If the post data validates using the rules setup in the user model
			// $validate is passed by reference and becomes a Validate object inside login()
			// if the captcha is required, then also make sure it's valid
			if (( ! $captcha_required || ($captcha_required && $captcha_valid)) && $user->login($validate, FALSE, $captcha_valid)) {
				// if the account has more than 5 login attempts and the captcha in invalid (or not received) then go back to the login page and force them to enter a captcha
				if ($user->too_many_login_attempts() && ! $captcha_valid) {
					// log out the user because they need to verified as human first
					$this->session[$login_config['session_key']]['force_captcha'] = TRUE;
					$captcha_required = TRUE;

				// login is all good, check for redirect
				} else {
					// user has to update their profile or password
					if ($user->force_update_profile_flag || $user->force_update_password_flag) {
						// add a message for the user regarding updating their profile or password
						$message_path = $user->force_update_profile_flag ? 'update_profile' : 'update_password';
						Message::add(Kohana::message('user', $message_path), Message::$notice);

						// instead of redirecting them to the location they requested, redirect them to the profile page
						$redirect = Route::get('account')->uri(array('action' => 'profile'));
					} // if

					if ( ! empty($redirect) && is_string($redirect)) {
						// Redirect after a successful login, but check permissions first
						$redirect_request = Request::factory($redirect);
						$next_controller = 'Controller_' . $redirect_request->controller;
						$next_controller = new $next_controller($redirect_request);
						if (Auth::instance()->allowed($next_controller, $redirect_request->action)) {
							// they have permission to access the page, so redirect them there
							$this->login_success_redirect($redirect);
						} else {
							// they don't have permission to access the page, so just go to the default page
							$this->login_success_redirect();
						}
					} else {
						// redirect to the user account
						$this->login_success_redirect();
					}
				} // if

			// If login failed (captcha and/or wrong credentials)
			} else {
				// determine if we should be displaying a recaptcha message
				if ( ! $captcha_valid && $captcha_received) {
					$additional_messages = array(__(Kohana::message('user', 'recaptcha_not_valid')));
				} else if ($captcha_required && ! $captcha_received) {
					$additional_messages = array(__(Kohana::message('user', 'enter_recaptcha')));
				} else {
					$additional_messages = array();
				}

				// if $validate is not an object, then get the Validate object from user and then do the validation so we can retrieve the errors (other than just missing the captcha)
				if ( ! is_object($validate)) {
					$user->get_login_validate($validate);
					$validate->check();
				}
				// Get errors for display in view and set the username and password to populate the fields (makes it easier for the user)
				Message::add(Message::add_validate_errors($validate, 'user', $additional_messages), Message::$error);

				// determine if we have to display the captcha because the account they attempted to access has too many attempts
				if ($user->loaded() && $user->too_many_login_attempts()) {
					$this->session[$login_config['session_key']]['force_captcha'] = TRUE;
					$captcha_required = TRUE;
				}
			} // if
		} // if $validate

		if ( ! empty($timed_out)) {
			// they have come from the timeout page, so send them back there
			Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'timedout')) . $this->get_redirect_query());
		}

		// set the user name and password in the view so the fields can be populated (makes logging in easier)
		$login_view->set('username', ( ! empty($validate['username']) ? $validate['username'] : cl4::get_param('username')));
		$login_view->set('password', ( ! empty($validate['password']) ? $validate['password'] : ''));
		$login_view->set('add_captcha', $captcha_required);

		$this->template->body_html = $login_view;
	} // function

	/**
	* Redirects the user the first page they should see after login
	* $redirect contains the page they may have requested before logging in and they should be redirected there
	* If $redirect is is NULL then the default redirect from the config will be used
	*
	* @param  string  $redirect  The path to redirect to
	* @return  void  never returns
	*/
	protected function login_success_redirect($redirect = NULL) {
		if ($redirect !== NULL) {
			Request::instance()->redirect($redirect);
		} else {
			$auth_config = Kohana::config('auth');
			Request::instance()->redirect(Route::get($auth_config['default_login_redirect'])->uri($auth_config['default_login_redirect_params']));
		}
	} // function login_success_redirect

	/**
	* Log the user out and redirects to the login page.
	*/
	public function action_logout() {
		try {
			if (Auth::instance()->get_user()) {
				if ( ! Auth::instance()->get_user()->logout()) {
					throw new Kohana_Exception('There was a problem logging out the user');
				}

				Message::add(__(Kohana::message('user', 'username.logged_out')), Message::$notice);
			} // if

			// redirect to the user account and then the signin page if logout worked as expected
			Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri() . $this->get_redirect_query());
		} catch (Exception $e) {
			cl4::exception_handler($e);
			Message::add(__(Kohana::message('user', 'username.not_logged_out')), Message::$error);

			if ( ! cl4::is_dev()) {
				// redirect them to the default page
				$auth_config = Kohana::config('auth');
				Request::instance()->redirect(Route::get($auth_config['default_login_redirect'])->uri($auth_config['default_login_redirect_params']));
			}
		} // try
	} // function action_logout

	/**
	* Display a page that displays the username and asks the user to enter the password
	* This is for when their session has timed out, but we don't want to make the login fully again
	* If the user has fully timed out, they will be logged out and returned to the login page
	*/
	public function action_timedout() {
		$redirect = cl4::get_param('redirect', '');

		$user = Auth::instance()->get_user();

		$max_lifetime = Kohana::config('auth.timed_out_max_lifetime');

		if ( ! $user || ($max_lifetime > 0 && Auth::instance()->timed_out($max_lifetime))) {
			// user is not logged in at all or they have reached the maximum amount of time we allow sometime to stay logged in, so redirect them to the login page
			Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'logout')) . $this->get_redirect_query());
		}

		$this->template->page_title = 'Timed Out';

		$timedout_view = View::factory('cl4/cl4login/timed_out')
			->set('redirect', $redirect)
			->set('username', $user->username);

		$this->template->body_html = $timedout_view;

		$this->add_on_load_js('$(\'#password\').focus();');
	} // function action_timedout

	/**
	* View: Access not allowed.
	*/
	public function action_noaccess() {
		// set the template title (see Controller_App for implementation)
		$this->template->title = 'Access not allowed';
		$view = $this->template->body_html = View::factory('cl4/cl4login/no_access')
			->set('referrer', cl4::get_param('referrer'));
	} // function action_noaccess

	/**
	* Returns the redirect value as a query string ready to use in a direct
	* The ? is added at the beginning of the string
	* An empty string is returned if there is no redirect parameter
	*
	* @return	string
	*/
	private function get_redirect_query() {
		$redirect = cl4::get_param('redirect');

		if ( ! empty($redirect)) {
			return URL::array_to_query(array('redirect' => $redirect), '&');
		} else {
			return '';
		}
	} // function get_redirect_query

	/**
	* A basic implementation of the "Forgot password" functionality
	*/
	public function action_forgot() {
		require_once(Kohana::find_file('vendor/recaptcha', 'recaptchalib'));

		$default_options = Kohana::config('cl4login');

		// set the template page_title (see Controller_Base for implementation)
		$this->template->page_title = 'Forgot Password';

		if (isset($_POST['reset_username'])) {
			// If recaptcha is valid and is received
			$captcha_received = FALSE;
			if (isset($_POST['recaptcha_challenge_field']) && isset($_POST['recaptcha_response_field'])) {
				$captcha_received = TRUE;
				$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			}

			$user = ORM::factory('user')->where('username', '=', $_POST['reset_username'])
				->find();

			// Admin passwords cannot be reset by email
			if ($captcha_received && $resp->is_valid && $user->loaded() && ! in_array($user->username, $default_options['admin_accounts'])) {
				// send an email with the account reset token
				$user->reset_token = cl4_Auth::generate_password(32);
				$user->save();

				try {
					$mail = new Mail();
					$mail->IsHTML();
					$mail->add_user($user->id);
					$mail->Subject = LONG_NAME . ' Password Reset';

					// build a link with action reset including their username and the reset token
					$url = URL::site(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'reset')) . '?' . http_build_query(array(
						'username' => $user->username,
						'reset_token' => $user->reset_token,
					)), TRUE);

					$mail->Body = View::factory('cl4/cl4login/forgot_link')
						->set('app_name', LONG_NAME)
						->set('url', $url)
						->set('admin_email', ADMIN_EMAIL);

					$mail->Send();

					Message::add(__(Kohana::message('login', 'reset_link_sent')), Message::$notice);
				} catch (Exception $e) {
					Message::add(__(Kohana::message('login', 'forgot_send_error')), Message::$error);
					throw $e;
				}
			} else if (in_array($user->username, $default_options['admin_accounts'])) {
				Message::add(__(Kohana::message('login', 'reset_admin_account')), Message::$warning);

			} else {
				Message::add(__(Kohana::message('login', 'reset_not_found')), Message::$warning);
				if ( ! $captcha_received || ! $resp->is_valid) {
					Message::add(__(Kohana::message('user', 'recaptcha_not_valid')), Message::$warning);
				}
			}
		} // if post

		$this->template->body_html = View::factory('cl4/cl4login/forgot');
	} // function

	/**
	* A basic version of "reset password" functionality.
	*
	* @todo consider changing this to not send the password, but instead allow them enter a new password right there; this might be more secure, but since we've sent them a link anyway, it's probably too late for security; the only thing is email is insecure (not HTTPS)
	*/
	function action_reset() {
		$default_options = Kohana::config('cl4login');

		// set the template title (see Controller_Base for implementation)
		$this->template->page_title = 'Password Reset';

		$username = cl4::get_param('username');
		if ($username !== null) $username = trim($username);
		$reset_token = cl4::get_param('reset_token');

		// make sure that the reset_token has exactly 32 characters (not doing that would allow resets with token length 0)
		// also make sure we aren't trying to reset the password for an admin
		if ( ! empty($username) && ! empty($reset_token) && strlen($reset_token) == 32) {
			$user = ORM::factory('user')->where('username', '=', $_REQUEST['username'])->and_where('reset_token', '=', $_REQUEST['reset_token'])->find();

			// admin passwords cannot be reset by email
			if (is_numeric($user->id) && ! in_array($user->username, $default_options['admin_accounts'])) {
				try {
					$password = cl4_Auth::generate_password();
					$user->password = $password;
					$user->failed_login_count = 0; // reset the login count
					$user->save();
				} catch (Exception $e) {
					Message::add(__(Kohana::message('login', 'password_email_error')), Message::$error);
					throw $e;
				}

				try {
					$mail = new Mail();
					$mail->IsHTML();
					$mail->add_user($user->id);
					$mail->Subject = LONG_NAME . ' New Password';

					// provide a link to the user including their username
					$url = URL::site(Route::get(Route::name(Request::instance()->route))->uri(), TRUE) . '?' . http_build_query(array('username' => $user->username));

					$mail->Body = View::factory('cl4/cl4login/forgot_reset')
						->set('app_name', LONG_NAME)
						->set('username', $user->username)
						->set('password', $password)
						->set('url', $url)
						->set('admin_email', ADMIN_EMAIL);

					$mail->Send();

					Message::add(__(Kohana::message('login', 'password_emailed')), Message::$notice);

				} catch (Exception $e) {
					Message::add(__(Kohana::message('login', 'password_email_error')), Message::$error);
					throw $e;
				}

				Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri());

			} else {
				Message::add(__(Kohana::message('login', 'password_email_username_not_found')), Message::$error);
				Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'forgot')));
			}

		} else {
			Message::add(__(Kohana::message('login', 'password_email_partial')), Message::$error);
			Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'forgot')));
		}
	} // function

		/**
	 * Registers a new user.
	 *//*
	public function action_register() {

		// see if the user is already logged in
		// todo: do something smarter here, like ask if they want to register a new user?
		if ($this->auth->logged_in()) {
			claero::flash_set('message', 'You already have an account.');
			$this->request->redirect($this->redirectUrl);
		}

		$this->redirectPage = 'register';

		if (Request::$method == 'POST') {
            // try to create a new user with the supplied credentials
            try {

                // check the recaptcha string to make sure it was entered properly
                require_once(ABS_ROOT . '/lib/recaptcha/recaptchalib.php');
                $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
                if (!$resp->is_valid) {
                    claero::flash_set('message', __('The reCAPTCHA text did not match up, please try again.'));
                    Fire::log('The reCAPTCHA text did not match up, please try again.');
                } else {

                    // try to create the new user
                    $newUser = Jelly::factory('user')
                         ->set(array(
                            'active_flag' => 0, // already defaulted in database
                            'date_created' => date('Y-m-d H:i:s'),
                            'email' => Security::xss_clean(Arr::get($_POST, 'email', '')),
                            'password' => Security::xss_clean(Arr::get($_POST, 'password', '')),
                            'password_confirm' => Security::xss_clean(Arr::get($_POST, 'password_confirm', '')),
                            'first_name' => Security::xss_clean(Arr::get($_POST, 'first_name', '')),
                            'middle_name' => Security::xss_clean(Arr::get($_POST, 'middle_name', '')),
                            'last_name' => Security::xss_clean(Arr::get($_POST, 'last_name', '')),
                            'company' => Security::xss_clean(Arr::get($_POST, 'company', '')),
                            'province_id' => Security::xss_clean(Arr::get($_POST, 'province_id', '')),
                            'work_phone' => Security::xss_clean(Arr::get($_POST, 'work_phone', '')),
                            'mobile_phone' => Security::xss_clean(Arr::get($_POST, 'mobile_phone', '')),
                         ));
                    if ($newUser->save()) {
                        claero::flash_set('message', __("Your account was created successfully."));
                        $this->redirectPage = 'index';
                    } // if
                    //Fire::log('looks like it worked?');
                } // if

            } catch (Validate_Exception $e) {
                claero::flash_set('message', __("A validation error occurred, please correct your information and try again."));
                Fire::log('A validation exception occurred: ');
                Fire::log($e->array);

            } catch (Exception $e) {
                Fire::log('Some other exception occured');
                Fire::log($e);
                $this->template->body_html .= 'Could not create user. Error: "' . Kohana::exception_text($e) . '"';
                claero::flash_set('message', 'An error occurred during registration, please try again later.');

            } // try
        } else {
            // invalid request type for registration
            Fire::log('invalid request type for registration');
		} // if

        // Redirect to login
        //$this->request->redirect($this->redirectUrl);
        fire::log('here we are');


        $this->provinceId = Security::xss_clean(Arr::get($_POST, 'province_id', ''));

	} // function action_register
*/
} // class Controller_cl4_Login