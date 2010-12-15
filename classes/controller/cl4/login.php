<?php defined('SYSPATH') or die('No direct script access.');

class Controller_cl4_Login extends Controller_Base {
	public $page = 'login';

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
		$force_captcha = Arr::get($this->session, $login_config['session_key'] . '.force_captcha', FALSE);

		// Update number of login attempts
		++$attempts;
		// If more than three login attempts, add a captcha to form
		$captcha_required = ($force_captcha || $attempts > $login_config['failed_login_captcha_display']);
		$this->session[$login_config['session_key']]['attempts'] = $attempts;
		$login_view->set('add_captcha', $captcha_required);

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
			if ($captcha_required && isset($validate['recaptcha_challenge_field']) && isset($validate['recaptcha_response_field'])) {
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
			if ($user->login($validate, FALSE, $captcha_valid) && ( ! $captcha_required || ($captcha_required && $captcha_valid))) {
				// if the account has more than 5 login attempts and the captcha in invalid (or not received) then go back to the login page and force them to enter a captcha
				if ($user->_failed_login_count > $login_config['max_failed_login_count'] && ! $captcha_valid) {
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
						$redirect = '/account/profile';
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
				if ( ! $captcha_valid) {
					$additional_messages = array(__(Kohana::message('user', 'recaptcha_not_valid')));
				} else {
					$additional_messages = array();
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
			Request::instance()->redirect('login/timedout' . $this->get_redirect_query());
		}

		// set the user name and password in the view so the fields can be populated (makes logging in easier)
		$login_view->set('username', ( ! empty($validate['username']) ? $validate['username'] : ''));
		$login_view->set('password', ( ! empty($validate['password']) ? $validate['password'] : ''));
		$login_view->set('add_captcha', $captcha_required);

		$this->template->body_html = $login_view;

		$this->add_on_load_js("\$('#username').focus();");
	} // function

	/**
	* Redirects the user the first page they should see after login
	* $redirect contains the page they may have requested before logging in and they should be redirected there
	*
	* @param  string  $redirect
	* @return  void  never returns
	*/
	protected function login_success_redirect($redirect = NULL) {
		if ( ! empty($redirect)) {
			Request::instance()->redirect($redirect);
		} else {
			$default_redirect = Kohana::config('auth.default_login_redirect');
			Request::instance()->redirect($default_redirect);
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
			}

			// redirect to the user account and then the signin page if logout worked as expected
			Request::instance()->redirect('login' . $this->get_redirect_query());
		} catch (Exception $e) {
			cl4::exception_handler($e);
			Message::add(__(Kohana::message('user', 'username.not_logged_out')), Message::$error);

			if ( ! cl4::is_dev()) {
				// redirect them to the default page
				$default_redirect = Kohana::config('auth.default_login_redirect');
				Request::instance()->redirect($default_redirect);
			}
		}
	} // function

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
			Request::instance()->redirect('login/logout' . $this->get_redirect_query());
		}

		$this->template->page_title = 'Timed Out';

		$timedout_view = View::factory('cl4/cl4login/timed_out')
			->set('redirect', $redirect)
			->set('username', $user->username);

		$this->template->body_html = $timedout_view;

		$this->template->on_load_js .= <<<EOA
$('#password').focus();
EOA;
	}

	/**
	* View: Access not allowed.
	*/
	public function action_noaccess() {
		// set the template title (see Controller_App for implementation)
		$this->template->title = 'Access not allowed';
		$view = $this->template->body_html = View::factory('cl4/cl4login/no_access')
			->set('referrer', cl4::get_param('referrer'));
	} // function

	/**
	* Returns the redirect value as a query string ready to use in a direct
	* The ? is added at the beginning of the string
	* An empty string is returned if there is no redirect parameter
	*
	* @return	string
	*/
	private function get_redirect_query() {
		$redirect = cl4::get_param('redirect');

		if ( ! empty($redirect)) return URL::array_to_query(array('redirect' => $redirect), '&');
		else return '';
	} // function
} // class
