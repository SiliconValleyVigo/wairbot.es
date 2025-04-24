<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision, visto
partes: fecha, id_maquina, tipo, notas, estado, tiempos
tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente
documentos: id_parte, url
*/

class Partes{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'partes';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];
        $id_maquina = $data['id_maquina'];

        $tiempos =[
            [
                'inicio' => 0,
                'fin' => 0,
                'usuario' => '',
                'horasTotales' => '00:00:00',
            ],
        ];

        $data['tiempos'] = json_encode($tiempos);

        if($userRol === 'CLIENTE' || $userRol === 'TECNICO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un parte'
            ];
        }

        if($userRol === 'AGENTE'){
            $sql = "SELECT id_agente FROM maquinas WHERE id = $id_maquina";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $id_agente = $row['id_agente'];

                if($id_agente != $userId){
                    return [
                        'status' => 'error',
                        'message' => 'No tienes permisos para crear un parte'
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para crear un parte'
                ];
            }
        }

        $created = Tools::create($conn, $tabla, $data, $check);

        $idParte = $created['id'];
        $tipoParte = $data['tipo'];

        if($tipoParte === 'INSTALACION'){
            //crear tarea de instalación id_parte, descripcion, tipo, referencia
            $tareas = [[
                'id_parte' => $idParte,
                'descripcion' => 'Instalación del maquina',
                'tipo' => 'REPARAR',
                'referencia' => ''
            ]];
        }

        $tareas = [[
            'id_parte' => $idParte,
            'descripcion' => '',
            'tipo' => '',
            'referencia' => ''
        ]];

        if($tareas){
            foreach($tareas as $key => $tarea){
                $id_parte = $tarea['id_parte'];
                $descripcion = $tarea['descripcion'];
                $tipo = $tarea['tipo'];
                $referencia = $tarea['referencia'];
            
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, '$descripcion', '$tipo', '$referencia')";
                if (mysqli_query($conn, $sql)) {
                    $last_id = mysqli_insert_id($conn);
                    $tareas[$key]['id_tarea'] = $last_id; // Agrega el ID de la tarea al array de tareas
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            }

            //si hay error al insertar las tareas, devolver error
            if($conn->error){
                return [
                    'status' => 'error',
                    'message' => 'Error al crear las tareas',
                    'error' => $conn->error
                ];
            }
        }

        if($created){
            if($created['status'] == 'success'){
                return [
                    'status' => 'success',
                    'message' => 'Parte creado correctamente',
                    'data' => $created,
                    'tareas' => $tareas
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'Error al crear el parte',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el parte',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'partes';
        $check = [];

        if($_SESSION['user_rol'] === 'CLIENTE' || $_SESSION['user_rol'] === 'TECNICO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un parte'
            ];
        };

        //comprobar si el estado del parte es CERRADO
        $sql = "SELECT estado FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado === 'CERRADO'){
                return [
                    'status' => 'error',
                    'message' => 'No puedes eliminar un parte cerrado'
                ];
            }
        }

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Parte eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el parte'
            ];
        }
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'partes';
        $id = $request['id'];
        $userRol = $_SESSION['user_rol'];

        if($userRol === 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar un parte'
            ];
        }

        if(array_key_exists('firma', $data)){
            $imagenInfo = $data['firma'];

            if($imagenInfo != null && $imagenInfo != '' && $imagenInfo != 'null' && $imagenInfo != false){
                $nombre = 'firma_parte_' . $id . '.jpg';
                $base64 = $imagenInfo;
    
                $imagen = Tools::fileToUrl($base64, $nombre);

                if($imagen){
                    $data['firma'] = $imagen;
                }else{
                    return [
                        'status' => 'error',
                        'message' => 'Error al subir la imagen'
                    ];
                }
            }
        }

        //comprobar si el estado del parte es CERRADO
        $sql = "SELECT estado FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado === 'CERRADO'){
                //comprobar si existe data['estado'] y si no es ABIERTO devolver error
                if(isset($data['estado']) && $data['estado'] !== 'ABIERTO__' && $userRol !== 'ADMINISTRADOR'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar un parte cerrado'
                    ];
                }
                
            }
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Parte actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el parte'
            ];
        }
    }

    static public function getPartesByMaquinaId($request){
        $conn = $_SESSION['db_connection'];
        $id_maquina = $request['id_maquina'];

        $sql = "SELECT * FROM partes WHERE id_maquina = $id_maquina";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $partes = [];

            while($row = $result->fetch_assoc()){
                $partes[] = $row;
            }

            return [
                'status' => 'success',
                'message' => 'Partes obtenidos correctamente',
                'data' => $partes
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'No se encontraron partes'
            ];
        }
    }

    static public function closeParte($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'partes';
        $id = $request['id'];
        $userRol = $_SESSION['user_rol'];

        if($userRol === 'CLIENTE' || $userRol === 'TECNICO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar un parte'
            ];
        }

        //comprobar si el estado del parte es CERRADO
        $sql = "SELECT estado FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado === 'CERRADO'){
                //comprobar si existe data['estado'] y si no es ABIERTO devolver error
                if(isset($data['estado']) && $data['estado'] !== 'ABIERTO__' && $userRol !== 'ADMINISTRADOR'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar un parte cerrado'
                    ];
                }
                
            }
        }

        $updated = Tools::update($conn, $tabla, $id, ['estado' => 'CERRADO']);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Parte actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el parte'
            ];
        }
    }

    static public function getPartesByTecnicoId(){
        $conn = $_SESSION['db_connection'];
        $id_tecnico = $_SESSION['user_id'];

        $sql = "SELECT * FROM partes WHERE id_tecnico = $id_tecnico AND estado = 'ABIERTO__'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $partes = [];

            while($row = $result->fetch_assoc()){
                $partes[] = $row;
            }

            //obtener maquina y cliente de cada parte
            foreach($partes as $key => $parte){
                $id_maquina = $parte['id_maquina'];

                $sql = "SELECT * FROM maquinas WHERE id = $id_maquina";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $maquina = $result->fetch_assoc();
                    $partes[$key]['maquina'] = $maquina;

                    $id_cliente = $maquina['id_cliente'];

                    $sql = "SELECT * FROM usuarios WHERE id = $id_cliente";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $cliente = $result->fetch_assoc();
                        $partes[$key]['cliente'] = $cliente;
                    }
                }
            }

            return [
                'status' => 'success',
                'message' => 'Partes obtenidos correctamente',
                'data' => $partes
            ];
        }else{
            return [
                'status' => 'success',
                'message' => 'No se encontraron partes',
                'data' => []
            ];
        }
    }

    static public function getById($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];

        $sql = "SELECT * FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $parte = $result->fetch_assoc();

            return [
                'status' => 'success',
                'message' => 'Parte obtenido correctamente',
                'data' => $parte
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'No se encontró el parte'
            ];
        }
    }

    static public function enviarPresupuestoWhatsapp($request){
        $telefono = $request['telefono'];
        $nombreMotor = $request['nombre_motor'];
        $motor = $request['id_motor'];
        $parte = $request['id_parte'];

        $conn = $_SESSION['db_connection'];

        //obtener las tareas del parte
        $sql = "SELECT * FROM tareas WHERE id_parte = $parte";
        $result = $conn->query($sql);

        $lineasTareas = '';
        $costeTotal = 0;

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $descripcion = $row['descripcion'];
                $coste = $row['coste'];

                //eliminar de coste cualquier caracter que no sea un número, un punto o una coma
                $coste = preg_replace('/[^0-9,.]/', '', $coste);

                //si existe una coma en coste, eliminiar los puntos
                if(strpos($coste, ',') !== false){
                    $coste = str_replace('.', '', $coste);
                }

                //si hay más de un punto, eliminar todos menos el último y cambiarlo por una coma y si solo hay uno, cambiarlo por una coma
                if(substr_count($coste, '.') > 1){
                    $coste = str_replace('.', '', $coste);
                    $coste = substr_replace($coste, ',', strrpos($coste, '.'), 1);
                }else{
                    $coste = str_replace('.', ',', $coste);
                }

                $lineasTareas .= "$descripcion\n$coste €\n\n";

                //cambiar la coma por un punto
                $coste = str_replace(',', '.', $coste);

                //convertir el coste a float con dos decimales
                $coste = number_format((float)$coste, 2, '.', '');

                $costeTotal += $coste;
            }
        }

        $mensaje = "Este es el prespuesto final por la reparación de tu máquina $nombreMotor:\n\n$lineasTareas\n*Coste total*: $costeTotal €\n\n*Indicanos si apruebas el presupuesto para proceder con la reparación o si necesitas más información*";

        $mensaje = urlencode($mensaje);

        /*
        Hacer consulta por curl: http://localhost/wairbot-datos/bot-datos/
        body: {
    "key":"PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU",
    "userPhone":"111111111",
    "adminPhone":"wairbotrepair1",
    "text":"#1,1,&$telefono&$motor&$parte&$mensaje"
}
        */

        //si la url es http://localhost/wairbot-datos/bot-datos/, cambiarla por la url de producción https://gestion-datos.wairbot.com/bot-datos/
        
        //obtener url donde se encuentra el archivo
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        //si la url contiene localhost, cambiarla por la url de producción
        if(strpos($url, 'localhost') !== false){
            $url = 'https://gestion-datos.wairbot.com/bot-datos/';
        }else{
            $url = 'http://localhost/wairbot-datos/bot-datos/';
        }
        
        $key = 'PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU';
        $userPhone = '111111111';
        $adminPhone = 'wairbotrepair1';
        $text = "#1,1,&$telefono&$motor&$parte&$mensaje";

        $data = [
            'key' => $key,
            'userPhone' => $userPhone,
            'adminPhone' => $adminPhone,
            'text' => $text
        ];

        $data = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return [
            'status' => 'success',
            'message' => 'Presupuesto enviado correctamente',
            'data' => $response
        ];
    }

    //actualizar estados de partes y visto de maquinas
    static public function updateEstados($request){
        $idParte = $request['idParte'];
        $idMaquina = $request['idMaquina'];
        $tareasEnviadoCliente = $request['tareasEnviadoCliente'];
        $tareasAprobadoCliente = $request['tareasAprobadoCliente'];
        $maquinasVisto = $request['maquinasVisto'];

        $conn = $_SESSION['db_connection'];

        //visto maquina
        //convertir $idMaquina en entero
        $idMaquina = intval($idMaquina);
        $sql = "UPDATE maquinas SET visto = '$maquinasVisto' WHERE id = $idMaquina";
        $conn->query($sql);


        //enviado_cliente y aprobado_cliente
        //convertir $idParte en entero
        $idParte = intval($idParte);
        $sql = "UPDATE partes SET enviado_cliente = '$tareasEnviadoCliente', aprobado_cliente = '$tareasAprobadoCliente' WHERE id = $idParte";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Estados actualizados correctamente'
        ];
    }

    //gestionar tiempos de parte
    static public function gestionarTiempos($request){
        $idParte = $request['idParte'];
        $nombreUsuario = $_SESSION['user_name'];
        $fecha = date('Y-m-d H:i:s');

        $conn = $_SESSION['db_connection'];

        //obtener tiempos de parte
        $sql = "SELECT tiempos FROM partes WHERE id = $idParte";
        $result = $conn->query($sql);

        $tiempos = [];
        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $tiempos = json_decode($row['tiempos'], true);
        }

        //obtener último tiempo
        $ultimoTiempo = end($tiempos);

        //si el último tiempo el fin es 0, finalizar tiempo
        if($ultimoTiempo['fin'] == 0 && $ultimoTiempo['inicio'] != 0){
            self::finalizarTiempo($fecha, $nombreUsuario, $idParte, $tiempos, $ultimoTiempo, $conn);
        }else{
            self::iniciarTiempo($fecha, $nombreUsuario, $idParte, $tiempos, $ultimoTiempo, $conn);
        }

        return [
            'status' => 'success',
            'message' => 'Tiempo actualizado correctamente'
        ];
    }

    //iniciar tiempo de parte
    static public function iniciarTiempo($fecha, $nombreUsuario, $idParte, $tiempos, $ultimoTiempo, $conn){
        //añadir nuevo fecha a inicio
        $ultimoTiempo['inicio'] = $fecha;

        //añadir nombre de usuario a tiempo
        $ultimoTiempo['usuario'] = $nombreUsuario;

        //eliminar último tiempo de tiempos
        array_pop($tiempos);

        //añadir nuevo tiempo a tiempos
        $tiempos[] = $ultimoTiempo;

        //guardar tiempos en base de datos
        $tiempos = json_encode($tiempos);
        $sql = "UPDATE partes SET tiempos = '$tiempos' WHERE id = $idParte";
        $conn->query($sql);

        return true;
    }

    //finalizar tiempo de parte y crear nuevo tiempo
    static public function finalizarTiempo($fecha, $nombreUsuario, $idParte, $tiempos, $ultimoTiempo, $conn){
        //añadir nuevo fecha a fin
        $ultimoTiempo['fin'] = $fecha;

        //horas totales
        $inicio = new DateTime($ultimoTiempo['inicio']);
        $fin = new DateTime($ultimoTiempo['fin']);
        $diferencia = $inicio->diff($fin);
        $horasTotales = $diferencia->format('%H:%I:%S');
        $ultimoTiempo['horasTotales'] = $horasTotales;

        //añadir nombre de usuario a tiempo
        $ultimoTiempo['usuario'] = $nombreUsuario;

        //eliminar último tiempo de tiempos
        array_pop($tiempos);

        //añadir nuevo tiempo a tiempos
        $tiempos[] = $ultimoTiempo;

        //añadir nuevo tiempo vacío
        $tiempos[] = [
            'inicio' => 0,
            'fin' => 0,
            'usuario' => '',
            'horasTotales' => '00:00:00',
        ];

        //guardar tiempos en base de datos
        $tiempos = json_encode($tiempos);
        $sql = "UPDATE partes SET tiempos = '$tiempos' WHERE id = $idParte";
        $conn->query($sql);

        return true;
    }

    //obtener una lista de tipos de partes sin repetir
    static public function getTipos(){
        $conn = $_SESSION['db_connection'];

        $sql = "SELECT DISTINCT tipo FROM partes";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $tipos = [];

            while($row = $result->fetch_assoc()){
                $tipos[] = $row['tipo'];
            }

            return [
                'status' => 'success',
                'message' => 'Tipos obtenidos correctamente',
                'data' => $tipos
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'No se encontraron tipos',
                'data' => []
            ];
        }
    }

    //Obtener Todos los partes no finalizadas, que el técnico no sea vacío o nulo y aprobadas por el cliente
    static public function getPartesSinFinalizar($request){
        $conn = $_SESSION['db_connection'];
        $search = $request['search'] ?? '';
       
        $sql = "SELECT * FROM partes WHERE estado != 'CERRADO'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $partes = [];

            while($row = $result->fetch_assoc()){
                $partes[] = $row;
            }

            //obtener el nombre de la maquina y el nombre del cliente de cada parte
            foreach($partes as $key => $parte){
                $id_maquina = $parte['id_maquina'];

                $sql = "SELECT * FROM maquinas WHERE id = $id_maquina";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $maquina = $result->fetch_assoc();
                    $partes[$key]['maquina'] = $maquina;

                    $id_cliente = $maquina['id_cliente'];

                    $sql = "SELECT * FROM usuarios WHERE id = $id_cliente";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $cliente = $result->fetch_assoc();
                        $partes[$key]['cliente'] = $cliente;
                    }

                    //obtener el nombre del técnico
                    $id_tecnico = $parte['id_tecnico'];

                    $sql = "SELECT * FROM usuarios WHERE id = $id_tecnico";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $tecnico = $result->fetch_assoc();
                        $partes[$key]['tecnico'] = $tecnico;
                    }
                }
            }

            //eliminar partes que no contengan el texto de búsqueda en ninguna de sus propiedades incluyendo maquina, cliente y tecnico
            if($search){
                $partes = array_filter($partes, function($parte) use ($search){
                    $maquina = $parte['maquina'];
                    $cliente = $parte['cliente'];
                    $tecnico = $parte['tecnico'];

                    $maquinaValues = array_values($maquina);
                    $clienteValues = array_values($cliente);
                    $tecnicoValues = array_values($tecnico);

                    $parteValues = array_merge($maquinaValues, $clienteValues, $tecnicoValues);

                    $parteValues = array_map(function($value){
                        return strtolower($value);
                    }, $parteValues);

                    $search = strtolower($search);

                    return in_array($search, $parteValues);
                });
            }

            return [
                'status' => 'success',
                'message' => 'Partes obtenidos correctamente',
                'data' => $partes
            ];
        }else{
            return [
                'status' => 'success',
                'message' => 'No se encontraron partes',
                'data' => []
            ];
        }
    }
}