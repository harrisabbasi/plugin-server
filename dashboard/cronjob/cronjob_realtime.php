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

	$data = $database->select("tokens_list", [
		"url", "id"
	]);

	$ch = set_up_curl();

	foreach ($data as $company) {

		var_dump($company['url']);
		
		if(substr($company['url'], -1) != '/') {
		    $company['url'] = $company['url'] . '/';
		}
		

		$token = (string) rand();

		curl_setopt($ch, CURLOPT_URL, $company['url'] . '?request=curl&type=orders' . '&token=' . $token);

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
		    var_dump(curl_error($ch));
		}

		$order_array = array();

		if ($response !== 'No orders' && strlen($response) < 500){
			$orders = explode('!', $response);

			foreach ($orders as $order) {
				$order_array[] = explode(' ', $order);
			}

			$previous_orders = $database->select("wordpress_orders", [
			    "order_id"
			  ], [
			    "company_id" => $company['id']
			  ]);
			/*$previous_orders = [['order_id' => 266]];*/

			if (count($previous_orders) !== 0){

				$array_order_ids = array();
				foreach ($previous_orders as $order) {
					$array_order_ids[] = $order['order_id'];
					
				}


				$order_array = array_filter($order_array, function ($order) use ($array_order_ids) {
					return !in_array((int)$order[0], $array_order_ids);
				});
			}

			foreach ($order_array as $order) {

				$database->insert("wordpress_orders", [
					"order_id" => $order[0],
					"timestamp" => $order[1] . ' ' . $order[2],
					"price"	=> $order[3],
					"bags_sold" => $order[4],
					"company_id" => $company['id']

				]);
				# code...
			}
			
		}

		var_dump($order_array);

		
		
	}

	curl_close($ch);

	var_dump($response);

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


	

	unlink('myscript.lock');
}
?>