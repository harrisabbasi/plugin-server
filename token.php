<?php
require_once('Medoo-master/src/Medoo.php');

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
	]
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

	if (!$token || $token !== $_SESSION['token']) {
	    // return 405 http status code
	    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
	    exit;
	} else {
		$eco_bag_token = bin2hex(random_bytes(16));

		$database->insert("tokens_list", [
			"eco_bag_token" => $eco_bag_token,
		]);

	}
    // …
}
else{
	session_start();
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
}

?>
<?php if (isset($eco_bag_token)){ ?>
	<h1>Here is your pick pack token. Add it in the pick pack dashboard and add or update your payement method</h1>
	<h2><?php echo $eco_bag_token ?></h2>
<?php } else { ?>

<form method="POST">
	<input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
	<input type="submit" value="Generate Token">
</form>

<?php } ?>



?>