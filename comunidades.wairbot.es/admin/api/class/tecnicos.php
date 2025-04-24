<?php
 
class Tecnicos{
    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];

        //Buscar en tablas tecnicos_$codigoBot y tecnicos_auxiliares_$codigoBot
        
        //si codigoBot es vacio, se obtienen las tablas y se buscan por readFromMultipleTables si no se busca por read
        if($codigoBot == ""){
            //obtener el telefono por id_agente
            /*
            $sql = "SELECT telefono FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);
            
            if($result->num_rows == 0){
                return [
                    'status' => 'error',
                    'message' => 'No tienes técnicos asignados'
                ];
            }*/

            //$telefono = $result->fetch_assoc()['telefono'];
            
            //obtener los codigos de los bots del agente
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while($row = $result->fetch_assoc()){
                $codigos[] = $row['codigo_bot'];
            }

            $tablas = [];
            foreach($codigos as $codigo){
                $tablas[] = "tecnicos_" . $codigo;
                $tablas[] = "tecnicos_auxiliares_" . $codigo;
            }

            $filtro = [];
            $datosRelacionados = [];
            $columnasUnicas = ['telefono'];

            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search, $columnasUnicas);

            $telefonos = [];
            $data = [];
            foreach($result['data'] as $tecnico){
                if(!in_array($tecnico['telefono'], $telefonos)){
                    $telefonos[] = $tecnico['telefono'];
                    $data[] = $tecnico;
                }
            }

            $result['data'] = $data;

            //cambiar el id por el telefono
            foreach($result['data'] as $key => $tecnico){
                $result['data'][$key]['id'] = $tecnico['telefono'];
            }

            return [
                'status' => 'success',
                'message' => 'Técnicos obtenidos correctamente',
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

            $tabla = "tecnicos_" . $codigoBot;
            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            //añadir a result los tecnicos_auxiliares_$codigoBot
            $tabla = "tecnicos_auxiliares_" . $codigoBot;
            $resultAux = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            $result['data'] = array_merge($result['data'], $resultAux['data']);

            //eliminar de $result['data'] los técnicos con los números repetido
            $telefonos = [];
            $data = [];
            foreach($result['data'] as $tecnico){
                if(!in_array($tecnico['telefono'], $telefonos)){
                    $telefonos[] = $tecnico['telefono'];
                    $data[] = $tecnico;
                }
            }

            $result['data'] = $data;

            return [
                'status' => 'success',
                'message' => 'Técnicos obtenidos correctamente',
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
        $id_agente = $_SESSION['user_id'];
        $telefonoTecnico = $request['telefonoTecnico'];

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

        /*
        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }*/

        //obtener todods los bots del agente
        $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
        $result = $conn->query($sql);

        $codigos = [];
        while($row = $result->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        $comunidades = [];
        $tecnico = [];
        foreach($codigos as $codigo){
            $sql = "SELECT * FROM tecnicos_" . $codigo . " WHERE telefono = '$telefonoTecnico'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $tecnico = $row;
                $comunidades[] = [
                    'codigo_bot' => $codigo,
                    'activo' => true,
                    'esAuxiliar' => false
                ];
            }else{
                $sql = "SELECT * FROM tecnicos_auxiliares_" . $codigo . " WHERE telefono = '$telefonoTecnico'";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $row = $result->fetch_assoc();
                    $tecnico = $row;
                    $comunidades[] = [
                        'codigo_bot' => $codigo,
                        'activo' => true,
                        'esAuxiliar' => true
                    ];
                }else{
                    $comunidades[] = [
                        'codigo_bot' => $codigo,
                        'activo' => false,
                        'esAuxiliar' => false
                    ];
                }
            }
        }

        //si $tecnico['telefono'] no existe, está vacio o es null, obtenerlo por codigoBot e id
        if(!isset($tecnico['telefono']) || $tecnico['telefono'] == "" || $tecnico['telefono'] == null){
            $sql = "SELECT * FROM tecnicos_" . $codigoBot . " WHERE id = $id";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                $sql = "SELECT * FROM tecnicos_auxiliares_" . $codigoBot . " WHERE id = $id";
                $result = $conn->query($sql);
            }

            $tecnico = $result->fetch_assoc();
        }

        return [
            'status' => 'success',
            'message' => 'Técnico obtenido correctamente',
            'data' => [
                'nombre' => $tecnico['nombre'] ?? "",
                'telefono' => $tecnico['telefono'] ?? "",
                'tipo' => $tecnico['tipo'] ?? "",
                'actividad' => $tecnico['actividad'] ?? "",
                'referencia' => $tecnico['referencia'] ?? "",
                'comunidades' => $comunidades
            ]
        ];
    }

    static public function crear($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $codigoBot = $request['codigoBot'];
        $id_agente = $_SESSION['user_id'];

        $tipo = $data['tipo'];
        $actividad = $data['actividad'];
        $nombre = $data['nombre'];
        $referencia = $data['referencia'];

        $telefono = $data['telefono'];

        //eliminar del telefono cualquier caracter que no sea un número
        $telefono = preg_replace("/[^0-9]/", "", $telefono);

        //si telefono comienza por 6 o 7 añadir prefijo 34 
        if(preg_match("/^[6-7]/", $telefono)){
            $telefono = "34" . $telefono;
        }

        $sql = "SELECT id_agente, telefono FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $firstRow = $result->fetch_assoc();
        $telefonoAdmin = $firstRow['telefono'];
            
        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }
    

        //antes de crear el tecnico en wairbot comprobar si el técnico ya existe en la base de datos por el telefono
        $sql = "SELECT id FROM tecnicos_" . $codigoBot . " WHERE telefono = '$telefono'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            return [
                'status' => 'error',
                'message' => 'Ya existe un técnico con ese teléfono'
            ];
        }

        self::crearTecnicoWairbot($telefonoAdmin, $codigoBot, false, $tipo, $actividad, $nombre, $referencia, $telefono);

        //comprobar si el telefono del tecnico ya existe en la tabla tecnicos_$codigoBot y si no existe insertar
        $sql = "SELECT id FROM tecnicos_" . $codigoBot . " WHERE telefono = '$telefono'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            $sql = "INSERT INTO tecnicos_" . $codigoBot . " (tipo, actividad, nombre, referencia, telefono, referencia_usuario) VALUES ('$tipo', '$actividad', '$nombre', '$referencia', '$telefono', '$codigoBot')";
            $conn->query($sql);
        }

        return [
            'status' => 'success',
            'message' => 'Técnico creado correctamente'
        ];
    }

    static public function crearTecnicoWairbot($telefonoAdmin, $codigoBot, $esAuxiliar, $tipo, $actividad, $nombre, $referencia, $telefono){
        $api = $_SESSION['ulr_api'];
        $esAuxiliar = $esAuxiliar ? "2" : "1";

        $datos = [
            "key" => "PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU",
            "userPhone" => $telefonoAdmin,
            "adminPhone" => $codigoBot,
            "text" => "#1,$esAuxiliar,&$tipo&$actividad&$nombre&$referencia&$telefono"
        ];

        $options = [
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($datos),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    static public function asignarComunidades($request){
        $conn = $_SESSION['db_connection'];
        $comunidades = $request['dataComunidades'];
        $tecnico = $request['dataTecnico'];
        $telefonoTecnico = $request['telefonoTecnico'];

        $codigosActivos = [];
        $codigosInactivos = [];
        $codigosAuxiliares = [];
        $codigosNoAuxiliares = [];
        foreach($comunidades as $comunidad){
            if($comunidad['activo']){
                $codigosActivos[] = $comunidad['codigo_bot'];
            }else{
                $codigosInactivos[] = $comunidad['codigo_bot'];
            }

            if($comunidad['esAuxiliar']){
                $codigosAuxiliares[] = $comunidad['codigo_bot'];
            }else{
                $codigosNoAuxiliares[] = $comunidad['codigo_bot'];
            }
        }

        $response = "";
        foreach($codigosActivos as $codigoActivo){
            //comprobar si el telefono del tecnico ya existe en la tabla tecnicos_$codigoActivo
            $sql = "SELECT id FROM tecnicos_" . $codigoActivo . " WHERE telefono = '$telefonoTecnico'";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                //obtener el telefono del admin
                $sql = "SELECT telefono FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoActivo'";
                $result = $conn->query($sql);
                $telefonoAdmin = $result->fetch_assoc()['telefono'];

                $tipo = $tecnico['tipo'];
                $actividad = $tecnico['actividad'];
                $nombre = $tecnico['nombre'];
                $referencia = $tecnico['referencia'];
                $telefono = $tecnico['telefono'];

                $response = self::crearTecnicoWairbot($telefonoAdmin, $codigoActivo, false, $tipo, $actividad, $nombre, $referencia, $telefono);
                //convertir $response en string
                $response .= json_encode($response) . "\n";
            }
        }

        foreach($codigosInactivos as $codigoInactivo){
            //comprobar si el telefono del tecnico ya existe en la tabla tecnicos_$codigoInactivo
            $sql = "SELECT id FROM tecnicos_" . $codigoInactivo . " WHERE telefono = '$telefonoTecnico'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                //borrar de la tabla tecnicos_$codigoInactivo
                $sql = "DELETE FROM tecnicos_" . $codigoInactivo . " WHERE telefono = '$telefonoTecnico'";
                $conn->query($sql);

                //eliminar de la tabla form las filas donde la columna name_table tenga un valor que contenga _$telefonoTecnico_$codigoInactivo
                $sql = "DELETE FROM form WHERE name_table LIKE '%_" . $telefonoTecnico . "_" . $codigoInactivo . "'";
                $conn->query($sql);

                $response .= "Técnico eliminado de la comunidad " . $codigoInactivo . "\n";
            }
        }

        foreach($codigosAuxiliares as $codigoAuxiliar){
            //comprobar si el telefono del tecnico ya existe en la tabla tecnicos_auxiliares_$codigoAuxiliar
            $sql = "SELECT id FROM tecnicos_auxiliares_" . $codigoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                //eliminarlo de la tabla tecnicos_$codigoAuxiliar si existe y añadirlo a la tabla tecnicos_auxiliares_$codigoAuxiliar
                $sql = "SELECT * FROM tecnicos_" . $codigoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $tecnico = $result->fetch_assoc();
                    $nombre = $tecnico['nombre'];
                    $tipo = $tecnico['tipo'];
                    $actividad = $tecnico['actividad'];
                    $referencia = $tecnico['referencia'];

                    $sql = "INSERT INTO tecnicos_auxiliares_" . $codigoAuxiliar . " (nombre, telefono, tipo, actividad, referencia) VALUES ('$nombre', '$telefonoTecnico', '$tipo', '$actividad', '$referencia')";
                    $conn->query($sql);

                    //borrar de la tabla tecnicos_$codigoAuxiliar
                    $sql = "DELETE FROM tecnicos_" . $codigoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
                    $conn->query($sql);

                    $response .= "Técnico añadido a la comunidad " . $codigoAuxiliar . "\n Técnico eliminado de la comunidad " . $codigoAuxiliar . "\n";
                }
            }
        }

        foreach($codigosNoAuxiliares as $codigoNoAuxiliar){
            //comprobar si el telefono del tecnico ya existe en la tabla tecnicos_auxiliares_$codigoNoAuxiliar
            $sql = "SELECT id FROM tecnicos_auxiliares_" . $codigoNoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                //eliminarlo de la tabla tecnicos_auxiliares_$codigoNoAuxiliar si existe y añadirlo a la tabla tecnicos_$codigoNoAuxiliar
                $sql = "SELECT * FROM tecnicos_auxiliares_" . $codigoNoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $tecnico = $result->fetch_assoc();
                    $nombre = $tecnico['nombre'];
                    $tipo = $tecnico['tipo'];
                    $actividad = $tecnico['actividad'];
                    $referencia = $tecnico['referencia'];

                    $sql = "INSERT INTO tecnicos_" . $codigoNoAuxiliar . " (nombre, telefono, tipo, actividad, referencia) VALUES ('$nombre', '$telefonoTecnico', '$tipo', '$actividad', '$referencia')";
                    $conn->query($sql);

                    //borrar de la tabla tecnicos_auxiliares_$codigoNoAuxiliar
                    $sql = "DELETE FROM tecnicos_auxiliares_" . $codigoNoAuxiliar . " WHERE telefono = '$telefonoTecnico'";
                    $conn->query($sql);

                    $response .= "Técnico añadido a la comunidad " . $codigoNoAuxiliar . "\n Técnico eliminado de la comunidad " . $codigoNoAuxiliar . "\n";
                }
            }
        }

        return [
            'status' => 'success',
            'message' => 'Comunidades asignadas correctamente',
            'response' => $response
        ];
    }

    static public function borrar($request){
        //Borrar solo si no tiene incidencias abiertas
        $conn = $_SESSION['db_connection'];
        $codigoBot = $request['codigoBot'];
        $telefonoTecnico = $request['telefonoTecnico'];
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

        //comprobar si hay algún registro en la tabla incidencias_tecnico_$telefonoTecnico_$codigoBot
        $sql = "SELECT id FROM incidencias_tecnico_" . $telefonoTecnico . "_" . $codigoBot;
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            return [
                'status' => 'error',
                'message' => 'No se puede borrar el técnico porque tiene incidencias abiertas'
            ];
        }

        //obtener todos los bots del agente
        $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
        $result = $conn->query($sql);

        $codigos = [];
        while($row = $result->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        //borrar de todas las tablas tecnicos_$codigoBot y tecnicos_auxiliares_$codigoBot
        foreach($codigos as $codigo){
            //borrar de la tabla tecnicos_$codigoBot
            $sql = "DELETE FROM tecnicos_" . $codigo . " WHERE telefono = '$telefonoTecnico'";
            $conn->query($sql);

            //borrar de la tabla tecnicos_auxiliares_$codigoBot
            $sql = "DELETE FROM tecnicos_auxiliares_" . $codigo . " WHERE telefono = '$telefonoTecnico'";
            $conn->query($sql);

            //eliminar de la tabla form las filas donde la columna name_table tenga un valor que contenga _$telefonoTecnico_$codigo
            $sql = "DELETE FROM forms WHERE name_table LIKE '%_" . $telefonoTecnico . "_" . $codigo . "'";
            $conn->query($sql);
        }

        //obtener el nombre de las tablas que contenga _$telefonoTecnico_
        $sql = "SHOW TABLES LIKE '%_" . $telefonoTecnico . "_%'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $tablas = $result->fetch_all(MYSQLI_ASSOC);
        
            foreach($tablas as $tabla){
                // Obtener el nombre de la tabla sin conocer la clave exacta
                $nombreTabla = array_values($tabla)[0]; // Esto asume que cada $tabla tiene un solo elemento
                // Formar correctamente la consulta SQL para eliminar la tabla
                $sql = "DROP TABLE `" . $nombreTabla . "`"; // Asegúrate de usar comillas inversas (`) para nombres de tablas
                $conn->query($sql);
            }
        }

        return [
            'status' => 'success',
            'message' => 'Técnico borrado correctamente'
        ];
    }

    static public function listarTipos(){
        $conn = $_SESSION['db_connection'];
        
        // Obtener los nombres de las tablas que comiencen por "tecnicos_"
        $sqlTables = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE 'tecnicos_%'";
        $resultTables = $conn->query($sqlTables);
    
        $unionQueries = [];
        while($row = $resultTables->fetch_assoc()){
            $tableName = $row['table_name'];
            
            // Verificar si la columna 'tipo' existe en la tabla
            $sqlColumnCheck = "SELECT column_name FROM information_schema.columns WHERE table_name = '$tableName' AND column_name = 'tipo'";
            $columnCheckResult = $conn->query($sqlColumnCheck);
            
            if($columnCheckResult->num_rows > 0){
                $unionQueries[] = "SELECT tipo FROM $tableName";
            }
        }
    
        if (empty($unionQueries)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron tablas con la columna tipo',
                'data' => []
            ];
        }
    
        // Unir todas las consultas en una sola
        $sql = "SELECT DISTINCT tipo FROM (" . implode(" UNION ALL ", $unionQueries) . ") AS combined";
        
        $result = $conn->query($sql);
    
        $tipos = [];
        while($row = $result->fetch_assoc()){
            $tipos[] = $row['tipo'];
        }
    
        return [
            'status' => 'success',
            'message' => 'Tipos obtenidos correctamente',
            'data' => $tipos
        ];
    }

    //obtener los datos de un técnico por el telefono sin saber el codigoBot
    static public function verUnoPorTelefono($request){
        $conn = $_SESSION['db_connection'];
        $telefono = $request['telefono'];
        $id_agente = $_SESSION['user_id'];

        //obtener todos los bots del agente
        $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
        $result = $conn->query($sql);

        $codigos = [];
        while($row = $result->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        $tecnico = [];
        foreach($codigos as $codigo){
            $sql = "SELECT * FROM tecnicos_" . $codigo . " WHERE telefono = '$telefono'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $tecnico = $result->fetch_assoc();
                break;
            }

            $sql = "SELECT * FROM tecnicos_auxiliares_" . $codigo . " WHERE telefono = '$telefono'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $tecnico = $result->fetch_assoc();
                break;
            }
        }

        if(empty($tecnico)){
            return [
                'status' => 'error',
                'message' => 'No se encontró el técnico'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Técnico obtenido correctamente',
            'data' => $tecnico
        ];
    }
}