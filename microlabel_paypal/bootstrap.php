<?php

/*
 * microlabel bootstrap file.
 */

define('PP_CONFIG_PATH', __DIR__);

$microlabel_paypal_redirect_uri="http://opensimo.org/play/auth_ok.php";
$microlabel_paypal_client_id='ATH7axAW1bxQT_D7qIxSEDxPZhbnNV5XDfGyTV30y6nNT7EgEKB7-o2zEN4e';
$microlabel_paypal_client_secret='EEu3yBADgZHyfzlLCQKDDeFO4VuqpnwRLlDdPPj1PoPRag42OeV_VMyMnkzM';

// Include the composer autoloader
if(!file_exists(__DIR__ .'/vendor/autoload.php')) {
	echo "The 'vendor' folder is missing. You must run 'composer update --no-dev' to resolve application dependencies.\nPlease see the README for more information.\n";
	exit(1);
}


require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/common.php';

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

$apiContext = getApiContext();

/**
 * Helper method for getting an APIContext for all calls
 *
 * @return PayPal\Rest\ApiContext
 */
function getApiContext() {
global $microlabel_paypal_client_id, $microlabel_paypal_client_secret;
	
	// ### Api context
	// Use an ApiContext object to authenticate 
	// API calls. The clientId and clientSecret for the 
	// OAuthTokenCredential class can be retrieved from 
	// developer.paypal.com

	$apiContext = new ApiContext(
		new OAuthTokenCredential(
			$microlabel_paypal_client_id
			,$microlabel_paypal_client_secret
		)
	);



	// #### SDK configuration
	
	// Comment this line out and uncomment the PP_CONFIG_PATH
	// 'define' block if you want to use static file 
	// based configuration

	$apiContext->setConfig(
		array(
			//'mode' => 'live',
			'mode' => 'sandbox',
			'http.ConnectionTimeOut' => 30,
			'log.LogEnabled' => false,
			'log.FileName' => '../PayPal.log',
			'log.LogLevel' => 'FINE'
		)
	);
	
	/*
	// Register the sdk_config.ini file in current directory
	// as the configuration source.
	if(!defined("PP_CONFIG_PATH")) {
		define("PP_CONFIG_PATH", __DIR__);
	}
	*/

	return $apiContext;
}
