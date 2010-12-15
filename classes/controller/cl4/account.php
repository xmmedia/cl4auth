<?php defined('SYSPATH') or die('No direct script access.');

class Controller_cl4_Account extends Controller_Base {
	public $page = 'account';

	/**
	* @see Controller_Base
	*/
	public $secure_actions = array(
		'profile' => TRUE,
		'password' => TRUE,
		'cancel' => TRUE,
	);

	/**
	* By default go the profile
	* If the user is not logged in, this will then redirect to the login page
	*/
	public function action_index() {
		Request::instance()->redirect('account/profile');
	} // function

	/**
	* Redirects to the profile action
	*/
	public function action_cancel() {
		Message::add('Your last action was cancelled.', Message::$notice);

		Request::instance()->redirect('account/profile');
	}

	/**
	* Profile edit and save (name and username)
	*/
	public function action_profile() {
		// set the template title (see Controller_Base for implementation)
		$this->template->page_title = 'Profile Edit';

		// get the current user from auth
		$user = Auth::instance()->get_user();
		// use the user loaded from auth to get the user profile model (extends user)
		$model = ORM::factory('userprofile', $user->id);

		if ( ! empty($_POST) && ! empty($_POST['form']) && $_POST['form'] == 'profile') {
			$validate = $model->save_values()->validate_profile_edit();

			// If the post data validates using the rules setup in the user model
			if ($validate === TRUE) {
				try {
					// the user no longer is forced to update their profile
					$mode->force_update_profile_flag = FALSE;
					// save first, so that the model has an id when the relationships are added
					$model->save();
					// message: profile saved
					Message::add(__(Kohana::message('account', 'profile_saved')), Message::$notice);
					// redirect because they have changed their name, which is displayed on the page
					Request::instance()->redirect('account/profile');

				} catch (Exception $e) {
					cl4::exception_handler($e);
					Message::add(__(Kohana::message('account', 'profile_save_error')), Message::$error);
				}

			} else {
				// Get errors for display in view
				Message::add(__(Kohana::message('account', 'profile_save_validation')) . Message::add_validate_errors($validate, 'user'), Message::$error);
			}
		} // if

		// use the user loaded from auth to get the user profile model (extends user)
		$model = ORM::factory('userprofile', $user->id, array(
			'display_reset' => FALSE,
			'hidden_fields' => array(
				Form::hidden('form', 'profile'),
			),
		));

		// prepare the view & form
		$this->template->body_html = View::factory('cl4/cl4account/profile')
			->set('edit_fields', $model->get_form(array(
				'form_action' => '/account/profile',
				'form_id' => 'editprofile',
			)));
	} // function action_profile

	/**
	* Saves the updated password and then calls action_profile() to generate form
	*/
	public function action_password() {
		$this->template->page_title = 'Change Password';

		// get the current user from auth
		$user = Auth::instance()->get_user();

		if ( ! empty($_POST) && ! empty($_POST['form']) && $_POST['form'] == 'password') {
			$validation = $user->validate_change_password($_POST);

			// check if there are any errors
			if (count($validation->errors()) == 0) {
				try {
					ORM::factory('userpassword', $user->id)
						->values(array('password' => $_POST['new_password']))
						// user no longer needs to update their password
						->values('force_update_password_flag', FALSE)
						->save();

					Message::add(__(Kohana::message('account', 'password_changed')), Message::$notice);

					// redirect and exit
					Request::instance()->redirect('account/profile');

				} catch (Exeception $e) {
					cl4::exception_handler($e);
					Message::add(__(Kohana::message('account', 'password_change_error')), Message::$error);
				}

			} else {
				Message::add(__(Kohana::message('account', 'password_change_validation')) . Message::add_validate_errors($validation, 'account'), Message::$error);
			}
		} // if

		// call action profile to generate the profile page with both username and email plus password fields
		$this->action_profile();
	} // function action_password

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
	/**
	* A basic implementation of the "Forgot password" functionality
	*/
	public function action_forgot() {
		require_once(Kohana::find_file('vendor/recaptcha', 'recaptchalib'));

		$default_options = Kohana::config('cl4account');

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
			if ($captcha_received && $resp->is_valid && $user->_loaded && ! in_array($user->username, $default_options['admin_accounts'])) {
				// send an email with the account reset token
				$user->reset_token = cl4_Auth::generate_password(32);
				$user->save();

				try {
					$mail = new Mail();
					$mail->IsHTML();
					$mail->add_user($user->id);
					$mail->Subject = LONG_NAME . ' Password Reset';

					$url = 'account/reset?' . http_build_query(array(
						'username' => $user->username,
						'reset_token' => $user->reset_token,
					), '', '&');
					$link = HTML::anchor($url, 'click here', array('target' => '_blank'));

					$mail->Body = View::factory('cl4/cl4account/forgot_link')
						->set('app_name', LONG_NAME)
						->set('url', $url)
						->set('link', $link)
						->set('admin_email', ADMIN_EMAIL);

					$mail->Send();

					Message::add(__(Kohana::message('account', 'reset_link_sent')), Message::$notice);
				} catch (Exception $e) {
					Message::add(__(Kohana::message('account', 'forgot_send_error')), Message::$error);
					throw $e;
				}
			} else if (in_array($user->username, $default_options['admin_accounts'])) {
				Message::add(__(Kohana::message('account', 'reset_admin_account')), Message::$warning);

			} else {
				Message::add(__(Kohana::message('account', 'reset_not_found')), Message::$warning);
				if ( ! $captcha_received || ! $resp->is_valid) {
					Message::add(__(Kohana::message('user', 'recaptcha_not_valid')), Message::$warning);
				}
			}
		} // if post

		$this->template->body_html = View::factory('cl4/cl4account/forgot');
	} // function

	/**
	* A basic version of "reset password" functionality.
	*
	* @todo consider changing this to not send the password, but instead allow them enter a new password right there; this might be more secure, but since we've sent them a link anyway, it's probably too late for security; the only thing is email is insecure (not HTTPS)
	*/
	function action_reset() {
		$default_options = Kohana::config('cl4account');

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
					Message::add(__(Kohana::message('account', 'password_email_error')), Message::$error);
					throw $e;
				}

				try {
					$mail = new Mail();
					$mail->IsHTML();
					$mail->add_user($user->id);
					$mail->Subject = LONG_NAME . ' New Password';

					$link = URL_ROOT . '/login';

					$mail->Body = View::factory('cl4/cl4account/forgot_link')
						->set('app_name', LONG_NAME)
						->set('username', $user->username)
						->set('password', $password)
						->set('admin_email', ADMIN_EMAIL);

					$mail->Send();

					Message::add(__(Kohana::message('account', 'password_emailed')), Message::$notice);

				} catch (Exception $e) {
					Message::add(__(Kohana::message('account', 'password_email_error')), Message::$error);
					throw $e;
				}

				Request::instance()->redirect('login');

			} else {
				Message::add(__(Kohana::message('account', 'password_email_username_not_found')), Message::$error);
				Request::instance()->redirect('account/forgot');
			}

		} else {
			Message::add(__(Kohana::message('account', 'password_email_partial')), Message::$error);
			Request::instance()->redirect('account/forgot');
		}
	} // function
} // class