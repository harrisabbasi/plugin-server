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
		'database' => 'eco_bag_stripe',
		'username' => 'root',
		'password' => '',
	]);

	$database->create("website_status", [
		"id" => [
			"INT",
			"NOT NULL",
			"AUTO_INCREMENT",
			"PRIMARY KEY"
		],
		"status" => [
			"INT",
			"NOT NULL"
		],
		"company_id" => [
			"INT",
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

		if ($response == "OK"){

			$status = 1;

		}
		else{
			$status = 0;
		}

		$row = $database->select("website_status", 
			"*",
			['company_id' => $company['id']]
		);

		if (empty($row)){
			$database->insert("website_status", [
				'company_id' => $company['id'],
				'status' => $status,
			]);
		}
		else{
			$database->update("website_status", [
				'status' => $status
			],[
				'company_id' => $company['id']
			]);
		}


		
	}

	curl_close($ch);

	var_dump($response);

	unlink('myscript.lock');
}
?>