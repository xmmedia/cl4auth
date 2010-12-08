<?php defined('SYSPATH') or die('No direct script access.');

// see /system/messages/validate.php for the defaults for each rule. These can be overridden on a per-field basis.
return array(
	// profile edit
	'profile_saved' => 'Your profile has been saved.',
	'profile_save_error' => 'There was a problem saving your profile. Please try again.',
	'profile_save_validation' => 'Your profile could not be saved because of the following: ',
	// password update
	'new_password' => array(
		'not_empty' => 'Your new password cannot be empty.',
		'min_length' => 'Your new password must be at least :param1 characters long.',
		'max_length' => 'Your new password must be less than :param1 characters long.',
	),
	'new_password_confirm' => array(
		'matches' => 'Both the new passwords must be the same.',
	),
	'current_password' => array(
		'not_the_same' => 'Your current password is incorrect.',
	),
	'password_changed' => 'Your password has been changed.',
	'password_change_error' => 'There was a problem updating your password. Please try again.',
	'password_change_validation' => 'Your password could not be changed because of the following: ',
	// forgot password
	'reset_link_sent' => 'A link to reset your password has been emailed to you.',
	'reset_send_error' => 'There was a problem sending your password reset link. The administrators have been notified.',
	'reset_admin_account' => 'This password for this account cannot be reset using this method.',
	'reset_not_found' => 'The username cannot be found.',
	'password_emailed' => 'Your new password has been emailed to you.',
	'password_email_error' => 'There was a problem resetting your password. The administrators have been notified.',
	'password_email_username_not_found' => 'The username could not be found. Please try copying and pasting the link from the email or contacting the administrator.',
	'password_email_partial' => 'Only partial information was received to reset your password. Please try copying and pasting the link from the email or contacting the administrator.',
    
    // reCAPTCHA isn't valid'
    'recaptcha_not_valid' => 'reCAPTCHA answer was not correct.  Please try again.'
);