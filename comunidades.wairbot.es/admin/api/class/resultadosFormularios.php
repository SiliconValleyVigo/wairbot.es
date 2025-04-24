<?php
 
class ResultadosFormularios{
    static public function Listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];

        if($codigoBot != ""){
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

            //obtener un array con los nombres de las tablas de los formularios personalizados que no contengan filtro
            $queNoContenga = ["incidencia", "_in_tec_", "tecnico", "lugares"];
            $queContenga = [$codigoBot];

            $sql = "SELECT name_table FROM forms WHERE name_table NOT LIKE '%".implode("%' AND name_table NOT LIKE '%", $queNoContenga)."%' AND name_table LIKE '%".implode("%' AND name_table LIKE '%", $queContenga)."%'";
            $result = $conn->query($sql);

            $tablas = [];
            while($row = $result->fetch_assoc()){
                $tablas[] = $row['name_table'];
            }

            //comprobar si las tablas existen y si no existen devolver datos vacios
            if(count($tablas) == 0){
                return [
                    'status' => 'success',
                    'message' => 'Resultados obtenidos correctamente',
                    'data' => [
                        'paginaActual' => 1,
                        'paginasTotales' => 1,
                        'data' => []
                    ]
                ];
            }

            $filtro = [];
            $datosRelacionados = [];
            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Resultados obtenidos correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
                ]
            ];

        }else{
            //obtener un array de los codigos de los bots del agente
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while($row = $result->fetch_assoc()){
                $codigos[] = $row['codigo_bot'];
            }

            //si no hay codigos devolver un mensaje de error
            if(count($codigos) == 0){
                return [
                    'status' => 'success',
                    'message' => 'Resultados obtenidos correctamente',
                    'data' => [
                        'paginaActual' => 1,
                        'paginasTotales' => 1,
                        'data' => []
                    ]
                ];
            }

            //obtener un array con los nombres de las tablas de los formularios personalizados que no contengan filtro
            $queNoContenga = ["incidencia", "_in_tec_", "tecnico", "lugares"];
            $queContenga = $codigos;//es un array

            $sql = "SELECT name_table FROM forms WHERE name_table NOT LIKE '%".implode("%' AND name_table NOT LIKE '%", $queNoContenga)."%' AND name_table LIKE '%".implode("%' AND name_table LIKE '%", $queContenga)."%'";
            $result = $conn->query($sql);

            $tablas = [];
            while($row = $result->fetch_assoc()){
                $tablas[] = $row['name_table'];
            }

            $filtro = [];
            $datosRelacionados = [];
            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            //en result['data'] si existe alguna key llamada disponibilidad, sugerencias, res_a_ o res_b_ cambiar el nombre de la key por "dato"
            foreach($result['data'] as $key => $value){
                if(array_key_exists('disponibilidad', $value)){
                    $result['data'][$key]['dato'] = $value['disponibilidad'];
                    unset($result['data'][$key]['disponibilidad']);
                }

                if(array_key_exists('sugerencia', $value)){
                    $result['data'][$key]['dato'] = $value['sugerencia'];
                    unset($result['data'][$key]['sugerencia']);
                }

                if(array_key_exists('res_a_', $value)){
                    $result['data'][$key]['dato'] = $value['res_a_'];
                    unset($result['data'][$key]['res_a_']);
                }

                if(array_key_exists('res_b_', $value)){
                    $result['data'][$key]['dato'] = $value['res_b_'];
                    unset($result['data'][$key]['res_b_']);
                }
            }

            return [
                'status' => 'success',
                'message' => 'Resultados obtenidos correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
                ]
            ];
        }
    }

    static public function verUno($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $sourceTable = $request['sourceTable'];
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
        $result = Tools::getById($conn, $sourceTable, $id, $filtro, $datosRelacionados);

        //en result la key nombre_Usuario deben ser cambiadas por source_table
        $result['source_table'] = $result['nombre_Usuario'];
        unset($result['nombre_Usuario']);


        //en result las keys que sean disponibilidad, sugerencias, res_a_ o res_b_ cambiar el nombre de la key por "dato"
        if(array_key_exists('disponibilidad', $result)){
            $result['dato'] = $result['disponibilidad'];
            unset($result['disponibilidad']);
        }

        if(array_key_exists('sugerencia', $result)){
            $result['dato'] = $result['sugerencia'];
            unset($result['sugerencia']);
        }

        if(array_key_exists('res_a_', $result)){
            $result['dato'] = $result['res_a_'];
            unset($result['res_a_']);
        }

        if(array_key_exists('res_b_', $result)){
            $result['dato'] = $result['res_b_'];
            unset($result['res_b_']);
        }

        return [
            'status' => 'success',
            'message' => 'Resultado obtenido correctamente',
            'data' => $result
        ];
    }

    static public function Responder($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $sourceTable = $request['sourceTable'];
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

        $registro = [
            'tabla' => $sourceTable,
            'nombre' => $data['nombre'],
            'direccion' => $data['direccion'],
            'dato' => $data['dato'],
            'respuesta' => $data['respuesta'],
            'telefono' => $data['telefono_usuario']
        ];

        $nombreTablaHistorial = "historial_formularios_" . $codigoBot;

        $sql = "CREATE TABLE IF NOT EXISTS $nombreTablaHistorial (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, tabla VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, direccion VARCHAR(255) NOT NULL, telefono VARCHAR(255) NOT NULL, dato LONGTEXT NOT NULL, respuesta LONGTEXT NOT NULL, reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
        $conn->query($sql);

        //insertar registro en historial_formularios
        
        $sql = "INSERT INTO $nombreTablaHistorial (tabla, nombre, direccion, telefono, dato, respuesta) VALUES ('".$registro['tabla']."', '".$registro['nombre']."', '".$registro['direccion']."', '".$registro['telefono']."', '".$registro['dato']."', '".$registro['respuesta']."')";
        $conn->query($sql);

        //borrar registro de la tabla
        $sql = "DELETE FROM $sourceTable WHERE id = $id";
        $conn->query($sql);

        //enviar mensaje al usuario
        $userPhone = $data['telefono_usuario'];
        $adminPhone = $codigoBot;
        $message = $data['respuesta'];

        $enviarMensaje = self::enviarUnMensaje($adminPhone, $userPhone, $message);

        return [
            'status' => 'success',
            'message' => 'Se ha respondido correctamente'
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
    

    static public function Borrar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $sourceTable = $request['sourceTable'];
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

        //obtener el registro que se va a borrar
        $sql = "SELECT * FROM $sourceTable WHERE id = $id";
        $result = $conn->query($sql);
        $registro = $result->fetch_assoc();

        //eliminar de registro reg_date, id, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario
        unset($registro['reg_date']);
        unset($registro['id']);
        unset($registro['id_Usuario']);
        unset($registro['nombre_Usuario']);
        unset($registro['referencia_Usuario']);

        //la key que no sea nombre o direccion cambiar el nombre de la key a "dato"
        foreach($registro as $key => $value){
            if($key != "nombre" && $key != "direccion"){
                $registro['dato'] = "$key: $value";
                unset($registro[$key]);
            }
        }

        //crear tabla de historial si no existe con las columnas: id, tabla, nombre, direccion, telefono, dato, respuesta, reg_date
        $sql = "CREATE TABLE IF NOT EXISTS historial_formularios (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, tabla VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, direccion VARCHAR(255) NOT NULL, telefono VARCHAR(255) NOT NULL, dato LONGTEXT NOT NULL, respuesta LONGTEXT NOT NULL, reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
        $conn->query($sql);

        //insertar registro en historial_formularios
        $sql = "INSERT INTO historial_formularios (tabla, nombre, direccion, telefono, dato, respuesta) VALUES ('".$sourceTable."', '".$registro['nombre']."', '".$registro['direccion']."', '".$registro['telefono_usuario']."', '".$registro['dato']."', '')";
        $conn->query($sql);

        //borrar registro de la tabla
        $sql = "DELETE FROM $sourceTable WHERE id = $id";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Resultado eliminado correctamente'
        ];
    }
}

/*
BACKEND:
Clase: ResultadosFormularios
Metodos:
    - Listar($request)
        datos: nPagina, search, filtro

    - verUno($request)
        datos: id, codigoBotId, sourceTable

    - Responder($request)
        datos: id, codigoBotId, sourceTable, data
        data = {
            nombre: "",
            direccion: "",
            dato: "",
            respuesta: "",
            telefono_usuario: ""
        }

    - Borrar($request)
        datos: id, codigoBotId, sourceTable
*/