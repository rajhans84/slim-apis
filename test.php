<?php
require 'vendor/autoload.php';
require 'config.php';
require 'rb.php';
$toolbox = R::setup('mysql:host=localhost;dbname='.DATABASE,  USERNAME, PASSWORD);
$redbean = $toolbox->getRedBean();
$a = new RedBean_AssociationManager( $toolbox );

//////////////
$order = $redbean->load("orders",5);

$phase = $redbean->load("phases", $order->phase);

//
$a->associate($phase, $order);
$records = $a->related($phase, "orders" );

$order = R::exportAll($order);
$phase = R::exportAll($phase);

echo "<pre>";
print_r($order);
print_r($phase);
print_r($records);
////////
echo json_encode($order[0]);