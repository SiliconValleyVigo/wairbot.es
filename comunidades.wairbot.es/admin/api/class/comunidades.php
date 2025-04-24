<?php
 
class Comunidades{
    static public $tabla = 'bots_comunidades_wairbot_central';
    static public $tablaWairbot = 'admin_bot';

    static public function crear($request){
        $tabla = self::$tabla;
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $check = ['codigo_bot'];
        $email_agente = $_SESSION['user_email'];
        $id_agente = $_SESSION['user_id'];

        //obtener de la tabla usuarios_comunidades_wairbot_central la columna limite por el email del agente
        $sql = "SELECT limite FROM usuarios_comunidades_wairbot_central WHERE email = '$email_agente'";
        $result = $conn->query($sql);
        $limite = $result->fetch_assoc()['limite'];

        //comprobar si el agente ha llegado al limite de comunidades en la tabla bots_comunidades_wairbot_central por su id_agente
        $sql = "SELECT COUNT(*) as comunidades FROM $tabla WHERE id_agente = $id_agente";
        $result = $conn->query($sql);
        $comunidades = $result->fetch_assoc()['comunidades'];

        if($comunidades >= $limite){
            return [
                'status' => 'error',
                'message' => 'Has llegado al límite de comunidades'
            ];
        }

        //email es codigo_bot@codigo_bot.com y el pasword es un número único
        $data["email"] = $data["codigo_bot"] . '@' . $data["codigo_bot"] . '.com';
        $data["password"] = rand(100000, 999999);

        //comprobar que email y codigo_bot no existen en mayusculas ni minusculas
        $sql = "SELECT * FROM $tabla WHERE UPPER(email) = UPPER('" . $data["email"] . "') OR UPPER(codigo_bot) = UPPER('" . $data["codigo_bot"] . "')";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            return [
                'status' => 'error',
                'message' => 'El código ya están en uso, utiliza otro código diferente y otro email'
            ];
        }

        //crear en wairbot
        $dataWairbot = [
            "key" => "",
            "text" => "user-botComunidades",
            "user" => $data["email"],
            "password" => $data["password"],
            "telefonoAdministrador" => $data["telefono"],
            "codigoBot" => $data["codigo_bot"],
            "prefix" => $data["prefijo"],
            "lang" => $data["idioma"]
        ];

        //eliminar cualquier caracter no numerico del telefonoAdministrador
        $dataWairbot["telefonoAdministrador"] = preg_replace('/\D/', '', $dataWairbot["telefonoAdministrador"]);

        //comprobar si el telefonoAdministrador contiene el prefijo y si no, añadirlo
        if(substr($dataWairbot["telefonoAdministrador"], 0, strlen($dataWairbot["prefix"])) != $dataWairbot["prefix"]){
            $dataWairbot["telefonoAdministrador"] = $dataWairbot["prefix"] . $dataWairbot["telefonoAdministrador"];
        }

        ToolsWairbot::createbot($dataWairbot);

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Comunidad creada correctamente',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'El código ya están en uso',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la comunidad',
                'error' => $created
            ];
        }
    }

    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = self::$tabla;
        $id_agente = $_SESSION['user_id'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //sin filtro id_agente
        $filtro = [
            'id_agente' => $id_agente
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

        return [
            'status' => 'success',
            'message' => 'Comunidades obtenidas correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function verUno($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $id_agente = $_SESSION['user_id'];

        //sin filtro id_agente
        $filtro = [
            'id_agente' => $id_agente
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        $codigoBot = $result['codigo_bot'];

        //de la tabla bots_comunidades_wairbot_central obtener todos los codigos_bot por el id_agente
        $sql = "SELECT codigo_bot FROM $tabla WHERE id_agente = $id_agente";
        $queryResult = $conn->query($sql);

        $codigos = [];
        while($row = $queryResult->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        //Iterar para crear una consulta para obtener los datos id, tipo, nombre, telefono, referencia_usuario de todas las filas de las tablas tecnicos_$codigo y      tecnicos_auxiliares_$codigo sin repetir filas
        $sql = "";
        foreach($codigos as $codigo){
            $sql .= "SELECT id, tipo, nombre, telefono, referencia_usuario, 'tecnicos' AS origen FROM tecnicos_$codigo UNION SELECT id, tipo, nombre, telefono, referencia_usuario, 'tecnicos_auxiliares' AS origen FROM tecnicos_auxiliares_$codigo UNION ";
        }

        //eliminar el ultimo UNION
        $sql = substr($sql, 0, -7);

        $queryResult = $conn->query($sql);

        $tecnicos = [];
        while($row = $queryResult->fetch_assoc()){
            $tecnicos[] = $row;
        }

        //eliminar los técnicos con el mismo telefono, pero si referencia_usuario es igual a codigoBot no se elimina pero sí se elimina la anterior coincidencia
        $tecnicosFiltrados = [];
        $telefonos = [];
        foreach($tecnicos as $tecnico){
            $telefono = $tecnico['telefono'];
            $referencia_usuario = $tecnico['referencia_usuario'];
            if(in_array($telefono, $telefonos)){
                if($referencia_usuario == $codigoBot){
                    $tecnicosFiltrados = array_filter($tecnicosFiltrados, function($tecnico) use ($telefono){
                        return $tecnico['telefono'] != $telefono;
                    });
                    $tecnicosFiltrados[] = $tecnico;
                }
            }else{
                $tecnicosFiltrados[] = $tecnico;
                $telefonos[] = $telefono;
            }
        }

        //si referencia_usuario es igual a codigoBot, activo es true, si no, es false
        foreach($tecnicosFiltrados as $tecnicoFiltrado){
            $referencia_usuario = $tecnicoFiltrado['referencia_usuario'];
            if($referencia_usuario == $codigoBot){
                $tecnicoFiltrado['activo'] = true;
            }else{
                $tecnicoFiltrado['activo'] = false;
            }
        }

        $result['tecnicos'] = $tecnicosFiltrados;

        return [
            'status' => 'success',
            'message' => 'Comunidad obtenida correctamente',
            'data' => $result
        ];
    }

    static public function eliminar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];
        $id_agente = $_SESSION['user_id'];

        //confirmar que el id pertenece al agente
        $sql = "SELECT id_agente FROM $tabla WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'La comunidad no existe'
            ];
        }

        //obtener el $adminPhone por el $id en la tabla bots_comunidades_wairbot_central
        $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id = $id";
        $result = $conn->query($sql);
        $adminPhone = $result->fetch_assoc()['codigo_bot'];

        $check = [];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        ToolsWairbot::deleteBot($adminPhone);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Comunidad eliminada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la comunidad',
                'error' => $deleted
            ];
        }
    }

    static public function modificar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $data = $request['data'];
        $id = $request['id'];
        $id_agente = $_SESSION['user_id'];

        //confirmar que el id pertenece al agente
        $sql = "SELECT id_agente FROM $tabla WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'La comunidad no existe'
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

        //solo se puede modificar el tiempo y el telefono
        $data = [
            'tiempo' => $data['tiempo'],
            'telefono' => $data['telefono']
        ];

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Comunidad modificada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al modificar la comunidad',
                'error' => $updated
            ];
        }
    }

    static public function getArrayCodigosComunidades(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;

        if(!array_key_exists('user_id', $_SESSION)){
            return [];
        }

        $id_agente = $_SESSION['user_id'];

        if($id_agente == null){
            return [];
        }

        $sql = "SELECT codigo_bot FROM $tabla WHERE id_agente = $id_agente";
        $result = $conn->query($sql);

        $codigos = [];
        while($row = $result->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        return $codigos;
    }

    /*
        Tecnicos::borrar
            -codigoBot
            -telefonoTecnico

        Tecnicos::crear
            -codigoBot
            -data
                -tipo
                -actividad
                -nombre
                -referencia (codigoBot)
                -telefono

        Tecnicos::verUnoPorTelefono
            - telefono

        {
            "telefono": "34605276063",
            "esAuxiliar": false,
            "activo": false
        }
    */
    static public function asignarTecnicosComunidad($request){
        $conn = $_SESSION['db_connection'];
        $codigoBot = $request['codigo_bot'];
        $tecnicos = $request['dataComunidades'];

        foreach($tecnicos as $tecnico){
            $telefono = $tecnico['telefono'];
            $esAuxiliar = $tecnico['esAuxiliar'];
            $activo = $tecnico['activo'];

            $tabla = $esAuxiliar ? 'tecnicos_auxiliares_' : 'tecnicos_';

            //si activo es true se comprueba si el telefono ya existe en la tabla tecnicos_$codigoBot y si no se crea
            if($activo){
                $sql = "SELECT * FROM $tabla$codigoBot WHERE telefono = '$telefono'";
                $result = $conn->query($sql);

                //si no existe buscar en la otra tabla y si está en la otra tabla, insertar en la tabla tecnicos_$codigoBot y eliminar de la otra tabla
                if($result->num_rows == 0){
                    $otraTabla = $esAuxiliar ? 'tecnicos_' : 'tecnicos_auxiliares_';

                    $sql = "SELECT * FROM $otraTabla$codigoBot WHERE telefono = '$telefono'";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $datosTecnico = $result->fetch_assoc();

                        //eliminar el id del array
                        unset($datosTecnico['id']);

                        //insertar los datos en la tabla tecnicos_$codigoBot: tipo, actividad, nombre, referencia, telefono, referencia_Usuario(codigo_bot)
                        $sql = "INSERT INTO $tabla$codigoBot (tipo, actividad, nombre, referencia, telefono, referencia_usuario) VALUES ('" . $datosTecnico['tipo'] . "', '" . $datosTecnico['actividad'] . "', '" . $datosTecnico['nombre'] . "', '" . $datosTecnico['referencia'] . "', '" . $datosTecnico['telefono'] . "', '$codigoBot')";
                        $conn->query($sql);

                        //eliminar los datos de la tabla tecnicos_auxiliares_$codigoBot
                        $sql = "DELETE FROM $otraTabla$codigoBot WHERE telefono = '$telefono'";
                        $conn->query($sql);
                    }
                }

                if($result->num_rows == 0){
                    //obtener los datos del técnico por su telefono
                    $dataQuery = ['telefono' => $telefono];
                    $tecnicoData = Tecnicos::verUnoPorTelefono($dataQuery);

                    $tecnicoData = $tecnicoData['data'];

                    $data = [
                        'tipo' => $tecnicoData['tipo'],
                        'actividad' => $tecnicoData['actividad'],
                        'nombre' => $tecnicoData['nombre'],
                        'referencia' => $codigoBot,
                        'telefono' => $telefono
                    ];

                    $dataQuery = [
                        'codigoBot' => $codigoBot,
                        'data' => $data
                    ];

                    $created = Tecnicos::crear($dataQuery);

                    //añadir a la tabla si no existe
                    $sql = "SELECT * FROM tecnicos_$codigoBot WHERE telefono = '$telefono'";
                    $result = $conn->query($sql);

                    if($result->num_rows == 0){
                        $sql = "INSERT INTO tecnicos_$codigoBot (tipo, actividad, nombre, referencia, telefono, referencia_usuario) VALUES ('" . $data['tipo'] . "', '" . $data['actividad'] . "', '" . $data['nombre'] . "', '" . $data['referencia'] . "', '" . $data['telefono'] . "', '$codigoBot')";
                        $conn->query($sql);
                    }

                    if($created['status'] == 'error'){
                        return [
                            'status' => 'error',
                            'message' => 'Error al asignar técnico',
                            'error' => $created
                        ];
                    }

                    //si es auxiliar se obtienen los datos del técnico de la tabla tecnicos_$codigoBot, se insertan en la tabla tecnicos_auxiliares_$codigoBot y se eliminan de la tabla tecnicos_$codigoBot
                    if($esAuxiliar){
                        $sql = "SELECT * FROM tecnicos_$codigoBot WHERE telefono = '$telefono'";
                        $result = $conn->query($sql);

                        if($result->num_rows > 0){
                            $datosTecnico = $result->fetch_assoc();

                            //eliminar el id del array
                            unset($datosTecnico['id']);

                            //insertar los datos en la tabla tecnicos_auxiliares_$codigoBot: tipo, actividad, nombre, referencia, telefono, referencia_Usuario(codigo_bot)
                            $sql = "INSERT INTO tecnicos_auxiliares_$codigoBot (tipo, actividad, nombre, referencia, telefono, referencia_usuario) VALUES ('" . $datosTecnico['tipo'] . "', '" . $datosTecnico['actividad'] . "', '" . $datosTecnico['nombre'] . "', '" . $datosTecnico['referencia'] . "', '" . $datosTecnico['telefono'] . "', '$codigoBot')";
                            $conn->query($sql);

                            //eliminar los datos de la tabla tecnicos_$codigoBot
                            $sql = "DELETE FROM tecnicos_$codigoBot WHERE telefono = '$telefono'";
                            $conn->query($sql);
                        }
                    }
                }
            }else{
                //si activo es false se comprueba si el telefono existe en la tabla tecnicos_$codigoBot y si existe se elimina
                $sql = "SELECT * FROM $tabla$codigoBot WHERE telefono = '$telefono'";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $sql = "DELETE FROM $tabla$codigoBot WHERE telefono = '$telefono'";
                    $conn->query($sql);
                }
            }
        }

        return [
            'status' => 'success',
            'message' => 'Técnicos actualizados correctamente'
        ];
    }

    /*
    CREATE TABLE mensajes_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        userPhone VARCHAR(255) NULL,
        codigoAdmin VARCHAR(255) NULL,
        mensaje LONGTEXT NULL,
        log LONGTEXT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    */
    //obtener los logs de una comunidad
    static public function obtenerLogs($request){
        $conn = $_SESSION['db_connection'];
        $codigoBot = $request['codigo_bot'];

        //obtener timestamp, userPhone y log de la tabla mensajes_logs por el codigoAdmin
        $sql = "SELECT timestamp, userPhone, log FROM mensajes_logs WHERE codigoAdmin = '$codigoBot' ORDER BY timestamp DESC";
        $result = $conn->query($sql);

        $logs = [];
        while($row = $result->fetch_assoc()){
            $logs[] = $row;
        }

        //poner los logs en formato tabla html con una cabecera con fecha, telefono y estado
        $html = "<style>
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                    }
                    thead {
                        background-color: #f2f2f2;
                    }
                 </style>";
        $html .= "<table>";
        $html .= "<thead><tr><th>Fecha</th><th>Teléfono</th><th>Estado</th></tr></thead><tbody>";
        foreach($logs as $log){
            $html .= "<tr><td>" . $log['timestamp'] . "</td><td>" . $log['userPhone'] . "</td><td>" . $log['log'] . "</td></tr>";
        }
        $html .= "</tbody></table>";

        return [
            'status' => 'success',
            'message' => 'Logs obtenidos correctamente',
            'data' => $html
        ];
    }

    ///////////////////////////////////////////////////////////////////////////
    //////////// ENVIAR A TECNICO AUXILIAR SI TECONICO NO RESPONDE ////////////
    ///////////////////////////////////////////////////////////////////////////
/*
    TABLAS:
    bots_comunidades_wairbot_central: id, id_agente, email, password, prefijo, telefono, codigo_bot, tiempo, idioma, reg_date
    
    tecnicos_$codigoBot: id, tipo, actividad, nombre, referencia, telefono, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date
    
    tecnicos_auxiliares_$codigoBot: id, tipo, actividad, nombre, referencia, telefono, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date
    
    incidencias_$codigoBot: id, lugar, incidencia_abierta_plugin_ComunidadesSelectorIncidenciasAbiertas, descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas, tipo, imagen, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date, codigo, usuario, tecnico
*/

    static public function enviarTecnicoAuxiliar($request){
        $logs = [];
        $conn = $_SESSION['db_connection'];
    
        //obtener todos los códigos de las comunidades y tiempo
        $sql = "SELECT codigo_bot, telefono, tiempo FROM bots_comunidades_wairbot_central";
        $result = $conn->query($sql);
    
        $comunidades = [];
        while($row = $result->fetch_assoc()){
            $comunidades[] = $row;
        }
    
        //obtener las incidencias de cada comunidad, analizar si el tiempo ha pasado y si el técnico no ha respondido, enviar al técnico auxiliar del   mismo tipo si existe
        foreach($comunidades as $comunidad){
            $logs[] = "Comunidad: " . json_encode($comunidad);
            $codigoBot = $comunidad['codigo_bot'];
            $tiempo = $comunidad['tiempo'];
        
            //obtener las incidencias de la comunidad
            $sql = "SELECT * FROM incidencias_$codigoBot WHERE tecnico IS NULL";
            try {
                $result = $conn->query($sql);
            } catch (mysqli_sql_exception $e) {
                if (strpos($e->getMessage(), "Unknown column 'tecnico'") !== false) {
                    // Agregar la columna 'tecnico' si no existe
                    $sql = "ALTER TABLE incidencias_$codigoBot ADD COLUMN tecnico VARCHAR(255)";
                    $conn->query($sql);
                    // Volver a ejecutar la consulta
                    $result = $conn->query("SELECT * FROM incidencias_$codigoBot WHERE tecnico IS NULL");
                } else {
                    throw $e;
                }
            }
        
            $incidencias = [];
            while($row = $result->fetch_assoc()){
                $incidencias[] = $row;
            }
        
            //si alguna incidencia ha pasado el tiempo y no tiene tecnico asignado, asignar al tecnico auxiliar del mismo tipo si existe
            foreach($incidencias as $incidencia){
                $logs[] = "Incidencia: " . json_encode($incidencia);

                $timestamp = $incidencia['reg_date'];
                $now = new DateTime();
                $timestamp = new DateTime($timestamp);
                $diff = $now->diff($timestamp);

                $totalMinutes = ($diff->h * 60) + $diff->i; // Convertir horas y minutos a minutos totales
                $requiredMinutes = $tiempo * 60; // Convertir el tiempo requerido a minutos
                //$requiredMinutes = 2;//PARA PRUEBAS
            
                $logs[] = "DIFFM: " . $totalMinutes;
                $logs[] = "Tiempo en minutos: $requiredMinutes";
                $logs[] = "Comparación: " . ($totalMinutes >= $requiredMinutes);
            
                if($totalMinutes >= $requiredMinutes){
                    $tipo = $incidencia['tipo'];
                    $tipo = explode("(", $tipo)[0];
                    $tipo = trim($tipo);
                
                    $tecnico = $incidencia['tecnico'];
                    if($tecnico == null){
                        //obtener el número de técnicos auxiliares del mismo tipo
                        $sql = "SELECT COUNT(*) as count FROM tecnicos_auxiliares_$codigoBot WHERE tipo = '$tipo'";
                        $logs[] = "Ejecutando consulta 3: $sql";
                        $result = $conn->query($sql);
                        $count = $result->fetch_assoc()['count'];
                        $logs[] = "Resultado de la consulta 3: " . json_encode(['count' => $count]);
                        $logs[] = "Cantidad de técnicos auxiliares: $count";

                        $logs[] = "Id de la incidencia: " . $incidencia['id'];
                    
                        if($count > 0){
                            $logs[] = "Asignando técnico auxiliar......";
                            $comprobadoUsuarioIncidencia = $incidencia['comprobado_Usuario'];
                        
                            $logs[] = "Comprobado_Usuario: $comprobadoUsuarioIncidencia";
                            $logs[] = "Count: $count";
                        
                            if($comprobadoUsuarioIncidencia == null || $comprobadoUsuarioIncidencia == 0 || $comprobadoUsuarioIncidencia == ""){
                                $logs[] = "Comprobado_Usuario es null o 0, asignando a 1";
                                $comprobadoUsuarioIncidencia = 0;
                            }
                        
                            if($comprobadoUsuarioIncidencia == $count){
                                $logs[] = "Comprobado_Usuario es igual a $count, saliendo del proceso XXXXXXXX";
                                continue;
                            }
                        
                            if($comprobadoUsuarioIncidencia < $count){
                                $logs[] = "Comprobado_Usuario es menor que $count, asignando a " . $comprobadoUsuarioIncidencia;
                                $comprobadoUsuarioIncidencia++;
                            }

                            $logs[] = "Id de la incidencia: " . $incidencia['id'];
                        
                            $sql = "SELECT * FROM tecnicos_auxiliares_$codigoBot WHERE tipo = '$tipo'";
                            $logs[] = "Ejecutando consulta 4: $sql";
                            $result = $conn->query($sql);
                        
                            $tecnicosAuxiliares = [];
                            while($row = $result->fetch_assoc()){
                                $tecnicosAuxiliares[] = $row;
                            }
                            $logs[] = "Resultado de la consulta 4: " . json_encode($tecnicosAuxiliares);
                        
                            $tecnicoAuxiliar = $tecnicosAuxiliares[$comprobadoUsuarioIncidencia-1];
                        
                            $telefonoTecnicoAuxiliar = $tecnicoAuxiliar['telefono'];
                            $nombreTecnicoAuxiliar = $tecnicoAuxiliar['nombre'];
                        
                            $lugar = $incidencia['lugar'];
                            $tipo = $incidencia['tipo'];
                            $descripcion = $incidencia['descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas'];
                            $contactoUsuario = $incidencia['usuario'];
                            $url = $incidencia['imagen'];
                            $codigoIncidencia = $incidencia['codigo'];
                        
                            if(substr($url, -4) != '.jpg'){
                                $url = "";
                            }
                        
                            $adminPhone = $codigoBot;
                            $telefonoAdmin = $comunidad['telefono'];
                        
                            $sql = "SELECT * FROM admin_bot WHERE AD_numero_de_telefono_del_bot__tel = '$adminPhone'";
                            $logs[] = "Ejecutando consulta 5: $sql";
                            $result = $conn->query($sql);
                            $admin = $result->fetch_assoc();
                            $adminPassword = $admin['AD_password_'];
                            $logs[] = "Resultado de la consulta 5: " . json_encode($admin);

                            //INSERTAR INCIDENCIA CON crearIncidenciaEnTecnicoAuxiliar
                            self::crearIncidenciaEnTecnicoAuxiliar($adminPhone, $lugar, $tipo, $descripcion, $url, $telefonoTecnicoAuxiliar, $codigoIncidencia, $contactoUsuario);
                        
                            $resumen = "Lugar: $lugar\nTipo: $tipo\nDescripción: $descripcion\nContacto: $contactoUsuario\nTecnico:     $nombreTecnicoAuxiliar  ($telefonoTecnicoAuxiliar)";
                        
                            $mensajeAdmin = "Incidencia asignada a técnico auxiliar $nombreTecnicoAuxiliar ($telefonoTecnicoAuxiliar)\n$resumen";
                            self::sendMessageWhatsapp($telefonoAdmin, $mensajeAdmin, $url, $adminPhone, $adminPassword);
                        
                            $mensajeTecnicoAuxiliar = "Se ha asignado una nueva incidencia:\n$resumen\n---------\n";
                            $mensajeTecnicoAuxiliar .= "\n¿Desea aceptar la incidencia? Escribe el número asociado a tu respuesta:\n\n*1.* Aceptar\n*2.* Rechazar";
                            self::sendMessageWhatsapp($telefonoTecnicoAuxiliar, $mensajeTecnicoAuxiliar, $url, $adminPhone, $adminPassword);
                        
                            $name_opcion = "aceptar_in_tec_$telefonoTecnicoAuxiliar";
                        
                            $incidenciasTecnicoAuxiliar = self::getIncidenciasTecnico($adminPhone, $telefonoTecnicoAuxiliar);

                            $id_incidencia = $incidencia['id'];
                        
                            $posicion = count($incidenciasTecnicoAuxiliar);

                            //convertir a string
                            $posicion = strval($posicion);

                            $respuestas = [$posicion];

                            $logs[] = "Datos para Iniciar Formulario: " . json_encode([$codigoBot, $telefonoTecnicoAuxiliar, $name_opcion, $respuestas]);
                        
                            $iniciarFormulario = self::iniciarFormulario($codigoBot, $telefonoTecnicoAuxiliar, $name_opcion, $respuestas);
                            $logs[] = "Resultado de iniciarFormulario: " . json_encode($iniciarFormulario);
                            //iniciarFormulario($telefonoAdmin, $telefonoTecnicoAuxiliar, $name_opcion, $respuestas)
                        
                            $sql = "UPDATE incidencias_$codigoBot SET comprobado_Usuario = $comprobadoUsuarioIncidencia WHERE id = " . $id_incidencia;
                            $logs[] = "Ejecutando consulta 6: $sql";
                            $conn->query($sql);

                            //insertar una acción en la tabla incidencias_ampliadas_... indicando a que técnico se le ha asignado la incidencia
                            $textoAmpliacion = "Se ha asignado la incidencia al técnico Auxiliar: " . $nombreTecnicoAuxiliar . " con teléfono: " . $telefonoTecnicoAuxiliar;
                            $logs[] = "Id de la incidencia: " . $incidencia['id'];
                            $sql = "INSERT INTO incidencias_ampliadas_$adminPhone (codigo, texto) VALUES ('$codigoIncidencia', '$textoAmpliacion')";
                            $logs[] = "Ejecutando consulta 7: $sql";
                            $conn->query($sql);
                        }
                    }
                } else {
                    $logs[] = "Incidencia ya tiene técnico asignado";
                }
            }
        }
    
        return [
            'status' => 'success',
            'message' => 'Técnicos auxiliares asignados correctamente',
            'logs' => $logs
        ];
    }

    /*
    "Datos para Iniciar Formulario: [\"34600857882\",\"34699694471\",\"aceptar_in_tec_34699694471\",[2]]"
    "Datos para Iniciar Formulario: [\"testcomunigal\",\"34699694471\",\"aceptar_in_tec_34699694471\",[\"8\"]]"

    "Resultado de iniciarFormulario: {\"status\":\"success\",\"message\":\"Formulario iniciado correctamente\",\"log\":{\"text\":\"user-exp\",\"expClass\":\"Puentes\",\"expFunction\":\"iniciarFormularioQuery\",\"admin_phone\":\"34600857882\",\"adminPhone\":\"34600857882\",\"userPhone\":\"34699694471\",\"name_opcion\":\"aceptar_in_tec_34699694471\",\"respuestas\":[\"8\"],\"print\":true,\"key\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2ODc2OTAyMzAsImRhdGEiOlt7IkFEX2lkXyI6IjEiLCJBRF9lbWFpbF8iOiJzdXNvbWFjaWFzQGhvdG1haWwuY29tIiwiQURfcGFzc3dvcmRfIjoiMTVlMmIwZDNjMzM4OTFlYmIwZjFlZjYwOWVjNDE5NDIwYzIwZTMyMGNlOTRjNjVmYmM4YzMzMTI0NDhlYjIyNSIsIkFEX3RpcG9fc2VydmljaW9fIjoiMSIsIkFEX3ZhbGlkYXRlZF8iOiIxIiwiQURfbm9tYnJlX2RlbF9zZXJ2aWNpb19fdGV4dCI6IkNvbWFuZGFzXG4iLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fZGVsX2JvdF9fdGVsIjoiNjUxNjU0MTY4NTQiLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fY29uX3Blcm1pc29fZGVfYWRtaW5pc3RyYWRvcl9fdGV4dCI6bnVsbCwiQURfcHJlZmlqb19pbnRlcm5hY2lvbmFsX19udW1iZXIiOm51bGwsInRpbWVzdGFtcCI6IjIwMjMtMDYtMjUgMTI6NDk6NDgifV19.LQHkRB_KkgjutZMy21e48DLg-aPXSlOvm18fkSQqaHQ\",\"token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2ODc2OTAyMzAsImRhdGEiOlt7IkFEX2lkXyI6IjEiLCJBRF9lbWFpbF8iOiJzdXNvbWFjaWFzQGhvdG1haWwuY29tIiwiQURfcGFzc3dvcmRfIjoiMTVlMmIwZDNjMzM4OTFlYmIwZjFlZjYwOWVjNDE5NDIwYzIwZTMyMGNlOTRjNjVmYmM4YzMzMTI0NDhlYjIyNSIsIkFEX3RpcG9fc2VydmljaW9fIjoiMSIsIkFEX3ZhbGlkYXRlZF8iOiIxIiwiQURfbm9tYnJlX2RlbF9zZXJ2aWNpb19fdGV4dCI6IkNvbWFuZGFzXG4iLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fZGVsX2JvdF9fdGVsIjoiNjUxNjU0MTY4NTQiLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fY29uX3Blcm1pc29fZGVfYWRtaW5pc3RyYWRvcl9fdGV4dCI6bnVsbCwiQURfcHJlZmlqb19pbnRlcm5hY2lvbmFsX19udW1iZXIiOm51bGwsInRpbWVzdGFtcCI6IjIwMjMtMDYtMjUgMTI6NDk6NDgifV19.LQHkRB_KkgjutZMy21e48DLg-aPXSlOvm18fkSQqaHQ\"},\"data\":{\"status\":\"success\",\"message\":\"Formulario iniciado correctamente.\",\"data\":null}}"

    */

    public static function crearIncidenciaEnTecnicoAuxiliar($adminPhone, $lugar, $tipo, $descripcion, $imagen, $telefonoDelTecnicoAuxiliar, $unico, $userPhone){
        $telefonoDelTecnico = $telefonoDelTecnicoAuxiliar;

        $conn = $_SESSION['db_connection'];
        $sql = "SHOW TABLES LIKE 'incidencias_tecnico_$telefonoDelTecnico%'";
        $result = $conn->query($sql);

        //crear una consulta para insertar en todas las tablas
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $nombreTabla = $row["Tables_in_wairbot_datos (incidencias_tecnico_$telefonoDelTecnico%)"];
                $sql = "INSERT INTO $nombreTabla (codigo, lugar, tipo, descripcion, imagen, usuario) VALUES ('$unico','$lugar', '$tipo', '$descripcion', '$imagen', '$userPhone')";
                $conn->query($sql);
            }
        }

        self::actualizarSelectores($adminPhone);
    }

    static private function iniciarFormulario($telefonoAdmin, $telefonoTecnicoAuxiliar, $name_opcion, $respuestas){
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2ODc2OTAyMzAsImRhdGEiOlt7IkFEX2lkXyI6IjEiLCJBRF9lbWFpbF8iOiJzdXNvbWFjaWFzQGhvdG1haWwuY29tIiwiQURfcGFzc3dvcmRfIjoiMTVlMmIwZDNjMzM4OTFlYmIwZjFlZjYwOWVjNDE5NDIwYzIwZTMyMGNlOTRjNjVmYmM4YzMzMTI0NDhlYjIyNSIsIkFEX3RpcG9fc2VydmljaW9fIjoiMSIsIkFEX3ZhbGlkYXRlZF8iOiIxIiwiQURfbm9tYnJlX2RlbF9zZXJ2aWNpb19fdGV4dCI6IkNvbWFuZGFzXG4iLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fZGVsX2JvdF9fdGVsIjoiNjUxNjU0MTY4NTQiLCJBRF9udW1lcm9fZGVfdGVsZWZvbm9fY29uX3Blcm1pc29fZGVfYWRtaW5pc3RyYWRvcl9fdGV4dCI6bnVsbCwiQURfcHJlZmlqb19pbnRlcm5hY2lvbmFsX19udW1iZXIiOm51bGwsInRpbWVzdGFtcCI6IjIwMjMtMDYtMjUgMTI6NDk6NDgifV19.LQHkRB_KkgjutZMy21e48DLg-aPXSlOvm18fkSQqaHQ";
        $data = [
            "text" => "user-exp",
            "expClass" => "Puentes",
            "expFunction" => "iniciarFormularioQuery",
            "admin_phone" => $telefonoAdmin,
            "adminPhone" => $telefonoAdmin,
            "userPhone" => $telefonoTecnicoAuxiliar,
            "name_opcion" => $name_opcion,
            "respuestas" => $respuestas,
            "print" => true,
            "key" => $token,
            "token" => $token 
        ];

        $url = "https://gestion-datos.wairbot.com/bot-datos/";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return [
            'status' => 'success',
            'message' => 'Formulario iniciado correctamente',
            'log' => $data,
            'data' => json_decode($result, true)
        ];
    }
    //6799e3d0f2543

    static private function getIncidenciasTecnico($adminPhone, $telefonoTecnico){
        $nombreTabla = "incidencias_tecnico_$telefonoTecnico" . "_$adminPhone";

        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM $nombreTabla";
        $result = $conn->query($sql);

        $incidencias = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()) {
                $incidencias[] = $row;
            }
        }

        return $incidencias;
    }

    static private function sendMessageWhatsapp($userPhone, $message, $file, $codigoAdministradorBot, $passwordAdministradorBot){
        $url = "https://wairbothook.wairbot.es/send";
        $data = [
            'userPhone' => $userPhone,
            'message' => $message,
            'file' => $file,
            'codigoAdministradorBot' => $codigoAdministradorBot,
            'passwordAdministradorBot' => $passwordAdministradorBot,
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

    public static function actualizarSelectores($adminPhone){
        $conn = $_SESSION['db_connection'];

        //Obtener formularios de la DB
        $condicionesFormularios = [
            'admin_phone' => $adminPhone,
        ];
        $resultado = self::obtenerDatos($conn, 'forms', $condicionesFormularios);
        $formularios = isset($resultado['datos']) && is_array($resultado['datos']) ? $resultado['datos'] : [];

        foreach($formularios as $formulario){
            $fields = json_decode($formulario['fields'], true);
            $id = $formulario['id'];

            if(!is_array($fields)){
                $fields = []; // Asegurarse de que $fields es un array
            }

            foreach($fields as &$field){
                if(strpos($field['type'], '::isTabla') !== false){
                    $type = explode('::', $field['type']);
                    $label = $field['label'];

                    $tablaName = $type[1] . '_' . $adminPhone;

                    //obtener los datos de la tabla
                    $datosTabla = self::obtenerDatos($conn, $tablaName, [])['datos'];

                    $newLabel = "[SELECTOR]: ";
                    foreach($datosTabla as $dato){
                        //obtener el valor de la posicon 1 del array
                        $idValor = array_values($dato)[0];
                        $valor1 = array_values($dato)[1];
                        $valor2 = array_values($dato)[2];

                        //si valor2 es null, o vacio, o 0, o false, o '0' entonces mostrar solo valor1 con el id
                        if($valor2 === null || $valor2 === '' || $valor2 === 0 || $valor2 === false || $valor2 === '0'){
                            $newLabel .= "$valor1 ($idValor),";
                            continue;
                        }

                        $newLabel .= "$valor1 ($valor2),";
                    }

                    //eliminar la ultima coma
                    $newLabel = substr($newLabel, 0, -1);

                    if($label !== $newLabel){
                        //añadir $newLabel a $fields con la key newLabel
                        $field['label'] = $newLabel;

                        //actualizar el campo fields del formulario
                        $fieldsJson = json_encode($fields);

                        $actualizar = self::actualizarFilas($conn, [
                            [
                                'tabla' => 'forms',
                                'datos' => [
                                    'fields' => $fieldsJson,
                                ],
                                'condicion' => [
                                    'id' => $id,
                                ],
                            ]
                        ]);

                        if($actualizar['status'] != true){
                            echo json_encode($actualizar);
                            exit();
                        }
                    }
                }
            }
            unset($field);
        }
    }

    static public function obtenerDatos($conn, $tabla, $condiciones) {
        $mensaje = "";
        $status = "";
        $codigoHttp = "";
        $datos = [];
    
        // Construir la cláusula WHERE con las condiciones
        $where = "";
        foreach ($condiciones as $columna => $valor) {
            $valor = mysqli_real_escape_string($conn, $valor); // Prevenir inyección SQL
            $where .= $columna . " = '" . $valor . "' AND ";
        }
        $where = rtrim($where, " AND "); // Eliminar el último "AND" redundante
    
        // Consultar los datos de la base de datos
        $query = "SELECT * FROM " . $tabla;
        if (!empty($where)) {
            $query .= " WHERE " . $where;
        }
    
        $result = mysqli_query($conn, $query);
    
        if ($result) {
            $status = true;
            $codigoHttp = 200;
            $mensaje = "Consulta exitosa";
            
            // Almacenar los datos en un array
            while ($row = mysqli_fetch_assoc($result)) {
                $datos[] = $row;
            }
    
            // Liberar memoria del resultado
            mysqli_free_result($result);
        } else {
            $status = false;
            $codigoHttp = 418;
            $mensaje = "Error en la consulta: " . mysqli_error($conn);
        }
    
        // Devolver el resultado
        return [
            "sql" => $query,
            "mensaje" => $mensaje,
            "status" => $status,
            "codigoHttp" => $codigoHttp,
            "datos" => $datos
        ];
    }

    static public function actualizarFilas($conn, $datos) {
        mysqli_set_charset($conn, "utf8mb4");
        $mensaje = "";
        $status = false;
        $codigoHttp = 400; // Código de error HTTP por defecto

        if (!empty($datos)) {
            foreach ($datos as $fila) {
                $tabla = $fila['tabla'];
                $datosActualizacion = $fila['datos'];
                $condiciones = $fila['condicion'];
    
                $updateQuery = "UPDATE $tabla SET ";
    
                foreach ($datosActualizacion as $columna => $valor) {
                    //si el valor es un array convertirlo a string
                    if(is_array($valor)){
                        $valor = json_encode($valor);
                    }
                    
                    if ($valor === 'NULL') {
                        $updateQuery .= "$columna = NULL, ";
                    } else {
                        if($valor === null){
                            $valor = "";
                        }
                        
                        $updateQuery .= "$columna = '" . mysqli_real_escape_string($conn, $valor) . "', ";
                    }
                }
    
                $updateQuery = rtrim($updateQuery, ", ");
    
                if (!empty($condiciones)) {
                    $updateQuery .= " WHERE ";
    
                    foreach ($condiciones as $condicion => $valorCondicion) {
                        if ($valorCondicion === 'NULL') {
                            $updateQuery .= "$condicion IS NULL AND ";
                        } else {
                            $updateQuery .= "$condicion = " . ($valorCondicion === null ? 'NULL' : "'$valorCondicion'") . " AND ";
                        }
                    }
    
                    $updateQuery = rtrim($updateQuery, "AND ");
                }
    
                $resultado = mysqli_query($conn, $updateQuery);
    
                if ($resultado) {
                    $mensaje = "Actualización exitosa";
                    $status = true;
                    $codigoHttp = 200;
                } else {
                    // Verificar si el error es por valor duplicado
                    if (mysqli_errno($conn) === 1062) {
                        // Obtener el valor duplicado
                        preg_match("/Duplicate entry '(.*)' for key/", mysqli_error($conn), $matches);
                        $valorDuplicado = $matches[1];
                        $mensaje = "Error al actualizar los datos. El valor '$valorDuplicado' ya existe en una columna con propiedad UNIQUE.";
                        $status = false;
                        $codigoHttp = 400;
                    } else {
                        $mensaje = "Error al actualizar los datos";
                        $codigoHttp = 418;
                        $status = false;
                    }
                }
            }
        } else {
            $mensaje = "No se proporcionaron datos para actualizar";
        }
    
        return [
            "sql" => $updateQuery,
            "mensaje" => $mensaje,
            "status" => $status,
            "codigoHttp" => $codigoHttp
        ];
    }
}