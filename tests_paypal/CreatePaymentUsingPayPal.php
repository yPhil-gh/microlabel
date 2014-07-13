<?php

// # Create Payment using PayPal as payment method
// This sample code demonstrates how you can process a
// PayPal Account based Payment.
// API used: /v1/payments/payment

require __DIR__ . '/../microlabel_paypal/bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
session_start();

// ### Payer
// A resource representing a Payer that funds a payment
// For paypal account payments, set payment method
// to 'paypal'.
$payer = new Payer();
$payer->setPaymentMethod("paypal");

$panier=array();
$total=0;
$currency="EUR";

//attention à la gestion des / dans les noms d'artistes ou d'album ou de song.
// tu dois réfléchir au problème de renommage de chanson ou d'album, impossible ensuite de faire le lien avec un article. A moins de gérer des id invariants pour les songs.
// impossible en réalité de renommer/supprimer une chanson qui a un jour été achetée.
$article= new Item();
$amount=1;
$item_price=1;
$product_type=1;
$product_id=1;
$article->setName('Song ('.round($amount/$item_price,2).'): Azero/Panopticon-demo/Good Game')
        ->setCurrency($currency)
        ->setQuantity(1)
        ->setPrice(number_format($amount, 2, ".", "" ))
        ->setSku("$product_type/$product_id")
    ;
$panier[]=$article;
$total+=$amount;

$article= new Item();
$amount=6.1;
$item_price=1;
$product_type=1;
$product_id=2;
$article->setName('Song Fan('.round($amount/$item_price,2).'): Azero/Panopticon-demo/Day Patrol')
        ->setCurrency($currency)
        ->setQuantity(1)
        ->setPrice(number_format($amount, 2, ".", "" ))
        ->setSku("$product_type/$product_id")
    ;
$panier[]=$article;
$total+=$amount;

$article= new Item();
$amount=15;
$item_price=10;
$product_type=2;
$product_id=5;
$article->setName('Album('.round($amount/$item_price,2).'): Azero/Counternatures')
        ->setCurrency($currency)
        ->setQuantity(1)
        ->setPrice(number_format($amount, 2, ".", "" ))
        ->setSku("$product_type/$product_id")
    ;
$panier[]=$article;
$total+=$amount;

$article= new Item();
$amount=1000;
$item_price=10;
$product_type=3;
$product_id=7;
$article->setName('Artist Adept('.round($amount/$item_price,2).'): Azero')
        ->setCurrency($currency)
        ->setQuantity(1)
        ->setPrice(number_format($amount, 2, ".", "" ))
        ->setSku("$product_type/$product_id")
        ;
$panier[]=$article;
$total+=$amount;

$itemList = new ItemList();
$itemList->setItems($panier);

// ### Amount
// Lets you specify a payment amount.
// You can also specify additional details
// such as shipping, tax.
$amount = new Amount();
$amount->setCurrency($currency)
       ->setTotal(number_format($total,2,".",""));

// ### Transaction
// A transaction defines the contract of a
// payment - what is the payment for and who
// is fulfilling it.
$transaction = new Transaction();
$transaction->setAmount($amount)
	->setItemList($itemList)
	->setDescription("Achats Microlabel");

// ### Redirect urls
// Set the urls that the buyer must be redirected to after
// payment approval/ cancellation.
$baseUrl = getBaseUrl();
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
	->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

// ### Payment
// A Payment Resource; create one using
// the above types and intent set to 'sale'
$payment = new Payment();
$payment->setIntent("sale")
	->setPayer($payer)
	->setRedirectUrls($redirectUrls)
	->setTransactions(array($transaction));

// ### Create Payment
// Create a payment by calling the 'create' method
// passing it a valid apiContext.
// (See bootstrap.php for more on `ApiContext`)
// The return object contains the state and the
// url to which the buyer must be redirected to
// for payment approval
try {
	$payment->create($apiContext);
} catch (PayPal\Exception\PPConnectionException $ex) {
	echo "Exception: " . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}

// ### Get redirect url
// The API response provides the url that you must redirect
// the buyer to. Retrieve the url from the $payment->getLinks()
// method
foreach($payment->getLinks() as $link) {
	if($link->getRel() == 'approval_url') {
		$redirectUrl = $link->getHref();
		break;
	}
}

// ### Redirect buyer to PayPal website
// Save the payment id so that you can 'complete' the payment
// once the buyer approves the payment and is redirected
// back to your website.
//
// It is not a great idea to store the payment id
// in the session. In a real world app, you may want to
// store the payment id in a database.
$_SESSION['paymentId'] = $payment->getId();
if(isset($redirectUrl)) {
	header("Location: $redirectUrl");
	exit;
}
