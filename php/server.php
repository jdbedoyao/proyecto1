<?php
/*
//-- Autenticacion via HTTP
//------------------------------->
$user = array_key_exists('PHP_AUTH_USER',$_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
$pwd = array_key_exists('PHP_AUTH_PW',$_SERVER) ? $_SERVER['PHP_AUTH_PW'] : '';

if ($user !== 'mauro' || $pwd !== '1234') {
	die;
}
//-- Autenticacion via HTTP
//-------------------------------<
*/

/*
//-- Autenticacion via HMAC
//------------------------------->
if (!array_key_exists('HTTP_X_HASH',$_SERVER) || !array_key_exists('HTTP_X_TIMESTAMP',$_SERVER) || !array_key_exists('HTTP_X_UID',$_SERVER)) {
	die;
}

list($hash,$uid,$timestamp) = [
	$_SERVER['HTTP_X_HASH'],
	$_SERVER['HTTP_X_UID'],
	$_SERVER['HTTP_X_TIMESTAMP']
];

$secret = 'Sh!! No se lo cuentes a nadie!';

$newHash = sha1($uid.$timestamp.$secret);

if ($newHash !== $hash) {
	die;
}
// Autenticacion via HMAC
//-------------------------------<
*/



/*
// Autenticación vía Access Tokens
//------------------------------->
if (!array_key_exists('HTTP_X_TOKEN',$_SERVER)) {
	http_response_code(401);
	die;
}
$url = 'http://localhost:8001';

$ch = curl_init($url);
curl_setopt(
	$ch,
	CURLOPT_HTTPHEADER,
	[
		"X-Token: {$_SERVER['HTTP_X_TOKEN']}"
	]
);
curl_setopt(
	$ch,
	CURLOPT_RETURNTRANSFER,
	true
);
$ret = curl_exec($ch);
if ($ret !== 'true'){
	http_response_code(403);
	die;
}
// Autenticación vía Access Tokens
//-------------------------------<
*/


// Definimos los recursos disponibles
$allowedResourceTypes = [
	'books',
	'authors',
	'genres',
];

// Validamos que el recurso este disponible
$resourceType = $_GET['resource_type'];

if (!in_array($resourceType,$allowedResourceTypes)) {
	http_response_code(400);
	die;
}

// Defino los recursos
$books = [
    1 => [
        'titulo' => 'Lo que el viento se llevo',
        'id_autor' => 2,
        'id_genero' => 2,
    ],
    2 => [
        'titulo' => 'La Iliada',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
    3 => [
        'titulo' => 'La Odisea',
        'id_autor' => 1,
        'id_genero' => 1,
    ],
];

// Se indica al cliente que lo que recibirá es un json
header('Content-Type: application/json');

// Levantamos el id del recurso buscado
// utilizando un operador ternario, si existe el source_id en el array de $_GET lo metemos en la variable $resourceId
$resourceId = array_key_exists('resource_id', $_GET) ? $_GET['resource_id'] : '';

// Generamos la respuesta asumiendo que el pedido es correcto
switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
	case 'GET':
		if (empty($resourceId)) {
			echo json_encode($books);
		} else {
			if (array_key_exists($resourceId,$books)) {
				echo json_encode($books[$resourceId]);
			} else {
				http_response_code(404);
			}
		}
		break;
	case 'POST':
		$json = file_get_contents('php://input');
		$books[] = json_decode($json,true);
		//echo array_keys($books)[count($books)-1];
		end($books);         // move the internal pointer to the end of the array
		$key = key($books);  // fetches the key of the element pointed to by the internal pointer
		echo json_encode($books[$key]);
 		break;
	case 'PUT':
		// Validamos que el recurso buscado exista
		if (!empty($resourceId) && array_key_exists($resourceId,$books)) {
			// Tomamos la entrada curda
			$json = file_get_contents('php://input');

			// Tansformamos el json recibido a un nuevo elemento
			$books[$resourceId] = json_decode($json,true);

			echo json_encode($books[$resourceId]);
		}
		break;
	case 'DELETE':
		// Validamos que el recurso buscado exista
		if (!empty($resourceId) && array_key_exists($resourceId,$books)) {
			unset($books[$resourceId]);
			echo json_encode($books);
		}
		break;
}

// Inicio el servidor en la terminal 1
// php -S localhost:8000 server.php

// Terminal 2 ejecutar 
// curl http://localhost:8000 -v
// curl http://localhost:8000/\?resource_type\=books
// curl http://localhost:8000/\?resource_type\=books | jq