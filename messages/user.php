<?php defined('SYSPATH') or die('No direct script access.');

/**
* These messages are for the user model (including userprofile)
*
* see /system/messages/validate.php for the defaults for each rule. These can be overridden on a per-field/message basis.
*/
return array(
	'username' => array(
		'not_empty' => ':field must not be empty.',
		'invalid' => ':field or password is incorrect.',
		'email' => ':field mst be an email address.',
		'min_length' => ':field must be an email address.',
		'max_length' => ':field must be an email address.',
		'username_available' => 'The username/email address entered is already used. Please use a different email address.',
		'too_many_attempts' => 'There have been too many attempts on this account. The account will be locked for 5 minutes.',
		'logged_out' => 'You have been logged out successfully.',
	),
	'password' => array(
		'not_empty' => ':field must not be empty.',
		'min_length' => ':field must be at least :param1 characters long.',
		'max_length' => ':field must be less than :param1 characters long.',
		'password_matches' => 'Your passwords need to both be the same.',
	),
);