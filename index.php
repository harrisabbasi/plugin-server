<?php 
require_once('Medoo-master/src/Medoo.php');
use Medoo\Medoo;

if isset($_GET['eco_bag_token']) & isset($_GET['return_url'])){

  $database = new Medoo([
      // [required]
      'type' => 'mysql',
      'host' => 'localhost',
      'database' => 'eco_bag',
      'username' => 'root',
      'password' => '',
    ]);

  $data = $database->select("tokens_list", [
      "eco_bag_token"
    ], [
      "eco_bag_token" => $_GET['eco_bag_token']
    ]);

  if ($database->error || count($data) != 1){
    echo 'There was some error retrieving the token';
    exit;
  }


}?>
<head>
  <meta charset="utf-8">
  
</head>
<body>
  <!-- Step one: add an empty container to your page -->
  <form id="payment-form" action="server.php" method="post">
      <!-- Putting the empty container you plan to pass to
        `braintree.dropin.create` inside a form will make layout and flow
        easier to manage -->
    <div id="dropin-container"></div>
    <input type="" name="first_name" placeholder="First Name" required>
    <input type="" name="last_name" placeholder="Last Name" required>
    <input type="" name="company" placeholder="Company" required>
    <input type="submit" />

    <input type="hidden" id="nonce" name="payment_method_nonce"/>
    <input type="hidden"  name="eco_bag_token" value="<?php echo $_GET['eco_bag_token'] ?>"/>
    <input type="hidden"  name="return_url" value="<?php echo $_GET['return_url'] ?>"/>
  </form>

  <script
  src="https://code.jquery.com/jquery-3.6.1.min.js"
  integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ="
  crossorigin="anonymous"></script>
  <script src="https://js.braintreegateway.com/web/dropin/1.33.4/js/dropin.min.js"></script>

  <script type="text/javascript">
  // call `braintree.dropin.create` code here
  var token;
  const form = document.getElementById('payment-form');

  $(document).ready(function() {
   // Step One ajax request to our server that returns client token
   $.post("server.php",
   {
     process: "token"
   },
   function(data, status){
     console.log("Data: " + data + "\nStatus: " + status);
     token = data;
     // Request to braintree server with client token (and the dropin container is created)
     braintree.dropin.create({
       container: document.getElementById('dropin-container'),
       authorization: token,
       // ...plus remaining configuration
     }, (error, dropinInstance) => {
       // Use `dropinInstance` here
       // Methods documented at https://braintree.github.io/braintree-web-drop-in/docs/current/Dropin.html
       form.addEventListener('submit', event => {
          event.preventDefault();

          dropinInstance.requestPaymentMethod((error, payload) => {
            if (error) console.error(error);

            
            document.getElementById('nonce').value = payload.nonce;
            form.submit();
          });
        });
     });
     
   });
 });
  

  
  
  </script>
</body>