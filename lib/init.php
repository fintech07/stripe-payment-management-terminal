<?php

/* start our session */
session_start();

/* require config, functions and db files */
require 'lib/config.php';
require 'lib/functions.php';
require 'vendor/idiorm.php';
require 'vendor/paris.php';
require 'vendor/stripe/stripe-php/init.php';

/* require our model files */
require 'models/Config.php';
require 'models/Item.php';
require 'models/Invoice.php';
require 'models/Payment.php';
require 'models/Subscription.php';
require 'models/Event.php';

/* set db credentials for our ORM */
ORM::configure('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name']);
ORM::configure('username', $config['db_username']);
ORM::configure('password', $config['db_password']);

if ( preg_match('/install/', $_SERVER['REQUEST_URI']) ) {

	/* prevent install action if we already have installed  */
	try {
		$config_collection = Model::factory('Config')->findMany();
		go('index.php');
	} catch (Exception $e) {
		$install_error = $e->getMessage();
		// allow install
	}

} else {

	/* check to see if we need to install  */
	try {
		$config_collection = Model::factory('Config')->findMany();
	} catch (Exception $e) {
		go('install.php');
	}

	/* extend config variable with db config values */
	$config_arr = array();
	foreach ( $config_collection as $config_obj ) {
		$config_arr[$config_obj->key] = $config_obj->value;
	}
	$config = array_merge($config, $config_arr);

	/* set stripe credentials */
	if ( !empty($config['stripe_secret_key']) ) {
		\Stripe\Stripe::setApiKey(trim($config['stripe_secret_key']));
	}

	/* redirect to https now if we need to */
	if ( $config['https_redirect'] && !is_ssl() && get('action') != 'paypal_ipn' ) {
		$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	    header('Location: ' . $redirect);
	    die();
	}

}

// setup our CSRF token
$csrf = '';
if ( session_id() ) {
	$csrf = md5(session_id() . 't3rm1nal');
}