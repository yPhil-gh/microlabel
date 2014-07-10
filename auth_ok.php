<?php
require __DIR__ . '/microlabel_paypal/bootstrap.php';
use PayPal\Auth\Openid\PPOpenIdTokeninfo;
$code=$_GET["code"];
try {
	$token=PPOpenIdTokeninfo::createFromAuthorizationCode(array(
		'client_id' => $microlabel_paypal_client_id
                ,'client_secret' => $microlabel_paypal_client_secret
		,'grant_type' => 'authorization_code'
		,'code' => $code
		,'redirect_uri'=>$microlabel_paypal_redirect_uri
	),$apiContext);
} catch (PayPal\Exception\PPConnectionException $ex) {
	echo "Exception:" . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}


//Next
use PayPal\Auth\Openid\PPOpenIdUserinfo;
try {
	$user = PPOpenIdUserinfo::getUserinfo(
	    array(
		'access_token' => $token["access_token"];
	    )
	);
} catch (PayPal\Exception\PPConnectionException $ex) {
	echo "Exception:" . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<head>
	<title>Welcome</title>
</head>
<body>
	<pre><?php  var_dump($user->toArray());?></pre>
</body>
</html>
