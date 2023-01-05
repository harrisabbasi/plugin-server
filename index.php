<?php 
require_once('Medoo-master/src/Medoo.php');
require 'vendor/autoload.php';
use Medoo\Medoo;

if (isset($_GET['eco_bag_token']) & isset($_GET['return_url'])){

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

$stripe = new \Stripe\StripeClient(
  'sk_test_51MFcSvBsDABuUoiXtoHX3oYHzXjyj9U6W1lQTUY1q0wMRGQ6EBKvQzHpXh82XAiftyEqV0395SXAeZzY0aUrTqrT00wH85eCL4'
);

if (isset($_GET['action']) && $_GET['action'] == 'update'){
  $row = $database->select("tokens_db", 
    "*",
    ["eco_bag_token" => $_GET['eco_bag_token']]
  );

  $customer = $stripe->customers->retrieve(
    $row[0]['payment_token'],
    []
  );

}
else{
  $customer = $stripe->customers->create([
  'name' => $data[0]['company'],
]);


}

$session = $stripe->checkout->sessions->create([
  'payment_method_types' => ['card'],
  'mode' => 'setup',
  'customer' => $customer->id,
  'success_url' => 'http://localhost/plugin_server/return_url.php?session_id={CHECKOUT_SESSION_ID}&return_url=' . $_GET['return_url'] . '&eco_bag_token=' . $_GET['eco_bag_token'] . '&customer=' . $customer->id,
  'cancel_url' => $_GET['return_url'] . '?token=' . $_GET['eco_bag_token'] . '&status=failure',
]);

header("Location: " . $session->url);



/*$intent = $stripe->setupIntents->create(
  [
    'customer' => $customer->id,
    'payment_method_types' => ['bancontact', 'card', 'ideal'],
  ]
);*/

/*$client_secret = $intent->client_secret;*/
?>
