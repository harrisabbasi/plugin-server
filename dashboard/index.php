<?php
require_once('../Medoo-master/src/Medoo.php');

use Medoo\Medoo;

$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag',
	'username' => 'root',
	'password' => '',
]);

session_start();
$_SESSION['token'] = md5(uniqid(mt_rand(), true));

$data = $database->select("tokens_list",
	array('id', 'eco_bag_token', 'url', 'company', 'price'));

if ($database->error || count($data) == 0){
  echo 'There was some error retrieving the token';
  exit;
}


if (isset($_SESSION['message'])){
	$message = $_SESSION['message'];
	unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
	<html>
	<head>
		<title>Eco Bag Dashboard</title>
		<style>
		  body{
		    padding:20px;
		    font-family: 'Open Sans', sans-serif;
		  }

		  .flex-container{
		  	display: flex;
		  	flex-wrap: wrap;
		  }

		  .flex-item{
		  	margin-left:30px;
		  	border: 1px solid #ccc;
		  	padding:20px;

		  }

		  .message{
		  	border: 1px solid #ccc;
		  	padding: 20px;
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
		<h2>Dashboard</h2>
		<?php if (isset($message)):?>
			<p class="message"><?php echo $message ?></p>
		<?php endif ?>
		<div class="flex-container">
			<?php foreach ($data as $company): ?>
				<div class="flex-item">
					<form action = "update.php" method = "POST">
						<input class="input" type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
						<input class="input" type="hidden" name="id" value="<?php echo $company['id'] ?>">
						<p>Company Name:</p>
						<p><?php echo $company['company'] ?></p>
						<p>Price:</p>
						<input type="number" class="input"  value="<?php echo (isset($company['price'])) ? $company['price'] : 3 ?>"name="price" placeholder="Price" required><br>
						<input type="submit" value="Update Price">

					</form>
				</div>
				
			<?php endforeach; ?>
		</div>




	</body>
</html>