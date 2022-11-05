<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('braintree-php-6.9.0/lib/autoload.php');
require_once('braintree-php-6.9.0/lib/Braintree/Gateway.php');
require_once('Medoo-master/src/Medoo.php');
use Medoo\Medoo;
/*use Braintree;*/


$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag',
	'username' => 'root',
	'password' => '',
]);

$database->create("tokens_db", [
	"id" => [
		"INT",
		"NOT NULL",
		"AUTO_INCREMENT",
		"PRIMARY KEY"
	],
	"eco_bag_token" => [
		"VARCHAR(32)",
		"NOT NULL"
	],
	"payment_token" => [
		"VARCHAR(32)",
		"NOT NULL"
	]
]);

$gateway = new Braintree\Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'rvxzx88yvhshnc5s',
    'publicKey' => 'xcx8v3h58kq6d9j9',
    'privateKey' => '0257a62eb5977e098d41bf30300ba112'
]);

if ($_POST['process'] == "token"){
	echo ($clientToken = $gateway->clientToken()->generate());
}


if (isset($_POST['payment_method_nonce'])){

	$nonceFromTheClient = $_POST["payment_method_nonce"];
	$eco_bag_token = $_POST["eco_bag_token"];
	$return_url = $_POST["return_url"];


	/*$result = $gateway->transaction()->sale([
	  'amount' => '10.00',
	  'paymentMethodNonce' => $nonceFromTheClient,
	  'options' => [
	    'submitForSettlement' => True
	  ]
	]);*/

	if (!isset($_POST['pick_pack_update'])){

		$result = $gateway->customer()->create([
		    'firstName' => $_POST['first_name'],
		    'lastName' => $_POST['last_name'],
		    'company' => $_POST['company'],
		    'paymentMethodNonce' => $nonceFromTheClient
		]);
		if ($result->success) {
		    echo($result->customer->id);
		    echo($result->customer->paymentMethods[0]->token);

		    $database->insert("tokens_db", [
		    	"eco_bag_token" => $eco_bag_token,
		    	"payment_token" => $result->customer->id,
		    ]);

		    header('Location: '. $return_url . '?status=success&token=' . $eco_bag_token);

		} else {
		    foreach($result->errors->deepAll() AS $error) {
		        echo($error->code . ": " . $error->message . "\n");
		    }

		    header('Location: '. $return_url . '?status=failure');
		}
	}
	else{
		$data = $database->select("tokens_db", [
		    "payment_token"
		  ], [
		    "eco_bag_token" => $_POST['eco_bag_token']
		  ]);

		if ($database->error || count($data) !== 1){
		  echo 'There was some error retrieving the token';
		  exit;
		}

		$customer = $gateway->customer()->find($data[0]['payment_token']);
		$token_2 = $customer->creditCards[0]->token;

		$result = $gateway->customer()->update(
		$data[0]['payment_token'],
  		[
  		  'paymentMethodNonce' => $nonceFromTheClient,
	      'firstName' => $_POST['first_name'],
	      'lastName' => $_POST['last_name'],
	      'company' => $_POST['company'],
	      'creditCard' => [
	             'options' => [
	                 'updateExistingToken' => $token_2
	             ]
	          ]
        ]
		);

		if ($result->success) {
		   /* echo($result->customer->id);
		    echo($result->customer->paymentMethods[0]->token);

		    $database->insert("tokens_db", [
		    	"eco_bag_token" => $eco_bag_token,
		    	"payment_token" => $result->customer->paymentMethods[0]->token,
		    ]);*/

		    header('Location: '. $return_url . '?status=success&token=' . $eco_bag_token);

		} else {
		    foreach($result->errors->deepAll() AS $error) {
		        echo($error->code . ": " . $error->message . "\n");
		    }

		    header('Location: '. $return_url . '?status=failure');
		}



	}

	var_dump($result);
}	

?>