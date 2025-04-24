<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
motores: imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ulitma_revision, fecha_proxima_revision
partes: fecha, id_motor, tipo, notas, estado, id_tecnico
tareas: id_parte, descripcion, tipo, referencia
documentos: tipo, id_parte, url
solicitudes: id_motor, id_agente, estado
notificaciones: id_cliente, texto
publicidad: url, imagen, nombre, precio, estado
emails: email, texto, fecha
*/

class Motores{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'motores';
        $check = ['codigo'];
        $userRol = $_SESSION['user_rol'];

        if($userRol != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un motor'
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

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                return [
                    'status' => 'success',
                    'message' => 'Motor creado correctamente',
                    'data' => $created
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'El código ya está en uso',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el motor',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'motores';
        $check = [];

        if($_SESSION['user_rol'] != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un motor'
            ];
        }

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Motor eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el motor'
            ];
        }
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $id = $request['id'];
        $tabla = 'motores';
        $userRol = $_SESSION['user_rol'];

        if($userRol != 'ADMINISTRADOR' && $userRol != 'AGENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar un motor'
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

        //si es agente solo puede actualizar id_cliente del motor que le pertenece
        if($userRol === 'AGENTE'){
            //eliminar el resto de campos que no sean id_cliente
            $data = array_filter($data, function($key){
                return $key == 'id_cliente';
            }, ARRAY_FILTER_USE_KEY);
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if(array_key_exists('id_agente', $data) || array_key_exists('id_cliente', $data)){
            $sql = "UPDATE solicitudes SET estado = 'ENTREGADO' WHERE id_motor = $id";
            $conn->query($sql);
        }

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Motor actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el motor',
                'error' => $updated
            ];
        }
    }

    static public function read($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'motores';
        $userId = $_SESSION['user_id'];
        $rol = $_SESSION['user_rol'];
        $enStock = $request['enStock'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el usuario es un agente solo puede ver los motores asociados a id_agente
        $filtro = [];
        if($rol === 'AGENTE'){
            $filtro['id_agente'] = $userId;
        }

        if($rol === 'CLIENTE'){
            $filtro['id_cliente'] = $userId;
        }

        $datosRelacionados = [];
        if($rol === 'ADMINISTRADOR'){
            $datosRelacionados = ['id_cliente' => 'usuarios', 'id_agente' => 'usuarios'];
        }else if($rol === 'AGENTE'){
            $datosRelacionados = ['id_cliente' => 'usuarios'];
        }

        if(!$enStock){
            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            $resultData = $result['data'];

            //si es administrador eliminar los motores que no tienen id_agente ni id_cliente
            if($rol === 'ADMINISTRADOR'){
                $resultData = array_filter($resultData, function($motor){
                    //convertir a entero
                    $motorIdAgente = intval($motor['id_agente']);
                    $motorIdCliente = intval($motor['id_cliente']);
                    return $motorIdAgente != 0 || $motorIdCliente != 0;
                });
            }else if($rol === 'AGENTE'){
                //si es agente eliminar los motores que no tienen id_cliente
                $resultData = array_filter($resultData, function($motor){
                    //convertir a entero
                    $motorIdCliente = intval($motor['id_cliente']);
                    return $motorIdCliente != 0;
                });
            }

            $result['data'] = $resultData;
        }else{
            if($rol === 'ADMINISTRADOR'){
                $result = Tools::read($conn, $nPagina, $tabla, ['id_cliente' => 0, 'id_agente' => 0], $datosRelacionados, $search);              
            }else if($rol === 'AGENTE'){
                if($enStock === true){
                    $result = Tools::read($conn, $nPagina, $tabla, ['id_cliente' => 0, 'id_agente' => 0], $datosRelacionados, $search);
                }else{
                    $result = Tools::read($conn, $nPagina, $tabla, ['id_cliente' => 0, 'id_agente' => $userId], $datosRelacionados, $search);
                }
            }
        }
        
        return [
            'status' => 'success',
            'message' => 'Motores obtenidos correctamente',
            'data' => $result,
        ];
    }

    static public function getById($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'motores';
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
            'message' => 'Motor obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function getAll($usuario){
        $conn = $_SESSION['db_connection'];
        $rol = $usuario['rol'];
        $tabla = 'motores';
        $userId = $usuario['id'];

        $resultado = [];
        if($rol === 'ADMINISTRADOR'){
            $conn = $_SESSION['db_connection'];
            //obtener marca, modelo, codigo de tabla motores con id_agente e id_cliente vacios o nulos
            $sql = "SELECT id, marca, modelo, codigo FROM $tabla WHERE (id_agente IS NULL OR id_agente = '') AND (id_cliente IS NULL OR id_cliente = '')";
            $result = $conn->query($sql);       
            
            //obtener un array con id => marca | modelo | codigo
            while($row = $result->fetch_assoc()){
                $resultado[$row['id']] = $row['marca'] . ' ' . $row['modelo'] . ' ' . $row['codigo'];
            }
        }else if($rol === 'AGENTE'){
            $conn = $_SESSION['db_connection'];
            //obtener marca, modelo, codigo de tabla motores
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

    static public function search($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'motores';
        $busqueda = $request['busqueda'];
        $userRol = $_SESSION['user_rol'];

        if($userRol != 'AGENTE' && $userRol != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'Rol no válido'
            ];
        }

        $filtro = [];
        $datosRelacionados = [];
        if($userRol === 'AGENTE'){
            $filtro['id_agente'] = $_SESSION['user_id'];
        }

        if($userRol === 'ADMINISTRADOR'){
            $datosRelacionados = ['id_cliente' => 'usuarios', 'id_agente' => 'usuarios'];
        }else if($userRol === 'AGENTE'){
            $datosRelacionados = ['id_cliente' => 'usuarios'];
        }

        $result = Tools::search($conn, $nPagina, $tabla, $busqueda, $filtro, $datosRelacionados);

        return $result;
    }

    static public function getByClienteId(){
        $conn = $_SESSION['db_connection'];
        $user_id = $_SESSION['user_id'];

        $sql = "SELECT * FROM motores WHERE id_cliente = $user_id";
        $result = $conn->query($sql);

        $motores = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $motores[] = $row;
            }
        }

        return [
            'status' => 'success',
            'data' => $motores
        ];
    }

    static public function getByClienteIdRequest($request){
        $conn = $_SESSION['db_connection'];
        $cliente_id = $request['cliente_id'];
        $rol = $_SESSION['user_rol'];

        if($rol != 'ADMINISTRADOR' && $rol != 'AGENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los motores de un cliente'
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

        $sql = "SELECT * FROM motores WHERE id_cliente = $cliente_id";
        $result = $conn->query($sql);

        $motores = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $motores[] = $row;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Motores del cliente obtenidos correctamente',
            'data' => $motores
        ];
    }
}