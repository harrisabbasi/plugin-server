<?php
require_once('../Medoo-master/src/Medoo.php');

use Medoo\Medoo;

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

$database = new Medoo([
	// [required]
	'type' => 'mysql',
	'host' => 'localhost',
	'database' => 'eco_bag_stripe',
	'username' => 'root',
	'password' => '',
]);

function sortByTimestamp($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
}
session_start();
$_SESSION['token'] = md5(uniqid(mt_rand(), true));

$data = $database->select("tokens_list",
	array('id', 'eco_bag_token', 'url', 'company', 'price'));

if ($database->error || count($data) == 0){
  echo 'No company is registered';
  exit;
}


if (isset($_GET['company_id'])){
	$company_id = $_GET['company_id'];
}

$companies = $database->select("tokens_list", [
	"url", "id", "company", "price"
]);

$update_time = $database->select("time_updated", "*");

if (isset($_GET['company_id'])){
	$orders = $database->select("wordpress_orders", [
		"id",
		"order_id",
		"price",
		"bags_sold",
		"timestamp",
		"company_id"
	], [
		"company_id" => $company_id
	]);

	usort($orders, 'sortByTimestamp');

	// var_dump($orders);

	/*var_dump($orders);*/
}




?>
<!DOCTYPE html>
	<html>
	<head>
		<title>Eco Bag Dashboard</title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

		  .box{
		  	position: relative;
		  	width: 250px;
		  }

		  .box select {
		    background-color: #0563af;
		    color: white;
		    padding: 12px;
		    width: 250px;
		    border: none;
		    font-size: 20px;
		    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
		    -webkit-appearance: none;
		    appearance: none;
		    outline: none;
		  }

		  .box::before {
		    content: "\f13a";
		    font-family: FontAwesome;
		    position: absolute;
		    top: 0;
		    right: 0;
		    width: 20%;
		    height: 100%;
		    text-align: center;
		    font-size: 28px;
		    line-height: 45px;
		    color: rgba(255, 255, 255, 0.5);
		    background-color: rgba(255, 255, 255, 0.1);
		    pointer-events: none;
		  }

		  .box:hover::before {
		    color: rgba(255, 255, 255, 0.6);
		    background-color: rgba(255, 255, 255, 0.2);
		  }

		  .box select option {
		    padding: 30px;
		  }

		  .flex-container{
		  	display: flex;
		  	flex-wrap: wrap;
		  	margin-bottom: 30px;
		  }

		  .container{
		  	padding:0px 30px 30px 30px;
		  }

		  input[type=submit]{
		  	font-size: 20px;
		    line-height: 30px;
		    height: 45px;
		    box-sizing: border-box;
		    background:#ccc; 
		    border:0 none;
		    cursor:pointer;
		    -webkit-border-radius: 5px;
		    border-radius: 5px; 
		    margin-left: 30px;

		     
		  }

		  table, th, td {
		    border: 1px solid black;
		    border-collapse: collapse;
		    text-align: center;
		  }

		  tr:nth-child(even) {
		    background-color: rgba(150, 212, 212, 0.4);
		  }

		  th:nth-child(even),td:nth-child(even) {
		    background-color: rgba(150, 212, 212, 0.4);
		  }
		  
		  


		</style>
	</head>
	<body>
		<div class="flex-container header" >
			<p><a href="index.php">Dashboard</a></p>
			<p><a href="orders.php">Companies Orders</a></p>
		</div>

		<div class="container">
			<form method="GET">
				<div class="flex-container">
					<div class="box">
					  <select name="company_id">
					  	<?php foreach ($companies as $company):?>
					    	<option <?php echo (isset($company_id) && $company_id == $company['id']) ? 'selected' : ''; ?> value="<?php echo $company['id'] ?>"><?php echo $company['company'] ?> </option>
					    <?php endforeach; ?>
					  </select>
					</div>

					<input type="submit" value="Select Company">
				</div>
			</form>
			<?php if (isset($_GET['company_id'])): ?>
				<table style="width:100%">
				  <tr>
				    <th>ID</th>
				    <th>ORDER ID</th>
				    <th>PRICE</th>
				    <th>BAGS SOLD</th>
				    <th>TIMESTAMP</th>
				  </tr>
				  <?php foreach ($orders as $order): ?>
					  <tr>
					    <td><?php echo $order['id'] ?></td>
					    <td><?php echo $order['order_id'] ?></td>
					    <td><?php echo $order['price'] ?></td>
					    <td><?php echo $order['bags_sold'] ?></td>
					    <td><?php echo $order['timestamp'] ?></td>
					  </tr>
					 <?php endforeach; ?>
				</table>
			<?php endif; ?>
		</div>


		
		




	</body>
</html>