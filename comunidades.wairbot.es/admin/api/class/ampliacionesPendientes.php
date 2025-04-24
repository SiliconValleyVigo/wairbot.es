<?php
 
class AmpliacionesPendientes{
    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];

        //si codigoBot es vacio, se obtienen las tablas y se buscan por readFromMultipleTables si no se busca por read
        if($codigoBot == ""){
            //obtener los codigos de los bots del agente
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while($row = $result->fetch_assoc()){
                $codigos[] = $row['codigo_bot'];
            }

            $tablas = [];
            foreach($codigos as $codigo){
                $tablas[] = "incidencias_ampliadas_" . $codigo;
            }

            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            //eliminar de result['data'] de las key texto el string *SOLICITUD DE AMPLIACIÓN DE INCIDENCIA*
            foreach($result['data'] as $key => $value){
                $result['data'][$key]['texto'] = str_replace("*SOLICITUD DE AMPLIACIÓN DE INCIDENCIA* ", "", $value['texto']);
            }

            return [
                'status' => 'success',
                'message' => 'Ampliaciones pendientes obtenidas correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
                ]
            ];

        }else{
            //comprobar que el codigoBot pertenece al agente
            $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                return [
                    'status' => 'error',
                    'message' => 'El código no existe'
                ];
            }

            $ids_agentes_comunidad = [];
            while($row = $result->fetch_assoc()){
                $ids_agentes_comunidad[] = $row['id_agente'];
            }

            if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para ver estos lugares'
                ];
            }

            $tabla = "incidencias_ampliadas_" . $codigoBot;
            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            //eliminar de result['data'] de las key texto el string *SOLICITUD DE AMPLIACIÓN DE INCIDENCIA* 
            foreach($result['data'] as $key => $value){
                $result['data'][$key]['texto'] = str_replace("*SOLICITUD DE AMPLIACIÓN DE INCIDENCIA* ", "", $value['texto']);
            }   

            return [
                'status' => 'success',
                'message' => 'Ampliaciones pendientes obtenidas correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
                ]
            ];
        }
    }

    static public function verUno($request){
        //no es necesario crear la tabla
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $tabla = "incidencias_ampliadas_" . $codigoBot;
        $id_agente = $_SESSION['user_id'];

        //comprobar que el codigoBot pertenece al agente
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }

        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }

        $filtro = [];
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //obtener el técnico por el telefono en el texto
        $texto = $result['texto'];
        $telefonoTecnico = explode("#", $texto)[1];

        $tablaTecnico = "tecnicos_" . $codigoBot;
        $sql = "SELECT * FROM $tablaTecnico WHERE telefono = '$telefonoTecnico'";
        $resultTecnico = $conn->query($sql);

        $tecnico = $resultTecnico->fetch_assoc();

        $result['tecnico'] = $tecnico;

        return [
            'status' => 'success',
            'message' => 'Ampliación pendiente obtenida correctamente',
            'data' => $result
        ];
    }

    static public function aceptar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['data']['codigoBotId'];
        $tabla = "incidencias_ampliadas_" . $codigoBot;
        $id_agente = $_SESSION['user_id'];
        $data = $request['data'];

        //comprobar que el codigoBot pertenece al agente
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }

        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }

        //obtener de data['texto'] el telefono del técnico que está entre dos #
        $texto = $data['texto'];
        $telefonoTecnico = explode("#", $texto)[1];

        //obtener el técnico por el telefono en la tabla tecnicos_$codigoBot
        $tablaTecnico = "tecnicos_" . $codigoBot;
        $sql = "SELECT * FROM $tablaTecnico WHERE telefono = '$telefonoTecnico'";
        $result = $conn->query($sql);
        
        $tecnico = $result->fetch_assoc();

        $nombreTecnico = $tecnico['nombre'];
        $referenciaTecnico = $tecnico['referencia'];
        $tipoTecnico = $tecnico['tipo'];

        $registro = [
            'tabla' => "incidencias_ampliadas_" . $codigoBot,
            'nombre' => $nombreTecnico . " (" . $referenciaTecnico . ")" . " - " . $tipoTecnico,
            'direccion' => "",
            'dato' => $texto,
            'telefono' => $telefonoTecnico,
            'respuesta' => $texto
        ];

        $nombreTablaHistorial = "historial_formularios_" . $codigoBot;

        $sql = "CREATE TABLE IF NOT EXISTS $nombreTablaHistorial (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, tabla VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, direccion VARCHAR(255) NOT NULL, telefono VARCHAR(255) NOT NULL, dato LONGTEXT NOT NULL, respuesta LONGTEXT NOT NULL, reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
        $conn->query($sql);

        //insertar registro en historial_formularios
        
        $sql = "INSERT INTO $nombreTablaHistorial (tabla, nombre, direccion, telefono, dato, respuesta) VALUES ('".$registro['tabla']."', '".$registro['nombre']."', '".$registro['direccion']."', '".$registro['telefono']."', '".$registro['dato']."', '".$registro['respuesta']."')";
        $conn->query($sql);

        //borrar registro de la tabla
        $sql = "DELETE FROM $tabla WHERE id = $id";
        $conn->query($sql);

        //enviar mensaje al técnico
        $adminPhone = $codigoBot;
        $userPhone = $telefonoTecnico;
        $message = $texto;

        self::enviarUnMensaje($adminPhone, $userPhone, $message);

        return [
            'status' => 'success',
            'message' => 'Ampliación pendiente aceptada correctamente'
        ];
    }

    static public function enviarUnMensaje($adminPhone, $userPhone, $message){
        //comprobar si existe un chat con el usuario
        $conn = $_SESSION['db_connection'];

        //obtener el password del admin
        $sql = "SELECT * FROM admin_bot WHERE AD_numero_de_telefono_del_bot__tel = '$adminPhone'";
        $result = $conn->query($sql);
        $admin = $result->fetch_assoc();
        $adminPassword = $admin['AD_password'];

        $url = "https://wairbothook.wairbot.es/send";
        $data = [
            'userPhone' => $userPhone,
            'message' => $message,
            'file' => "",
            'codigoAdministradorBot' => $adminPhone,
            'passwordAdministradorBot' => $adminPassword
        ];
        $data = json_encode($data);
        
        //curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

/*
BACKEND:
Clase: AmpliacionesPendientes
Funciones:
- listar
     datos: nPagina, search, filtro
- verUna
        datos: id, codigoBotId
- aceptar
        datos: id, codigoBotId, data
        data: texto
*/
