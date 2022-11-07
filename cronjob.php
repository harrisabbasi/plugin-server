<?php

if (!isset($_GET['eco_bags_sold']) || !isset($_GET['eco_bag_token'])){
   exit;
}

require_once('braintree-php-6.9.0/lib/autoload.php');
require_once('Medoo-master/src/Medoo.php');
use Medoo\Medoo;

$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag',
	'username' => 'root',
	'password' => '',
]);

$data = $database->select("tokens_db", [
	"payment_token"
], [
	"eco_bag_token" => $_GET['eco_bag_token']
]);



$gateway = new Braintree\Gateway([
    'environment' => 'sandbox',
    'merchantId' => 'rvxzx88yvhshnc5s',
    'publicKey' => 'xcx8v3h58kq6d9j9',
    'privateKey' => '0257a62eb5977e098d41bf30300ba112'
]);

/*$paymentMethod = $gateway->paymentMethod()->find('k8yf1zqf');*/

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
}




?>