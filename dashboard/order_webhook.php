<?php

ini_set("log_errors", 1);
ini_set("error_log", "C:/wamp64/www/plugin_server/dashboard/php-error.log");


if (!isset($_GET['eco_bag_token'])){
   exit;
}

require_once('../Medoo-master/src/Medoo.php');
use Medoo\Medoo;

$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag_stripe',
	'username' => 'root',
	'password' => '',
]);

if (isset($_GET['eco_bag_token'])){
	$data = $database->select("tokens_list", [
	    "eco_bag_token"
	  ], [
	    "eco_bag_token" => $_GET['eco_bag_token']
	  ]);

	if ($database->error || count($data) != 1){
	  echo 'There was some error retrieving the token';
	  exit;
	}
}

$database->create("wordpress_orders", [
	"id" => [
		"INT",
		"NOT NULL",
		"AUTO_INCREMENT",
		"PRIMARY KEY"
	],
	"price" => [
		"VARCHAR(32)",
		"NOT NULL"
	],
	"bags_sold" => [
		"INT",
		"NOT NULL"
	],
	"timestamp" => [
		"VARCHAR(100)",
		"NOT NULL"
	],
	"company_id" => [
		"INT",
		"NOT NULL"
	],
	"order_id" => [
		"INT",
		"NOT NULL"
	]
]);

$database->create("time_updated", [
	"id" => [
		"INT",
		"NOT NULL",
		"AUTO_INCREMENT",
		"PRIMARY KEY"
	],
	"time" => [
		"VARCHAR(100)",
		"NOT NULL"
	],
]);


if(substr($_GET['url'], -1) == '/') {
    $_GET['url'] = substr($_GET['url'], 0, -1);
}

$data_2 = $database->select("tokens_list", [
	"url", "id"
],[
	"url[~]" => $_GET['url']
]);

if ($database->error || count($data_2) != 1){
  echo 'There was some error retrieving the company';
  exit;
}

$database->insert("wordpress_orders", [
	"order_id" => $_GET['order_id'],
	"timestamp" => $_GET['timestamp'],
	"price"	=> $_GET['eco_bag_price'],
	"bags_sold" => $_GET['eco_bags_sold'],
	"company_id" => $data_2[0]['id']

]);

$row = $database->select("time_updated", 
	"*"
);

if (empty($row)){
	$database->insert("time_updated", [
		'time' => date('d-m-y h:i:s')
	]);
}
else{
	$database->update("time_updated", [
		'time' => date('d-m-y h:i:s')
	]);
}




?>