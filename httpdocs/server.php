<?php

//mostrar errores
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("accept: application/json");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'");

require_once "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once "./ia-text-process.php";

// Conexión a la base de datos (puedes modificar los valores según tu configuración)
$servername = "localhost";
$username = "trello_wairbot";
$password = "i9Y39_s7e";
$dbname = "trello_wairbot";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

const _SECRET_KEY = 'JITOXR2SQQvT7Bf6kw02qWV2DtHM64';

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

/*
// Crear la tabla si no existe
$query_create_table = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    autorizado BOOLEAN NOT NULL DEFAULT 0,
    trello VARCHAR(255) NOT NULL
    tokens longtext NOT NULL
    plan int(11) NOT NULL DEFAULT 0
)";
$conn->query($query_create_table);
*/

$post = file_get_contents('php://input');
$_POST = json_decode($post, true);

$funcion = $_POST['funcion'];

if($funcion === 'registro'){
    $email = $_POST['email'];
    $password = $_POST['password'];
    registrarUsuario($email, $password);

} else if($funcion === 'login'){
    $email = $_POST['email'];
    $password = $_POST['password'];
    login($email, $password);

} else if($funcion === 'autorizar'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    if(verificarToken($id, $token)){
        autorizar($id);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }

} else if($funcion === 'procesar'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $texto = $_POST['texto'];
    $trello = $_POST['trello'];
    $card = $_POST['card'];
    $campo = $_POST['campo'];
    $idioma = $card['idioma'];
    if(verificarToken($id, $token)){
        procesarTexto($id, $trello, $texto, $card, $campo, $idioma);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
} else if($funcion === 'trello'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $trello = $_POST['trello'];
    if(verificarToken($id, $token)){
        guardarTokenTrello($id, $trello);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
} else if($funcion === 'getUserData'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    if(verificarToken($id, $token)){
        $user = getUserData($id);

        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'success',
                'message' => 'Usuario obtenido correctamente',
                'id' => $user['id'],
                'email' => $user['email'],
                'autorizado' => $user['autorizado'],
                'trello' => $user['trello'],
                'token' => $token
            ]
        );
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
} else if($funcion === 'recuperarContrasena'){
    $email = $_POST['email'];
    generarNuevaContrasena($email);
    
}else if($funcion === 'actualizarCardTrello'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $trello = $_POST['trello'];
    $card = $_POST['card'];
    $campo = $_POST['campo'];
    $texto = $_POST['texto'];
    if(verificarToken($id, $token)){
        $trelloToken = $trello;
        $idCard = $card['id'];
        cambiarCarApiTrello($trelloToken, $idCard, $texto, $campo);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}else if($funcion === 'consultaGeneral'){
    $texto = $_POST['texto'];
    $card = $_POST['card'];
    
    //verificar token
    $id = $_POST['id'];
    $token = $_POST['token'];
    $idioma = $card['idioma'];

    if(verificarToken($id, $token)){
        consultaGeneral($texto, $card, $idioma);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}else if($funcion === 'nombreCardTrello'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $card = $_POST['card'];
    if(verificarToken($id, $token)){
        $titulo = $card['titulo'];
        $descripcion = $card['descripcion'];
        $idioma = $card['idioma'];

        generarTituloTrello($titulo, $descripcion, $idioma);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}else if($funcion === 'descripcionCardTrello'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $card = $_POST['card'];
    if(verificarToken($id, $token)){
        $titulo = $card['titulo'];
        $descripcion = $card['descripcion'];
        $idioma = $card['idioma'];
        
        generarDescripcionTrello($titulo, $descripcion, $idioma);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}else if($funcion === 'gestionarTokensOpenAI'){
    $id = $_POST['id'];
    $token = $_POST['token'];
    $tokensUsados = $_POST['tokensUsados'];
    if(verificarToken($id, $token)){
        gestionarTokensOpenAI($id, $tokensUsados);
    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}

function getUserData($id){
    global $conn;

    // Obtener el usuario de la base de datos
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();

    return $user;
}


// Función para registrar un usuario
function registrarUsuario($email, $password){
    global $conn;

    // Verificar si el correo electrónico ya existe
    $query_check_email = "SELECT * FROM usuarios WHERE email = '$email'";
    $result_check_email = $conn->query($query_check_email);

    if ($result_check_email->num_rows > 0) {
        //buscar un usuario con ese email + ":::::"
        $query_check_email = "SELECT * FROM usuarios WHERE email = '$email:::::'";
        $result_check_email = $conn->query($query_check_email);

        //si existe un usuario con ese email + ":::::" y su contraseña es nula o vacia, actualizar la contraseña
        if ($result_check_email->num_rows > 0) {
            //obtener el id del usuario
            $user = $result_check_email->fetch_assoc();
            $id = $user['id'];

            //actualizar la contraseña codificada, el email y el autorizado a 0
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $query_update_password = "UPDATE usuarios SET password = '$hashed_password', email = '$email', autorizado = 0 WHERE id = $id";
            $conn->query($query_update_password);

            // Obtener el usuario de la base de datos
            $query_get_user = "SELECT * FROM usuarios WHERE email = '$email'";
            $result = $conn->query($query_get_user);
            $user = $result->fetch_assoc();

            // Generar un token para el usuario
            $token = generarToken(
                [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'autorizado' => $user['autorizado']
                ]
            );

            // Devolver el token como respuesta
            echo json_encode(
                [
                    'token' => $token,
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'autorizado' => $user['autorizado'],
                    'trello' => $user['trello'],
                    'status' => 'success',
                    'message' => 'Usuario logueado correctamente'
                ]
            );
            return;
        }else{
            // El correo electrónico ya existe, devolver mensaje de error
            echo json_encode(['status' => 'error', 'message' => 'El correo electrónico ya está registrado']);
            return;
        }
    }

    // Cifrar la contraseña antes de almacenarla en la base de datos
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario en la base de datos
    $query_insert_user = "INSERT INTO usuarios (email, password) VALUES ('$email', '$hashed_password')";
    $conn->query($query_insert_user);

    // Obtener el ID del nuevo usuario
    $id = $conn->insert_id;

    // Obtener autorización
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();
    $autorizado = $user['autorizado'];

    // Generar un token para el nuevo usuario
    $token = generarToken(['id' => $id, 'email' => $email, 'autorizado' => $autorizado]);

    // Devolver el token como respuesta
    echo json_encode(
        [
            'token' => $token,
            'id' => $id,
            'email' => $email,
            'autorizado' => $autorizado,
            'trello' => $user['trello'],
            'status' => 'success',
            'message' => 'Usuario registrado correctamente'
        ]
    );
}


// Función para generar un token JWT
function generarToken($data){
    $payload = [
        'id' => $data['id'],
        'email' => $data['email'],
        'autorizado' => $data['autorizado']
    ];

    return JWT::encode($payload, _SECRET_KEY, 'HS256');
}

// Función para verificar el token y ejecutar una función de ejemplo
function verificarToken($id, $token){
    // Verificar el token
    $decoded_token = JWT::decode($token, new Key(_SECRET_KEY, 'HS256'));

    // Verificar que el ID del token coincide con el ID proporcionado
    $result = false;
    if ($decoded_token->id == $id) {
        $result = true;
    }

    return $result;
}

// Función para loguear un usuario
function login($email, $password){
    global $conn;

    // Obtener el usuario de la base de datos
    $query_get_user = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();

    // Verificar que el usuario existe y que la contraseña es correcta
    if ($user && password_verify($password, $user['password'])) {
        // Verificar que el usuario está autorizado
        
        // Generar un token para el usuario
        $token = generarToken(
            [
                'id' => $user['id'],
                'email' => $user['email'],
                'autorizado' => $user['autorizado']
            ]
        );
        // Devolver el token como respuesta
        echo json_encode(
            [
                'token' => $token,
                'id' => $user['id'],
                'email' => $user['email'],
                'autorizado' => $user['autorizado'],
                'trello' => "",
                'status' => 'success',
                'message' => 'Usuario logueado correctamente'
            ]
        );
        
    } else {
        // Devolver un error si el usuario no existe o la contraseña es incorrecta
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario o contraseña incorrectos'
            ]
        );
    }
}

// Función para autorizar un usuario
function autorizar($id){
    global $conn;

    // Actualizar el usuario en la base de datos
    $query_update_user = "UPDATE usuarios SET autorizado = 1 WHERE id = $id";
    $conn->query($query_update_user);

    // Obtener el usuario de la base de datos
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();

    // Generar un token para el usuario
    $token = generarToken(
        [
            'id' => $user['id'],
            'email' => $user['email'],
            'autorizado' => $user['autorizado']
        ]
    );

    // Devolver el token como respuesta
    echo json_encode(
        [
            'token' => $token,
            'id' => $user['id'],
            'email' => $user['email'],
            'autorizado' => $user['autorizado'],
            'trello' => $user['trello'],
            'status' => 'success',
            'message' => 'Usuario autorizado correctamente'
        ]
    );
}

function guardarTokenTrello($id, $trello){
    global $conn;

    // Actualizar el usuario en la base de datos
    $query_update_user = "UPDATE usuarios SET trello = '$trello' WHERE id = $id";
    $conn->query($query_update_user);

    // Obtener el usuario de la base de datos
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();

    // Generar un token para el usuario
    $token = generarToken(
        [
            'id' => $user['id'],
            'email' => $user['email'],
            'autorizado' => $user['autorizado']
        ]
    );

    // Devolver el token como respuesta
    echo json_encode(
        [
            'token' => $token,
            'id' => $user['id'],
            'email' => $user['email'],
            'autorizado' => $user['autorizado'],
            'trello' => $user['trello'],
            'status' => 'success',
            'message' => 'Usuario autorizado correctamente'
        ]
    );
}

// Función para procesar un texto
function procesarTexto($id, $trello, $texto, $card, $campo, $idioma){
    global $conn;

    // Verificar que el usuario está autorizado
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();
    if ($user['autorizado'] == 1) {
        // Procesar el texto
        $IaResultado = TextProcess::procesarTexto($texto, $card, $idioma);

        $texto = $IaResultado['respuesta'];
        $tokensUsados = $IaResultado['tokensUsados'];

        echo json_encode(
            [
                'status' => 'success',
                'message' => 'Texto procesado correctamente',
                'iaResultado' => $texto,
                'tokensUsados' => $tokensUsados,
                'campo' => $campo,
            ]
        );

    } else {
        // Devolver un error si el usuario no está autorizado
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'Usuario no autorizado'
            ]
        );
    }
}

function consultaGeneral($texto, $card, $idioma){
    $respuesta = TextProcess::consultaGeneral($texto, $card, $idioma);

    $texto = $respuesta['respuesta'];
    $tokensUsados = $respuesta['tokensUsados'];

    echo json_encode(
        [
            'status' => 'success',
            'message' => 'Texto procesado correctamente',
            'respuesta' => $texto,
            'tokensUsados' => $tokensUsados,
        ]
    );
}

function cambiarCarApiTrello($trelloToken, $idCard, $texto, $campo){
    $cardId = $idCard;
    $apiKey = "64b5b4f9c7e3e7606d024d4f3529d143";
    $apiToken = $trelloToken;

    $endpoint = "https://api.trello.com/1/cards/{$cardId}?key={$apiKey}&token={$apiToken}";

    if($campo === "titulo"){
        $data = [
            'name' => $texto
        ];
    }

    if($campo === "descripcion"){
        $data = [
            'desc' => $texto
        ];
    }

    $ch = curl_init($endpoint);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    }

    curl_close($ch);

    echo json_encode(
        [
            'status' => 'success',
            'message' => "$campo cambiado correctamente",
            'response' => $response,
            'campo' => $campo,
        ]
    );
}

function generarNuevaContrasena($email) {
    global $conn;

    // Verificar si el correo electrónico existe en la base de datos
    $query_check_email = "SELECT * FROM usuarios WHERE email = '$email'";
    $result_check_email = $conn->query($query_check_email);

    if ($result_check_email->num_rows > 0) {
        // Generar una nueva contraseña
        $nueva_contrasena = generarContrasenaAleatoria();

        // Cifrar la nueva contraseña antes de almacenarla en la base de datos
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $query_update_password = "UPDATE usuarios SET password = '$hashed_password' WHERE email = '$email'";
        $conn->query($query_update_password);

        // Enviar el correo electrónico con la nueva contraseña
        enviarCorreoRecuperacion($email, $nueva_contrasena);

        // Devolver mensaje de éxito
        echo json_encode(['status' => 'success', 'message' => 'Se ha enviado una nueva contraseña al correo electrónico']);
    } else {
        // El correo electrónico no existe en la base de datos
        echo json_encode(['status' => 'error', 'message' => 'Correo electrónico no encontrado']);
    }
}

function enviarCorreoRecuperacion($destinatario, $nuevaContrasena) {
    // Configuración del correo electrónico
    $asunto = 'Recuperación de contraseña';
    $mensaje = 'Hola, has solicitado la recuperación de tu contraseña. Tu nueva contraseña es: ' . $nuevaContrasena;

    // Encabezados del correo
    $cabeceras = 'From: tu_direccion_de_correo@example.com' . "\r\n" .
                 'Reply-To: tu_direccion_de_correo@example.com' . "\r\n" .
                 'X-Mailer: PHP/' . phpversion();

    // Intenta enviar el correo
    if (mail($destinatario, $asunto, $mensaje, $cabeceras)) {
        // Éxito al enviar el correo
        return true;
    } else {
        // Error al enviar el correo
        return false;
    }
}

function generarContrasenaAleatoria($longitud = 8) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $contrasena = '';
    for ($i = 0; $i < $longitud; $i++) {
        $index = rand(0, strlen($caracteres) - 1);
        $contrasena .= $caracteres[$index];
    }
    return $contrasena;
}

function generarTituloTrello($titulo, $descripcion, $idioma){
    $tituloTrello = TextProcess::generarTituloTrello($titulo, $descripcion, $idioma);

    $texto = $tituloTrello['respuesta'];
    $tokensUsados = $tituloTrello['tokensUsados'];

    echo json_encode(
        [
            'status' => 'success',
            'message' => 'Titulo generado correctamente',
            'titulo' => $texto,
            'tokensUsados' => $tokensUsados,
        ]
    );
}

function generarDescripcionTrello($titulo, $descripcion, $idioma){
    $descripcionTrello = TextProcess::generarDescripcionTrello($titulo, $descripcion, $idioma);

    $texto = $descripcionTrello['respuesta'];
    $tokensUsados = $descripcionTrello['tokensUsados'];

    echo json_encode(
        [
            'status' => 'success',
            'message' => 'Descripcion generada correctamente',
            'descripcion' => $texto,
            'tokensUsados' => $tokensUsados,
        ]
    );
}

function gestionarTokensOpenAI($id, $tokensUsados){
    //obtener de la base de datos la columna tokens y el plan
    global $conn;

    // Obtener el usuario de la base de datos
    $query_get_user = "SELECT * FROM usuarios WHERE id = $id";
    $result = $conn->query($query_get_user);
    $user = $result->fetch_assoc();

    $tokens = $user['tokens'];
    $plan = $user['plan'];
    if($plan === null || $plan === ""){
        $plan = 0;
    } else {
        $plan = intval($plan);
    }

    $autorizado = $user['autorizado'];

    //si tokens es nullo o vacio, crear un array vacio
    if($tokens === null || $tokens === ""){
        $tokens = [
            'consumidos' => 0,
            'fecha' => date('Y-m-d H:i:s')
        ];
    } else {
        //si tokens no es nullo o vacio, convertirlo en array
        $tokens = json_decode($tokens, true);
    }

    //si la fecha es del mes anterior al actual resetear los tokens consumidos a 0
    $fecha = new DateTime($tokens['fecha']);
    $fechaActual = new DateTime();
    $fechaActual->modify('first day of this month');
    $fechaActual->setTime(0, 0, 0);
    $fecha->modify('first day of this month');
    $fecha->setTime(0, 0, 0);
    if($fecha < $fechaActual){
        $tokens['consumidos'] = 0;
    }

    //si $tokens consumidos es 0 y autorizado es 0 actualizar autorizado a 1
    if($tokens['consumidos'] === 0 && $autorizado === 0){
        $query_update_user = "UPDATE usuarios SET autorizado = 1 WHERE id = $id";
        $conn->query($query_update_user);
    }

    //sumar los tokens consumidos a los tokens consumidos actuales
    $tokens['consumidos'] += $tokensUsados;

    if ($plan === 1){
        //si el plan es 1, comprobar que los tokens consumidos no superen los 2.0Millones
        if($tokens['consumidos'] > 2000000 && $tokens['consumidos'] < 2500000){
            //si los tokens consumidos superan los 2.5Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'alert',
                    'message' => 'Tokens a punto de agotarse',
                ]
            );
            return;
        }

        if($tokens['consumidos'] > 2500000){
            //actualizar autorizado a 0
            $query_update_user = "UPDATE usuarios SET autorizado = 0 WHERE id = $id";
            $conn->query($query_update_user);

            //si los tokens consumidos superan los 2.5Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => 'Tokens agotados',
                ]
            );
            return;
        }
    }

    if ($plan === 2){
        //si el plan es 2, comprobar que los tokens consumidos no superen los 5.0Millones
        if($tokens['consumidos'] > 5000000 && $tokens['consumidos'] < 5500000){
            //si los tokens consumidos superan los 5.0Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'alert',
                    'message' => 'Tokens a punto de agotarse',
                ]
            );
            return;
        }

        if($tokens['consumidos'] > 5500000){
            //actualizar autorizado a 0
            $query_update_user = "UPDATE usuarios SET autorizado = 0 WHERE id = $id";
            $conn->query($query_update_user);

            //si los tokens consumidos superan los 5.5Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => 'Tokens agotados',
                ]
            );
            return;
        }
    }

    if ($plan === 3){
        //si el plan es 3, comprobar que los tokens consumidos no superen los 10.0Millones
        if($tokens['consumidos'] > 10000000 && $tokens['consumidos'] < 11000000){
            //si los tokens consumidos superan los 10.0Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'alert',
                    'message' => 'Tokens a punto de agotarse',
                ]
            );
            return;
        }

        if($tokens['consumidos'] > 11000000){
            //actualizar autorizado a 0
            $query_update_user = "UPDATE usuarios SET autorizado = 0 WHERE id = $id";
            $conn->query($query_update_user);

            //si los tokens consumidos superan los 11.0Millones, devolver un error
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => 'Tokens agotados',
                ]
            );
            return;
        }
    }

    //actualizar los tokens consumidos y la fecha en la base de datos
    $tokens = json_encode($tokens);
    $query_update_user = "UPDATE usuarios SET tokens = '$tokens' WHERE id = $id";
    $conn->query($query_update_user);

    echo json_encode(
        [
            'status' => 'success',
            'message' => 'Tokens actualizados correctamente',
        ]
    );
}


// Cerrar la conexión a la base de datos al finalizar
$conn->close();