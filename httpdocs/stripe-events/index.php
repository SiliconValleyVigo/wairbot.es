<?php
// Muestra todos los errores como parte de la salida del script
ini_set('display_errors', 1);
// webhook.php
//
// Use this sample code to handle webhook events in your integration.
//
// 1) Paste this code into a new file (webhook.php)
//
// 2) Install dependencies
//   composer require stripe/stripe-php
//
// 3) Run the server on http://localhost:4242
//   php -S localhost:4242

require 'stripe-php-master/init.php';

// The library needs to be configured with your account's secret key.
// Ensure the key is kept out of any version control system you might be using.
$stripe = new \Stripe\StripeClient(getenv('STRIPE_SECRET_KEY'));

// This is your Stripe CLI webhook secret for testing your endpoint locally.
$endpoint_secret = 'whsec_x8ukHsWZmSfqX0VCeQLV9arbo6VXwSnJ';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  exit();
}

// Handle the event
switch ($event->type) {
    case 'customer.subscription.created':
      $subscription = $event->data->object;
      $subscriptionItem = $subscription->items->data[0];
      $productId = $subscriptionItem->price->product;
      $customer = $stripe->customers->retrieve($subscription->customer, []);
      $email = $customer->email;
      actualizarSuscripcion($email, $productId, 1);
      break;
  
    case 'customer.subscription.updated':
      $subscription = $event->data->object;
      $subscriptionItem = $subscription->items->data[0];
      $productId = $subscriptionItem->price->product;
      $customer = $stripe->customers->retrieve($subscription->customer, []);
      $email = $customer->email;
      actualizarSuscripcion($email, $productId, 1);
      break;
  
    case 'customer.subscription.deleted':
      $subscription = $event->data->object;
      $customer = $stripe->customers->retrieve($subscription->customer, []);
      $email = $customer->email;
      actualizarSuscripcion($email, false, 0);
      break;
  
    case 'invoice.payment_failed':
      $invoice = $event->data->object;
      $customer = $stripe->customers->retrieve($invoice->customer, []);
      $email = $customer->email;
      actualizarSuscripcion($email, false, 0);
      break;
  
    case 'invoice.payment_succeeded':
      $invoice = $event->data->object;
      $subscription = $stripe->subscriptions->retrieve($invoice->subscription, []);
      $subscriptionItem = $subscription->items->data[0];
      $productId = $subscriptionItem->price->product;
      $customer = $stripe->customers->retrieve($invoice->customer, []);
      $email = $customer->email;
      actualizarSuscripcion($email, $productId, 1);
      break;
  
    default:
      echo 'Received unknown event type ' . $event->type;
}

http_response_code(200);

function actualizarSuscripcion($email, $productId, $autorizado){
    $servername = getenv('WAIRBOT_DB_NAME');
    $username = getenv('TRELLO_DB_USER');
    $password = getenv('TRELLO_DB_PASSWORD');
    $dbname = getenv('TRELLO_DB_NAME');
    
    // Crear la conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if($productId == "prod_PE9Ydztr0Qkx22"){
        $productId = 1;
    }else if($productId == "prod_PE9dKEi6VR7EPf"){
        $productId = 2;
    }else if($productId == "prod_PE9lqWDZjYgSwh"){
        $productId = 3;
    }else{
        //producto de prueba
        if($productId == 'prod_PEDBou5V48Mi1r'){
            $productId = 4;
        }else{
            $productId = 0;
        }
    }

    // Actualizar la plan y autorizado
    $query_update = "UPDATE usuarios SET plan = $productId, autorizado = $autorizado WHERE email = '$email'";

    if ($conn->query($query_update) === TRUE) {
        echo "Record updated successfully";
    } else {
        //si no existe el usuario, se crea solo con el email, el plan y autorizado en 0
        $query_insert = "INSERT INTO usuarios (email, plan, autorizado) VALUES ('$email:::::', $productId, 0)";

        if ($conn->query($query_insert) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $query_insert . "<br>" . $conn->error;
        }
    }
}