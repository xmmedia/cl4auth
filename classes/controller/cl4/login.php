<?php defined('SYSPATH') or die('No direct script access.');

class Controller_cl4_Login extends Controller_Base {
	public $page = 'login';

	/**
	* View: Login form.
	*/
	public function action_index() {
		require_once(Kohana::find_file('vendor/recaptcha', 'recaptchalib'));
		$session = Session::instance();

		// set the template title (see Controller_App for implementation)
		$this->template->page_title = 'Login';

		// If user already signed-in
		if (Auth::instance()->logged_in() === TRUE){
			// redirect to the user account
			$this->login_success_redirect();
		}

		$timed_out = cl4::get_param('timed_out');
		$redirect = cl4::get_param('redirect', '');

		$login_view = View::factory('cl4/cl4login/login')
			->set('redirect', $redirect);

		// Get number of login attempts this session
		$attempts = $session->get('login_attempts', 0);

		// If more than three login attempts, add Captcha to form
		$login_view->set('add_captcha', $attempts > 3);

		// put the post in another var so we don't change it to a validate object in login()
		$validate = $_POST;

		// $_POST is not empty
		if ( ! empty($validate)) {
			// If recaptcha was set
			if (isset($validate['recaptcha_challenge_field'])) {
				// Test if recaptcha is valid
				$resp = recaptcha_check_answer(
					RECAPTCHA_PRIVATE_KEY,
					$_SERVER['REMOTE_ADDR'],
					$validate['recaptcha_challenge_field'],
					$validate['recaptcha_response_field']
				);
			}

			// If recaptcha was not set or recaptcha is valid
			if (( ! isset($_POST['recaptcha_challenge_field'])) || ($resp->is_valid)) {
				// Instantiate a new user
				$user = ORM::factory('user');

				// Check Auth
				// more specifically, username and password fields need to be set.
				// If the post data validates using the rules setup in the user model
				// $validate is passed by reference and becomes a validate object inside login()
				if ($user->login($validate)) {
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
				// If login failed
				} else {
					// Get errors for display in view and set the username and password to populate the fields (makes it easier for the user)
					Message::add(Message::add_validate_errors($validate, 'user'), Message::$error);
					$login_view->set('username', $validate['username']);
					$login_view->set('password', $validate['password']);

					// Update number of failed login attempts
					$attempts++;
					$session->set('login_attempts', $attempts);
					$login_view->set('add_captcha', $attempts > 3);
				}
			// If recaptcha was not valid
			} else {
				Message::add(__(Kohana::message('account', 'recaptcha_not_valid')), Message::$warning);
				$login_view->set('username', $validate['username']);
				$login_view->set('password', $validate['password']);
			}
		// If $_POST is empty
		} else {
			$login_view->set('username', '');
			$login_view->set('password', '');
		}

		if ( ! empty($timed_out)) {
			// they have come from the timeout page, so send them back there
			Request::instance()->redirect('login/timedout' . $this->get_redirect_query());
		}

		$this->template->body_html = $login_view;

		$this->template->on_load_js .= "\n$('#username').focus();\n";
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
		} catch (Exception $e) {
			cl4::exception_handler($e);
		}

		// redirect to the user account and then the signin page if logout worked as expected
		Request::instance()->redirect('login' . $this->get_redirect_query());
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
