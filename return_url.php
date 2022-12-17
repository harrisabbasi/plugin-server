<?php
require_once('Medoo-master/src/Medoo.php');
require 'vendor/autoload.php';
use Medoo\Medoo;

if (isset($_GET['eco_bag_token']) & isset($_GET['customer'])){

  $database = new Medoo([
    // [required]
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'eco_bag_stripe',
    'username' => 'root',
    'password' => '',
  ]);

  $data = $database->select("tokens_list", [
      "eco_bag_token",
      "company"
    ], [
      "eco_bag_token" => $_GET['eco_bag_token']
    ]);

  if ($database->error || count($data) != 1){
    echo 'There was some error retrieving the token';
    exit;
  }


} else {
 echo 'Something went wrong. Please try again';
 exit; 
}

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

$stripe = new \Stripe\StripeClient(
  'sk_test_51MFcSvBsDABuUoiXtoHX3oYHzXjyj9U6W1lQTUY1q0wMRGQ6EBKvQzHpXh82XAiftyEqV0395SXAeZzY0aUrTqrT00wH85eCL4'
);
$intent = $stripe->setupIntents->retrieve(
  $_GET['intent'],
  []
);

if ($intent->status == 'succeeded'){

	$row = $database->select("tokens_db", 
		"*",
		["eco_bag_token" => $_GET['eco_bag_token']]
	);

	if (empty($row)){
		$database->insert("tokens_db", [
			"eco_bag_token" => $_GET['eco_bag_token'],
			"payment_token" => $_GET['customer'],
		]);
	}

	header("Location: " . $_GET['return_url'] . '?token=' . $_GET['eco_bag_token'] . '&status=success');
	die();
	//make it default source using the customer object, you can retreive the default source (payment method id) using the intent object
}
else{
	header("Location: " . $_GET['return_url'] . '?token=' . $_GET['eco_bag_token'] . '&status=failure');
	die();
}




?>

