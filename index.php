<?php
require 'vendor/autoload.php';
require 'config.php';
require 'rb.php';
// register Slim auto-loader
//\Slim\Slim::registerAutoloader();

// set up database connection
R::setup('mysql:host=localhost;dbname='.DATABASE,  USERNAME, PASSWORD);
R::freeze(true);

// initialize app
$app = new \Slim\Slim();

// handle OPTIONS requests for /stories
$app->options('/meta/', function () use ($app) {
   exit;
});
// handle OPTIONS requests for /stories
$app->options('/sources/:id', function () use ($app) {
   exit;
});

$app->options('/users/me', function () use ($app) {
   exit;
});

$app->options('/stories/', function () use ($app) {
    exit;
});
// handle OPTIONS requests for /stories/:id
$app->options('/stories/:id', function () use ($app) {
    exit;
});
// handle GET requests for /metadata
$app->get('/sources/:id', function ($id) use ($app) {
//     query database for all articles
    $sources = R::find('sources', 'whose=?', array($id) );

//    print_r($fields);
//    foreach($fields as $field){
//        $phase = R::load('phases', $fields->phase);
////        print_r($order->phase);die;
//        $order->phase = $phase;
//        //print_r($phase);
//    }

    // send response header for JSON content type
    $app->response()->header('Content-Type', 'application/json');

    // return JSON-encoded response body with query results
    echo json_encode(R::exportAll($sources));
});
// handle GET requests for /metadata
$app->get('/meta/', function () use ($app) {
//    print_r($limit);
//     query database for all articles
    $fields = R::findAll('fields' );

//    print_r($fields);
//    foreach($fields as $field){
//        $phase = R::load('phases', $fields->phase);
////        print_r($order->phase);die;
//        $order->phase = $phase;
//        //print_r($phase);
//    }

    // send response header for JSON content type
    $app->response()->header('Content-Type', 'application/json');

    // return JSON-encoded response body with query results
    echo json_encode(R::exportAll($fields));
});




// handle GET requests for /articles
$app->get('/stories/', function () use ($app) {
    $request = $app->request();
    $limit = $request->get('pageSize');
//    print_r($limit);
//     query database for all articles
    $orders = R::findAll('orders', "LIMIT 100" );

//    print_r($orders);
    foreach($orders as $order){
        $phase = R::load('phases', $order->phase);
//        print_r($order->phase);die;
        $order->phase = $phase;
        //print_r($phase);
    }

    // send response header for JSON content type
    $app->response()->header('Content-Type', 'application/json');

    // return JSON-encoded response body with query results
    echo json_encode(R::exportAll($orders));
});


class ResourceNotFoundException extends Exception {}

// handle GET requests for /articles/:id
$app->get('/stories/:id', function ($id) use ($app) {
    try {
        // query database for single article
        $order = R::findOne('orders', 'id=?', array($id));
        $phase = R::load('phases', $order->phase);
//        print_r($order->phase);die;
        $order->phase = $phase;
//        echo "<pre>";
        $order = R::exportAll($order);
//        print_r($order[0]);

        if ($order) {
            // if found, return JSON response
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode($order[0]);
        } else {
            // else throw exception
            throw new ResourceNotFoundException();
        }
    } catch (ResourceNotFoundException $e) {
        // return 404 server error
        $app->response()->status(404);
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// handle PUT requests to /orders/:id
$app->put('/stories/:id', function ($id) use ($app) {
    try {
        // get and decode JSON request body
        $request = $app->request();
        $body = $request->getBody();
        $input = json_decode($body);

        // query database for single order
        $order = R::findOne('orders', 'id=?', array($id));
        // return JSON-encoded response body
        if ($order) {
            //copy new values to order
                foreach($input as $prop => $value){

                    if(isset($order->$prop) && !is_object($value)){
//                        echo "\n\r$prop => $value\n\r";
                        $order->$prop = $value;
                    }
                }
            R::store($order);
            $app->response()->header('Content-Type', 'application/json');
            echo json_encode(R::exportAll($order));
        } else {
            throw new ResourceNotFoundException();
        }
    } catch (ResourceNotFoundException $e) {
        $app->response()->status(404);
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// handle GET requests for /articles
$app->get('/users/me', function () use ($app) {
    try {
//    echo "hello";exit;
        $request = $app->request();
        $username = $request->get('username');
        $password = $request->get('password');
        $origin = $request->get('origin');
        if($username == 'orcon' && $password == 'orcon'){
            $accesstoken = "ooN2yP1MXWCAZh2LM3Nz";
            $response = array('accessToken'=>$accesstoken);
            $app->response()->header('Content-Type', 'application/json');
            // return JSON-encoded response body with query results
            echo json_encode($response);
        }else{
            $app->response()->status(401);
        }
        // send response header for JSON content type

    } catch (ResourceNotFoundException $e) {
        $app->response()->status(404);
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

// run
$app->run();