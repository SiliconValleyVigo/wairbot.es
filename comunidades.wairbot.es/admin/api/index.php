<?php

//echo ini_get('post_max_size');
//ini_set('post_max_size', '75M');

//mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

//headers
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("accept: application/json");

//obtener los datos
$postData = file_get_contents('php://input');
$requests = !empty($postData) ? json_decode($postData, true) : array();

################################# IMPORTACIONES #################################
//incluir toda la carpeta class
foreach (glob("class/*.php") as $filename){
    include $filename;
}

############################# INICIAR BASE DE DATOS #############################
$_SESSION['db_connection'] = DB::initDB();

################################# VARIABLES #################################
$clase = $requests['clase'];
$funcion = $requests['funcion'];

$data = $requests;
unset($data['token']);
unset($data['clase']);
unset($data['funcion']);

//Si $requests['usuario'] no es un array, convertirlo a array
if(isset($requests['usuario'])){
    if(!is_array($requests['usuario'])){
        $requestsUsuario = json_decode($requests['usuario'], true);
    }else{
        $requestsUsuario = $requests['usuario'];
    }
}

################################# COMPROBAR TOKEN #################################
if($clase !== 'Login'){
    //comprobar si el token es válido
    $token = $requests['token'];

    $usuario = [
        'email' => $requestsUsuario['email'],
        'rol' => $requestsUsuario['rol']
    ];

    $tokenValido = login::verifyToken($token, $usuario);

    unset($data['usuario']);

    if(!$tokenValido){
        echo json_encode([
            'status' => 'error',
            'message' => 'Token inválido'
        ]);
        exit();
    }

    $sonId40 = [2, 38,39, 41, 42, 43];
    if(in_array($requestsUsuario['id'], $sonId40)){
        $requestsUsuario['id'] = 40;
        $requestsUsuario['email'] = "Gesfincas";
    }

    ################################# DATOS USUARIOS #################################
    $_SESSION['user_id'] = $requestsUsuario['id'] ?? null;
    $_SESSION['user_rol'] = $requestsUsuario['rol'] ?? null;
    $_SESSION['user_email'] = $requestsUsuario['email'] ?? null;
    $_SESSION['rows_per_page'] = $requests['rows_per_page'] ?? 24;
    $_SESSION['ulr_api'] = "https://gestion-datos.wairbot.com/bot-datos/";
    //$_SESSION['ulr_api'] = "http://localhost/wairbot-datos/bot-datos/";
}

################################# EJECUTAR #################################
if(!$data){
    $data = [
        'usuario' => $requestsUsuario
    ];
}

$resultado = $clase::$funcion($data);
if (is_array($resultado)) {
    echo json_encode($resultado);
} else {
    echo $resultado;
}

############################# CERRAR BASE DE DATOS #############################
$_SESSION['db_connection']->close();

