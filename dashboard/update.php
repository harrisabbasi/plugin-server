<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	session_start();
	$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
	$id = trim(filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING));
	$price = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING));


	if (!$token || $token != $_SESSION['token']) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
	    exit;
	}
	else{

		$data = $database->select("tokens_list", 
		    array('id', 'url','price', 'company')
		  ,
		    array('id' => $id)
		  );
		

		if (count($data) == 1){
			if ($data[0]['price'] === $price){
				exit;
			}
			// add curl request

			if(substr($data[0]['url'], -1) == '/') {
			    $data[0]['url'] = substr($data[0]['url'], 0, -1);
			}

			//$referer = $_SERVER['HTTP_HOST'];
			$referer = 'http://localhost/plugin_server/dashboard/update.php';
			$token = (string) rand();
						/*$response = file_get_contents($data[0]['url'] . '/?request=curl');*/
			/*var_dump($data[0]['url']);*/
			//Use curl on production server
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $data[0]['url'] . '/?request=curl&price=' . $price . '&token=' . $token);

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,2);

			curl_setopt($ch, CURLOPT_REFERER, $referer);

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($ch, CURLOPT_FAILONERROR, true);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
			    var_dump(curl_error($ch));
			}

			curl_close($ch);

			var_dump($response);

			if ($response == 'success'){
				$database->update("tokens_list",
					array("price" => $price),
					array('id' => $id)
				);

				if ($database->error){
					exit;
				}

				if (!isset($_SESSION['message'])){
					$_SESSION['message'] = 'The price for company ' . $data[0]['company'] . ' has been updated';
				}

				header('location:index.php');
			}
			
		}








	}


}



?>