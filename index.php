<?php 
require_once('Medoo-master/src/Medoo.php');
require 'vendor/autoload.php';
use Medoo\Medoo;

if (isset($_GET['eco_bag_token']) & isset($_GET['return_url'])){

  $database = new Medoo([
    // [required]
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'eco_bag_stripe',
    'username' => 'root',
    'password' => '',
  ]);

  $data = $database->select("tokens_list", [
      "eco_bag_token",
      "company"
    ], [
      "eco_bag_token" => $_GET['eco_bag_token']
    ]);

  if ($database->error || count($data) != 1){
    echo 'There was some error retrieving the token';
    exit;
  }


} else {
 echo 'Something went wrong. Please try again';
 exit; 
}

$stripe = new \Stripe\StripeClient(
  'sk_test_51MFcSvBsDABuUoiXtoHX3oYHzXjyj9U6W1lQTUY1q0wMRGQ6EBKvQzHpXh82XAiftyEqV0395SXAeZzY0aUrTqrT00wH85eCL4'
);

if (isset($_GET['action']) && $_GET['action'] == 'update'){
  $row = $database->select("tokens_db", 
    "*",
    ["eco_bag_token" => $_GET['eco_bag_token']]
  );

  $customer = $stripe->customers->retrieve(
    $row[0]['payment_token'],
    []
  );

}
else{
  $customer = $stripe->customers->create([
  'name' => $data[0]['company'],
]);


}

$intent = $stripe->setupIntents->create(
  [
    'customer' => $customer->id,
    'payment_method_types' => ['bancontact', 'card', 'ideal'],
  ]
);

$client_secret = $intent->client_secret;
?>
<head>
  <meta charset="utf-8">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital@0;1&display=swap" rel="stylesheet">
  <script src="https://js.stripe.com/v3/"></script>
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
  <form id="payment-form">
    <div id="payment-element">
      <!-- Elements will create form elements here -->
    </div>
    <input type="submit" id="submit"></button>
    <div id="error-message">
      <!-- Display error message to your customers here -->
    </div>
  </form>

  <script
  src="https://code.jquery.com/jquery-3.6.1.min.js"
  integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ="
  crossorigin="anonymous"></script>
  

  <script type="text/javascript">
  

  const stripe = Stripe('pk_test_51MFcSvBsDABuUoiXnPU6GqCf3AaoQqyQ1xH1Xk4MG3Z8XDQNlsiYlbBHzwPHdrs3y9kBeOnJEhcQyK0CDDLagS3X00MY2Wbjuu');
  
  const options = {
    clientSecret: '<?php echo $client_secret ?>',
    // Fully customizable with appearance API.
    appearance: {/*...*/},
  };

  // Set up Stripe.js and Elements to use in checkout form, passing the client secret obtained in step 3
  const elements = stripe.elements(options);

  // Create and mount the Payment Element
  const paymentElement = elements.create('payment');
  paymentElement.mount('#payment-element');

  const form = document.getElementById('payment-form');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const {error} = await stripe.confirmSetup({
      //`Elements` instance that was used to create the Payment Element
      elements,
      confirmParams: {
        return_url: 'http://localhost/plugin_server/return_url.php?eco_bag_token=' + '<?php echo $_GET['eco_bag_token'] ?>' + '&customer=' + '<?php echo $customer->id ?>' + '&intent=' + '<?php echo $intent->id?>',
      }
    });

    if (error) {
      // This point will only be reached if there is an immediate error when
      // confirming the payment. Show error to your customer (for example, payment
      // details incomplete)
      const messageContainer = document.querySelector('#error-message');
      messageContainer.textContent = error.message;
    } else {
      // Your customer will be redirected to your `return_url`. For some payment
      // methods like iDEAL, your customer will be redirected to an intermediate
      // site first to authorize the payment, then redirected to the `return_url`.
    }
  });
  
  
  
  </script>
</body>