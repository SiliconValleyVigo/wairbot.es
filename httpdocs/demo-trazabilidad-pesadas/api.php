<?php

session_start();

const _API = "https://gestion-datos.wairbot.com/bot-datos/";
//const _API = "http://localhost/wairbot-datos/bot-datos/";

//headers
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("accept: application/json");

//obtener los datos de la api de mensajería (whatsapp)
$postData = file_get_contents('php://input');
$requests = !empty($postData) ? json_decode($postData, true) : array();

$_SESSION["funcion_admin"] = $requests["funcion_admin"];

//conexion a la base de datos
$NAME = getenv('WAIRBOT_DB_NAME');
$USER = getenv('WAIRBOT_DB_USER');
$PASS = getenv('WAIRBOT_DB_PASSWORD');
$HOST = getenv('WAIRBOT_DB_HOST');

$conn = mysqli_connect($HOST, $USER, $PASS, $NAME);

$ejecutar = call_user_func($_SESSION["funcion_admin"], $requests);
echo $ejecutar;

function create($requests){
    global $conn;
    $tabla = 'agregar_pesada_demotrazabilidadpesadas';
    $codigo_1 = $requests["codigo_1"];
    $barco_2 = $requests["barco_2"];
    $puerto_3 = $requests["puerto_3"];
    $producto_4 = $requests["producto_4"];
    $peso_5 = $requests["peso_5"];  
    $telefono_usuario = "346123456789";

    $sql = "INSERT INTO $tabla (codigo_1, barco_2, puerto_3, producto_4, peso_5, telefono_usuario) VALUES ('$codigo_1', '$barco_2', '$puerto_3', '$producto_4', '$peso_5', '$telefono_usuario')";
    $result = mysqli_query($conn, $sql);

    if($result){
        $response = array(
            "status" => "success",
            "message" => "Datos guardados correctamente"
        );
    }else{
        $response = array(
            "status" => "error",
            "message" => "Error al guardar los datos"
        );
    }

    return json_encode($response);
}

function getPesadasDescargadasAndPesadasSinDescargar(){
    global $conn;
    $tabla = 'agregar_pesada_demotrazabilidadpesadas';
    $sql = "SELECT * FROM $tabla";
    $result = mysqli_query($conn, $sql);

    $pesadas_descargadas = [];
    $pesadas_sin_descargar = [];

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            //si tiene fecha de salida está descargado, sino no está sin descargar
            if($row["fecha_salida"] != "" && $row["fecha_salida"] != null){
                $pesadas_descargadas[] = $row;
            }else{
                $pesadas_sin_descargar[] = $row;
            }
        }
    }

    //obtener el número de pesadas descargadas y sin descargar
    $num_pesadas_descargadas = count($pesadas_descargadas);
    $num_pesadas_sin_descargar = count($pesadas_sin_descargar);

    //obtener el peso total de las pesadas descargadas
    $peso_total_descargadas = 0;
    foreach($pesadas_descargadas as $pesada){
        //convertir el peso a float con dos decimales
        $peso_total_descargadas += floatval($pesada["peso_5"]);
    }

    //obtener el peso total de las pesadas sin descargar
    $peso_total_sin_descargar = 0;
    foreach($pesadas_sin_descargar as $pesada){
        //convertir el peso a float con dos decimales
        $peso_total_sin_descargar += floatval($pesada["peso_5"]);
    }

    //obtener el porcentaje de pesadas descargadas
    //comprobar que no se divida por 0
    if($num_pesadas_descargadas == 0 && $num_pesadas_sin_descargar == 0){
        $porcentaje_pesadas_descargadas = 0;
    }else if($num_pesadas_descargadas == 0 && $num_pesadas_sin_descargar > 0){
        $porcentaje_pesadas_descargadas = 0;
    }else{
        $porcentaje_pesadas_descargadas = ($num_pesadas_descargadas * 100) / ($num_pesadas_descargadas + $num_pesadas_sin_descargar);
    }
    
    //obtener el porcentaje de pesadas sin descargar siendo la diferencia entre 100 y el porcentaje de pesadas descargadas
    $porcentaje_pesadas_sin_descargar = 100 - $porcentaje_pesadas_descargadas;

    //si no hay pesadas descargadas el porcentaje de pesadas sin descargar también es 0
    if($num_pesadas_descargadas == 0){
        $porcentaje_pesadas_sin_descargar = 0;
    }

    //si no hay ninguno descargado pero hay al menos uno sin descargar el porcentaje de pesadas sin descargar es 100
    if($num_pesadas_descargadas == 0 && $num_pesadas_sin_descargar > 0){
        $porcentaje_pesadas_sin_descargar = 100;
    }

    $response = [
        "status" => "success",
        "descargadas" => [
            "num_pesadas" => $num_pesadas_descargadas,
            "peso_total" => $peso_total_descargadas,
            "porcentaje" => $porcentaje_pesadas_descargadas
        ],
        "sin_descargar" => [
            "num_pesadas" => $num_pesadas_sin_descargar,
            "peso_total" => $peso_total_sin_descargar,
            "porcentaje" => $porcentaje_pesadas_sin_descargar
        ]
    ];

    return json_encode($response);
}

function getDataBaseBascula(){
    global $conn;
    $tabla = 'pesadas_tbasculas';
    $sql = "SELECT * FROM $tabla";
    $result = mysqli_query($conn, $sql);

    $pesadas = [];

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $pesadas[] = $row;
        }
    }

    $response = [
        "status" => "success",
        "pesadas" => $pesadas
    ];

    return json_encode($response);
}

function borrarTodo($requests){
    global $conn;
    $tabla = 'agregar_pesada_demotrazabilidadpesadas';
    $sql = "DELETE FROM $tabla";
    $result = mysqli_query($conn, $sql);

    $tabla = 'pesadas_tbasculas';
    $sql = "DELETE FROM $tabla";
    $result = mysqli_query($conn, $sql);

    if($result){
        $response = array(
            "status" => "success",
            "message" => "Datos eliminados correctamente"
        );
    }else{
        $response = array(
            "status" => "error",
            "message" => "Error al eliminar los datos"
        );
    }

    return json_encode($response);
}