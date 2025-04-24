<?php
//ver errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//echo ini_get('post_max_size');
//ini_set('post_max_size', '75M');

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
if(!($clase === 'Login' || ($clase === 'Usuarios' && $funcion === 'preinscripcion'))){
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

    ################################# DATOS USUARIOS #################################
    $_SESSION['user_id'] = $requestsUsuario['id'] ?? null;
    $_SESSION['user_rol'] = $requestsUsuario['rol'] ?? null;
    $_SESSION['user_email'] = $requestsUsuario['email'] ?? null;
    $_SESSION['rows_per_page'] = $requests['rows_per_page'] ?? 24;
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

/*

https://pruebasbot.wairbot.es/asdegal/
Usuario: admin
Contraseña: admin

*CICLO*
* 1. Crear coodinador
* 2. Crear voluntario relacionado con el coordinador
* 3. Crear beneficiario relacionado con el coordinador
* 4. Crear acción relacionada con el coordinador, voluntario y beneficiario

*COORDINADOR*
* Puede acceder y editar a los datos de las acciones, voluntarios y beneficiarios relacionados con él

*VOLUNTARIO*
* Puede acceder y editar a los datos de las acciones relacionadas con él

*FICHAS*
* Las fichas a parte de los datos directos, también muestra los datos relacionados
* Los campos que tienen el simbilo del lapiz son para generar campos de selectores

--------------------------------------
*CAMBIOS*
* Se ha insertado todos los datos de voluntarios y beneficiarios (ACTIVOS Y NO ACTIVOS), las fechas de nacimento que no estaban se han calculado por la edad a partir de la fecha actual
* Se puede cambiar el rol de los voluntarios y coordinadores (ADMINISTRADOR, COORDINADOR, VOLUNTARIO)
* Pantalla de voluntarios de baja
* Pantalla de beneficiarios de baja
* Se pueden marcar tareas como PERIODICAS y añadir un periodo
* Pantalla para ver las tareas periodicas
* Calculo de edad por fecha de nacimiento
* Se han añadido nuevos campos a las fichas de voluntarios y beneficiarios
*/