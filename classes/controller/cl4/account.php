<?php defined('SYSPATH') or die('No direct script access.');

class Controller_cl4_Account extends Controller_Base {
	public $page = 'account';

	/**
	* Must be logged in to access any of this controller
	* No specific permissions to any of the actions because anyone logged can access them
	* @see Controller_Base
	*/
	public $auth_required = TRUE;

	/**
	* By default go the profile
	* If the user is not logged in, this will then redirect to the login page
	*/
	public function action_index() {
		Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'profile')));
	} // function

	/**
	* Redirects to the profile action
	*/
	public function action_cancel() {
		Message::add('Your last action was cancelled.', Message::$notice);

		Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'profile')));
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
		$model = ORM::factory('user_profile')->find($user->pk());

		if ( ! empty($_POST) && ! empty($_POST['form']) && $_POST['form'] == 'profile') {
			$validate = $model->save_values()->validate_profile_edit();

			// If the post data validates using the rules setup in the user model
			if ($validate === TRUE) {
				try {
					// the user no longer is forced to update their profile
					$model->force_update_profile_flag = FALSE;
					// save first, so that the model has an id when the relationships are added
					$model->save();
					// message: profile saved
					Message::add(__(Kohana::message('account', 'profile_saved')), Message::$notice);
					// redirect because they have changed their name, which is displayed on the page
					Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'profile')));

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
		$model = ORM::factory('user_profile', $user->pk(), array(
			'display_reset' => FALSE,
			'hidden_fields' => array(
				Form::hidden('form', 'profile'),
			),
		));

		// prepare the view & form
		$this->template->body_html = View::factory('cl4/cl4account/profile')
			->set('edit_fields', $model->get_form(array(
				'form_action' => URL::site(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'profile'))),
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
					$model = ORM::factory('user_password', $user->pk())
						->values(array(
							'password' => $_POST['new_password'],
							// user no longer needs to update their password
							'force_update_password_flag' => FALSE,
						))
						->save();

					Message::add(__(Kohana::message('account', 'password_changed')), Message::$notice);

					// redirect and exit
					Request::instance()->redirect(Route::get(Route::name(Request::instance()->route))->uri(array('action' => 'profile')));

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
} // class