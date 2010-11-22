<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
	'remember_lifetime' => 1209600, // 14 days
	'auth_lifetime'  => 10800, // 3 hours: the amount of time till the user will have to enter their password to continue using the site; set to 0 for unlimited
	'timed_out_max_lifetime' => 172800, // 2 days: the amount of time till the user will have to fully login again and will not be able to login through the timed out password only page; set to 0 for unlimited
	'timestamp_key'  => 'auth_timestamp',
	'default_login_redirect' => 'dbadmin', // the location to redirect the user after they login; used within Controller_cl4_login::login_success_redirect()
);