<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision
partes: fecha, id_maquina, tipo, notas, estado
tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente
documentos: id_parte, url
*/

class Maquinas{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'maquinas';
        $check = ['codigo'];
        $userRol = $_SESSION['user_rol'];

        if($userRol != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un maquina'
            ];
        }

        //si el código está vacío añadir un código único
        if(!array_key_exists('codigo', $data) || $data['codigo'] == '' || $data['codigo'] == null){
            $data['codigo'] = uniqid();
        }

        if(array_key_exists('imagen', $data)){
            $imagenInfo = $data['imagen'];

            if($imagenInfo != null && $imagenInfo != '' && $imagenInfo != 'null' && $imagenInfo != false){
                $nombre = $imagenInfo['nombre'];
                $base64 = $imagenInfo['base64'];
    
                $imagen = Tools::fileToUrl($base64, $nombre);

                if($imagen){
                    $data['imagen'] = $imagen;
                }else{
                    return [
                        'status' => 'error',
                        'message' => 'Error al subir la imagen'
                    ];
                }
            }
        }

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                return [
                    'status' => 'success',
                    'message' => 'Maquina creado correctamente',
                    'data' => $created
                ];
            }

            if($created['status'] == 'error'){
                //si el código ya existe añadir un código unico
                if($created['result'] == 'El valor de la columna codigo ya existe'){
                    $data['codigo'] = $data['codigo'] . '-' . uniqid();
                    $created = Tools::create($conn, $tabla, $data, $check);

                    if($created){
                        return [
                            'status' => 'success',
                            'message' => 'Maquina creado correctamente',
                            'data' => $created
                        ];
                    }else{
                        return [
                            'status' => 'error',
                            'message' => 'Error al crear el maquina',
                            'error' => $created
                        ];
                    }
                }
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el maquina',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'maquinas';
        $check = [];

        if($_SESSION['user_rol'] != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un maquina'
            ];
        }

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Maquina eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el maquina'
            ];
        }
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $id = $request['id'];
        $tabla = 'maquinas';
        $userRol = $_SESSION['user_rol'];

        if($userRol != 'ADMINISTRADOR' && $userRol != 'AGENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar una maquina'
            ];
        }

        if(array_key_exists('imagen', $data)){
            $imagenInfo = $data['imagen'];

            if($imagenInfo != null && $imagenInfo != '' && $imagenInfo != 'null' && $imagenInfo != false){
                $nombre = $imagenInfo['nombre'];
                $base64 = $imagenInfo['base64'];
    
                $imagen = Tools::fileToUrl($base64, $nombre);

                if($imagen){
                    $data['imagen'] = $imagen;
                }else{
                    return [
                        'status' => 'error',
                        'message' => 'Error al subir la imagen'
                    ];
                }
            }
        }

        //si es agente solo puede actualizar id_cliente del maquina que le pertenece
        if($userRol === 'AGENTE'){
            //eliminar el resto de campos que no sean id_cliente
            $data = array_filter($data, function($key){
                return $key == 'id_cliente';
            }, ARRAY_FILTER_USE_KEY);
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Maquina actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el maquina',
                'error' => $updated
            ];
        }
    }

    static public function read($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'maquinas';
        $userId = $_SESSION['user_id'];
        $rol = $_SESSION['user_rol'];
        $enStock = $request['enStock'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el usuario es un agente solo puede ver los maquinas asociados a id_agente
        $filtro = [];
        if($rol === 'AGENTE'){
            $filtro['id_agente'] = $userId;
        }

        if($rol === 'CLIENTE'){
            $filtro['id_cliente'] = $userId;
        }

        $filtroNegativo = [];
        if($rol === 'ADMINISTRADOR'){
            $filtroNegativo = [
                'id_cliente' => 0,
                'id_agente' => 0,
                'id_agente' => 1
            ];
        }

        $datosRelacionados = [];
        if($rol === 'ADMINISTRADOR'){
            $datosRelacionados = ['id_cliente' => 'usuarios', 'id_agente' => 'usuarios'];
        }else if($rol === 'AGENTE'){
            $datosRelacionados = ['id_cliente' => 'usuarios'];
        }

        if(!$enStock){
            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, null, $filtroNegativo);

            $resultData = $result['data'];

            //si es administrador eliminar los maquinas que no tienen id_agente ni id_cliente
            if($rol === 'ADMINISTRADOR'){
                $resultData = array_filter($resultData, function($maquina){
                    //convertir a entero
                    $maquinaIdAgente = intval($maquina['id_agente']);
                    $maquinaIdCliente = intval($maquina['id_cliente']);
                    return $maquinaIdAgente != 0 || $maquinaIdCliente != 0;
                });
            }else if($rol === 'AGENTE'){
                //si es agente eliminar los maquinas que no tienen id_cliente
                $resultData = array_filter($resultData, function($maquina){
                    //convertir a entero
                    $maquinaIdCliente = intval($maquina['id_cliente']);
                    return $maquinaIdCliente != 0;
                });
            }

            $result['data'] = $resultData;
        }else{
            $orderBy = "ORDER BY CASE WHEN maquinas.visto = 'CONTESTADO ' THEN 1 WHEN maquinas.visto = 'PENDIENTE' THEN 2 ELSE 3 END, maquinas.id DESC";
            if($rol === 'ADMINISTRADOR'){
                $filtro = [
                    'id_cliente' => 0,
                    'id_agente' => 0,
                    'id_agente' => 1
                ];
                $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy);              
            }else if($rol === 'AGENTE'){
                if($enStock === true){
                    $result = Tools::read($conn, $nPagina, $tabla, ['id_cliente' => 0, 'id_agente' => 0], $datosRelacionados, $search, $orderBy);;
                }else{
                    $result = Tools::read($conn, $nPagina, $tabla, ['id_cliente' => 0, 'id_agente' => $userId], $datosRelacionados, $search, $orderBy);;
                }
            }
        }
        
        return [
            'status' => 'success',
            'message' => 'Maquinas obtenidos correctamente',
            'data' => $result,
        ];
    }

    static public function getById($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'maquinas';
        $rol = $_SESSION['user_rol'];

        $enStock = false;
        if(array_key_exists('enStock', $request)){
            $enStock = $request['enStock'];
        }

        $filtro = [];
        $datosRelacionados = [];
        if($rol === 'AGENTE'){
            if($enStock){
                $filtro['id_agente'] = $_SESSION['user_id'];
            }
        }

        if($rol === 'CLIENTE'){
            $filtro['id_cliente'] = $_SESSION['user_id'];
        }

        if($rol === 'ADMINISTRADOR'){
            $datosRelacionados = ['id_cliente' => 'usuarios', 'id_agente' => 'usuarios'];
        }else if($rol === 'AGENTE'){
            $datosRelacionados = ['id_cliente' => 'usuarios'];
        }

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Maquina obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function getAll($usuario){
        $conn = $_SESSION['db_connection'];
        $rol = $usuario['rol'];
        $tabla = 'maquinas';
        $userId = $usuario['id'];

        $resultado = [];
        if($rol === 'ADMINISTRADOR'){
            $conn = $_SESSION['db_connection'];
            //obtener marca, modelo, codigo de tabla maquina con id_agente e id_cliente vacios o nulos
            $sql = "SELECT id, marca, modelo, codigo FROM $tabla WHERE (id_agente IS NULL OR id_agente = '') AND (id_cliente IS NULL OR id_cliente = '')";
            $result = $conn->query($sql);       
            
            //obtener un array con id => marca | modelo | codigo
            while($row = $result->fetch_assoc()){
                $resultado[$row['id']] = $row['marca'] . ' ' . $row['modelo'] . ' ' . $row['codigo'];
            }
        }else if($rol === 'AGENTE'){
            $conn = $_SESSION['db_connection'];
            //obtener marca, modelo, codigo de tabla maquinas
            $sql = "SELECT id, marca, modelo, codigo FROM $tabla WHERE id_agente = $userId AND (id_cliente IS NULL OR id_cliente = '')";
            $result = $conn->query($sql);         
            
            //obtener un array con id => marca | modelo | codigo
            while($row = $result->fetch_assoc()){
                $resultado[$row['id']] = $row['marca'] . ' ' . $row['modelo'] . ' ' . $row['codigo'];
            }
            
        }else{
            return [
                'status' => 'error',
                'message' => 'Rol no válido'
            ];
        }

        return $resultado;
    }

    static public function getByClienteId(){
        $conn = $_SESSION['db_connection'];
        $user_id = $_SESSION['user_id'];

        $sql = "SELECT * FROM maquinas WHERE id_cliente = $user_id";
        $result = $conn->query($sql);

        $maquinas = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $maquinas[] = $row;
            }
        }

        return [
            'status' => 'success',
            'data' => $maquinas
        ];
    }

    static public function getByClienteIdRequest($request){
        $conn = $_SESSION['db_connection'];
        $cliente_id = $request['cliente_id'];
        $rol = $_SESSION['user_rol'];

        if($rol != 'ADMINISTRADOR' && $rol != 'AGENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los maquinas de un cliente'
            ];
        }

        //si el rol es AGENTE comprobar que el cliente pertenece al agente
        if($rol === 'AGENTE'){
            $agente_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM usuarios WHERE id = $cliente_id AND id_agente = $agente_id";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                return [
                    'status' => 'error',
                    'message' => 'El cliente no pertenece al agente'
                ];
            }
        }

        $sql = "SELECT * FROM maquinas WHERE id_cliente = $cliente_id";
        $result = $conn->query($sql);

        $maquinas = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $maquinas[] = $row;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Maquinas del cliente obtenidos correctamente',
            'data' => $maquinas
        ];
    }

    //comprobar si una máquina existe por su código y si el usuario tiene permisos para verla
    static public function checkMaquina($request){
        $conn = $_SESSION['db_connection'];
        $codigo = $request['codigo'];
        $rol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        $sql = "SELECT * FROM maquinas WHERE codigo = '$codigo'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'La máquina no existe'
            ];
        }

        $maquina = $result->fetch_assoc();

        //obtener el agente de la máquina
        $agenteId = $maquina['id_agente'];

        //si el rol es agente comprobar que la máquina pertenece al agente
        if($rol === 'AGENTE'){
            if($agenteId != $userId){
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para ver esta máquina'
                ];
            }
        }

        //si el rol es cliente o tecnico impedir que vea la máquina
        if($rol === 'CLIENTE' || $rol === 'TECNICO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver esta máquina'
            ];
        }

        //si puede ver la maquina mandar si la máquina está en stock o no
        $enStock = false;
        if($maquina['id_cliente'] == 0 && $maquina['id_agente'] == 0){
            $enStock = true;
        }

        //mandar el id 
        return [
            'status' => 'success',
            'message' => 'Máquina obtenida correctamente',
            'data' => [
                'id' => $maquina['id'],
                'enStock' => $enStock
            ]
        ];
    }
}