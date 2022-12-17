<?php
require_once('Medoo-master/src/Medoo.php');
require 'vendor/autoload.php';
use Medoo\Medoo;

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");


if (!isset($_GET['eco_bags_sold']) || !isset($_GET['eco_bag_token'])){
   exit;
}


$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag_stripe',
	'username' => 'root',
	'password' => '',
]);

$data = $database->select("tokens_db", [
	"payment_token"
], [
	"eco_bag_token" => $_GET['eco_bag_token']
]);

$stripe = new \Stripe\StripeClient('sk_test_51MFcSvBsDABuUoiXtoHX3oYHzXjyj9U6W1lQTUY1q0wMRGQ6EBKvQzHpXh82XAiftyEqV0395SXAeZzY0aUrTqrT00wH85eCL4');

$payment_method = $stripe->customers->allPaymentMethods(
  $data[0]['payment_token'],
  ['type' => 'card', 'limit' => 1]
);

try {
  $stripe->paymentIntents->create([
    'amount' => $_GET['eco_bags_sold'] * 100,
    'currency' => 'cad',
    'customer' => $data[0]['payment_token'],
    'payment_method' => $payment_method->data[0]->id,
    'off_session' => true,
    'confirm' => true,
  ]);

  echo 'true';

} catch (\Stripe\Exception\CardException $e) {
  // Error code will be authentication_required if authentication is needed
  $error = 'Error code is:' . $e->getError()->code;
  $payment_intent_id = $e->getError()->payment_intent->id;
  $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

  $obj = serialize( $payment_intent);
  $content = $error . '\n' . $obj;
  file_put_contents('errors.txt', $content, FILE_APPEND);

  echo 'false';
}


/*$gateway = new Braintree\Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'rvxzx88yvhshnc5s',
    'publicKey' => 'xcx8v3h58kq6d9j9',
    'privateKey' => '0257a62eb5977e098d41bf30300ba112'
]);



$result = $gateway->transaction()->sale([
  'amount' => $_GET['eco_bags_sold'],
  'customerId' => $data[0]['payment_token'],
  'options' => [
    'submitForSettlement' => True
  ]
]);

if ($result->success){
	echo 'true';
}
else{
	echo 'false';
}*/




?>