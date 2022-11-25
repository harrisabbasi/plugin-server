<?php
require_once('Medoo-master/src/Medoo.php');
use Medoo\Medoo;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag',
	'username' => 'root',
	'password' => '',
]);

$database->create("tokens_list", [
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
	"url" => [
		"VARCHAR(32)",
		"NOT NULL"
	],
	"company" => [
		"VARCHAR(32)",
		"NOT NULL"
	],
	"price" => [
		"INT",
		"NOT NULL"
	]
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	session_start();

	$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
	$url = trim(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_STRING));
	$company = trim(filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING));

	$company_exists = false;

	if(substr($url, -1) == '/') {
    	$url = substr($url, 0, -1);
	}


	$data_2 = $database->select("tokens_list", [
		"url", "id"
	],[
		"url[~]" => $url
	]);

	if ($database->error || !empty($data_2)){
	  $company_exists = true;
	}


	if (!$token || $token != $_SESSION['token']) {
	    // return 405 http status code
	    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
	    exit;
	} else {

		if ($company_exists){
			$company_exists_error = 'The company already exists';
		}

		else{
			$eco_bag_token = bin2hex(random_bytes(16));

			$database->insert("tokens_list", [
				"eco_bag_token" => $eco_bag_token,
				"url" => $url,
				"company" => $company,
				"price"   => 3
			]);

		}
		

	}
    // …
}
else{
	session_start();
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
}

?>

<head>
	<meta charset="utf-8">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital@0;1&display=swap" rel="stylesheet">
	<style>
	  body{
	    padding:20px;
	    font-family: 'Open Sans', sans-serif;
	  }

	  .input {
	    font-size: 16px;
	    font-size: max(16px, 1em);
	    font-family: inherit;
	    padding: 0.25em 0.5em;
	    background-color: #fff;
	    border: 2px solid var(--input-border);
	    border-radius: 4px;
	    margin-bottom: 10px;
	  }

	  input[type=submit] {
	      padding:5px 15px; 
	      background:#ccc; 
	      border:0 none;
	      cursor:pointer;
	      -webkit-border-radius: 5px;
	      border-radius: 5px; 
	  }
	</style>
	
</head>

<body>

	<h3 style="text-align: center;">Pick Pack App</h3>
	
	<?php if (isset($company_exists_error)){ ?>
		<p><?php echo $company_exists_error ?></p>
	<?php }?>

	<?php if (isset($eco_bag_token)){ ?>
		<h1>Here is your pick pack token. Add it in the pick pack dashboard and add or update your payement method</h1>
		<h2><?php echo $eco_bag_token ?></h2>
	<?php } else { ?>

	<form method="POST">
		<input class="input" type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
		<p>Enter the URL of your root wordpress installation:</p>
		<input type="url" class="input" type="" name="url" placeholder="Domain" required><br>
		<p>Company Name:</p>
		<input class="input" type="" name="company" placeholder="Company" required><br>
		<input type="submit" value="Generate Token">
	</form>

</body>

<?php } ?>