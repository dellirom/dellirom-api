# Fast API Dellirom

#Using for slim

```
code block
```

```php
//php code
$foo = new BarClass();
```

## Get All Items

```php
// Get All Items
$app->get( $api->route . 's', function (Request $request, Response $response ) {
	$api 		= new dellirom\Api;
	$api->crud(array('crud' => 'read'));
});
```

## Single Item
```php
$app->get( $api->route . '/{id}', function (Request $request, Response $response ) {
	$api 		= new dellirom\Api;
	$api->crud(array('crud' => 'read', 'id' => $request->getAttribute('id')));
});
```

## Add Item
```php
$app->post( $api->route . '/add', function (Request $request, Response $response) {
	$api 		= new dellirom\Api;
	$fields = array_flip($api->getFields());
	foreach ($fields as $field => $value) {
		$fields[$field] =  $request->getParam($field);
	}
	$api->crud( array('crud' => 'create', 'fields' => $fields) );
});
```

## Update Item
```php
$app->put( $api->route . '/{id}', function (Request $request, Response $response) {
	$api 		= new dellirom\Api;
	$fields = array_flip($api->getFields());
	foreach ($fields as $field => $value) {
		$fields[$field] =  $request->getParam($field);
	}
	$api->crud( array('crud' => 'update', 'id' => $request->getAttribute('id'), 'fields' => $fields) );
});
```

## Delete Item
```php
$app->delete($api->route . '/{id}', function (Request $request, Response $response) {
	$api 		= new dellirom\Api;
	$api->crud( array('crud' => 'delete', 'id' => $request->getAttribute('id')) );
});
```