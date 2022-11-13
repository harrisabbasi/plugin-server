<?php 

ini_set('max_execution_time', 0);

require_once('../../Medoo-master/src/Medoo.php');

require_once('includes/functions.php');

use Medoo\Medoo;

$script_not_running = check_if_script_running("myscript.lock");

if ($script_not_running){
	

	$database = new Medoo([
		// [required]
		'type' => 'mysql',
		'host' => 'localhost',
		'database' => 'eco_bag',
		'username' => 'root',
		'password' => '',
	]);

	$database->create("wordpress_orders", [
		"id" => [
			"INT",
			"NOT NULL",
			"AUTO_INCREMENT",
			"PRIMARY KEY"
		],
		"price" => [
			"INT",
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
		]
	]);

	$data = $database->select("tokens_list", [
		"url"
	]);

	$ch = set_up_curl();

	foreach ($data as $company) {

		var_dump($company['url']);

		curl_setopt($ch, CURLOPT_URL, $company['url'] . '/?request=curl&type=orders');

		$response = curl_exec($ch);
		$order_array = array();

		if ($response !== 'No orders'){
			$orders = explode('!', $response);

			foreach ($orders as $order) {
				$order_array[] = explode(' ', $order);
			}
		}

		var_dump($order_array);

		if (curl_errno($ch)) {
		    var_dump(curl_error($ch));
		}
		
	}

	curl_close($ch);

	var_dump($response);

	


	unlink('myscript.lock');
}
?>