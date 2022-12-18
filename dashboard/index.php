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

session_start();
$_SESSION['token'] = md5(uniqid(mt_rand(), true));

$data = $database->select("tokens_list",
	array('id', 'eco_bag_token', 'url', 'company', 'price'));

if ($database->error || count($data) == 0){
  echo 'No company is registered';
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
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
		<style>
		  body{
		    padding:20px;
		    font-family: 'Open Sans', sans-serif;
		  }

		  a {
		    background-color: red;
		    color: white;
		    padding: 1em 1.5em;
		    text-decoration: none;
		    text-transform: uppercase;
		    border-radius: 10px;
		  }

		  .header{
		  	margin-bottom: 30px;
		  }

		  .header p{
		  	margin-left:30px;
		  }


		  .flex-container{
		  	display: flex;
		  	flex-wrap: wrap;
		  }

		  .flex-item{
		  	margin-left:30px;
		  	border: 1px solid #ccc;
		  	padding:20px;
		  	box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
		  	border-radius: 10px;

		  }

		  .message{
		  	border: 1px solid #ccc;
		  	box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
		  	border-radius: 10px;
		  	margin-left: 30px;
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
		<div class="flex-container header" >
			<p><a href="index.php">Dashboard</a></p>
			<p><a href="orders.php">Companies Orders</a></p>
		</div>
		
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
						<input step=".01" type="number" class="input"  value="<?php echo (isset($company['price'])) ? $company['price'] : 3 ?>"name="price" placeholder="Price" required><br>
						<input type="submit" value="Update Price">

					</form>
				</div>
				
			<?php endforeach; ?>
		</div>




	</body>
</html>