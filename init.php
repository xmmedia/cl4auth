<?php
$routes = Kohana::config('claero.routes');

if ($routes['login']) {
	// login page
	Route::set('login', '(<lang>/)login(/<action>)', array('lang' => $lang_options))
	    ->defaults(array(
	        'lang' => DEFAULT_LANG,
	        'controller' => 'login',
	        'action' => 'index',
	));
}

if ($routes['account']) {
	// account: profile, change password, forgot, register
	Route::set('account', '(<lang>/)account(/<action>)', array('lang' => $lang_options))
	    ->defaults(array(
	        'controller' => 'account',
	        'lang' => DEFAULT_LANG,
	        'action' => 'index',
	));
}