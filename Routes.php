<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//require '../vendor/autoload.php';

$app = new \Slim\App;

$api = new dellirom\Api;

// Get All Items
$app->get( $api->route . 's', function (Request $request, Response $response ) {
	$api 		= new Api;
	$api->crud(array('crud' => 'read'));
});

// Single Item
$app->get( $api->route . '/{id}', function (Request $request, Response $response ) {
	$api 		= new Api;
	$api->crud(array('crud' => 'read', 'id' => $request->getAttribute('id')));
});

// Add Item
$app->post( $api->route . '/add', function (Request $request, Response $response) {
	$api 		= new Api;
	$fields = array_flip($api->getFields());
	foreach ($fields as $field => $value) {
		$fields[$field] =  $request->getParam($field);
	}
	$api->crud( array('crud' => 'create', 'fields' => $fields) );
});

// Update Item
$app->put( $api->route . '/{id}', function (Request $request, Response $response) {
	$api 		= new Api;
	$fields = array_flip($api->getFields());
	foreach ($fields as $field => $value) {
		$fields[$field] =  $request->getParam($field);
	}
	$api->crud( array('crud' => 'update', 'id' => $request->getAttribute('id'), 'fields' => $fields) );
});

// Delete Item
$app->delete($api->route . '/{id}', function (Request $request, Response $response) {
	$api 		= new Api;
	$api->crud( array('crud' => 'delete', 'id' => $request->getAttribute('id')) );
});


