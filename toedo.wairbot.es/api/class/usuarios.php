<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision
partes: fecha, id_maquina, tipo, notas, estado
tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente
documentos: id_parte, url
*/
 
class Usuarios{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'usuarios';
        $check = ['email', 'nombre'];
        $dataRol = $data['rol'];
        $userRol = $_SESSION['user_rol'];

        if(!self::permisos('create', $userRol, $dataRol)){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear este usuario'
            ];
        }

        //comprobar si existe imagen en data
        if(array_key_exists('imagen', $data)){
            $imagenInfo = $data['imagen'];

            //si imagen no es null o vacio obtengo la url de la imagen
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
                    'message' => 'Agente creado correctamente',
                    'data' => $created
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'El email o el nombre ya están en uso',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el agente',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'usuarios';
        $check = [];

        $dataRol = self::obtenerRol($id);
        $userRol = $_SESSION['user_rol'];

        if(!self::permisos('delete', $userRol, $dataRol, $id)){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar este usuario'
            ];
        }

        if($dataRol === 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No puedes eliminar un administrador'
            ];
        }

        if($dataRol === 'AGENTE'){
            $check = [
                'maquinas' => 'id_agente',
                'usuarios' => 'id_agente',
            ];
        }

        if($dataRol === 'TECNICO'){
            $check = [
                'partes' => 'id_tecnico',
            ];
        }

        if($dataRol === 'CLIENTE'){
            $check = [
                'maquinas' => 'id_cliente',
            ];
        }
        
        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Agente eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el agente',
                'error' => $deleted
            ];
        }
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $data = $request['data'];
        $tabla = 'usuarios';

        $dataRol = self::obtenerRol($id);
        $userRol = $_SESSION['user_rol'];

        if(!self::permisos('update', $userRol, $dataRol, $id)){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar este usuario'
            ];
        }

        //imagen
        if(array_key_exists('imagen', $data)){
            $imagenInfo = $data['imagen'];

            //si imagen no es null o vacio obtengo la url de la imagen
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

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Agente actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el agente',
                'error' => $updated
            ];
        }
    }

    static public function read($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'usuarios';
        $dataRol = $request['rol'];
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        if(!self::permisos('read', $rol, $dataRol)){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para leer este usuario'
            ];
        }
        
        $filtro = ['rol' => $dataRol];
        $datosRelacionados = [];
        if($rol === 'AGENTE'){
            $filtro['id_agente'] = $_SESSION['user_id'];
        }else{
            if($dataRol === 'CLIENTE' || $dataRol === 'TECNICO'){
                $datosRelacionados = ['id_agente' => 'usuarios'];
            }
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

        //obtener de la tabla conversaciones los userPhone donde la columna leido sea 0 y el adminPhone sea wairbotrepair1_CHAT_
        $sql = "SELECT userPhone FROM conversaciones WHERE leido = 0 AND adminPhone = 'wairbotrepair1_CHAT_'";

        //conectar a:
        $DB_NAME = getenv('WAIRBOT_DB_HOST');
        $DB_USER = getenv('WAIRBOT_DB_USER');
        $DB_PASS = getenv('WAIRBOT_DB_PASSWORD');
        $DB_HOST = getenv('WAIRBOT_DB_NAME');
        $connWairbot = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if($connWairbot->connect_error){
            return [
                'status' => 'error',
                'message' => 'Error al conectar con la base de datos'
            ];
        }

        $result2 = $connWairbot->query($sql);
        $userPhones = [];
        if($result2->num_rows > 0){
            while($row = $result2->fetch_assoc()){
                $userPhones[] = $row['userPhone'];
            }
        }

        //cerrar la conexion
        $connWairbot->close();

        //obtener los usuarios con esos userPhones de la tabla usuarios
        $usuariosConNotificaciones = [];
        foreach ($userPhones as $userPhone) {
            $sql = "SELECT * FROM usuarios WHERE telefono_whatsapp = '$userPhone' AND rol = 'CLIENTE' AND id_agente = " . $_SESSION['user_id'];
            $result3 = $conn->query($sql);
            if($result3->num_rows > 0){
                $usuariosConNotificaciones[] = $result3->fetch_assoc();
            }
        }

        return [
            'status' => 'success',
            'message' => 'Usuarios obtenidos correctamente',
            'data' => $result,
            'userPhones' => $userPhones,
            'notificaciones' => $usuariosConNotificaciones
        ];
    }

    static public function getById($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'usuarios';
        $dataRol = $request['rol'];
        $rol = $_SESSION['user_rol'];

        if(!self::permisos('read', $rol, $dataRol, $id)){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para leer este usuario'
            ];
        }

        $filtro = [];
        $datosRelacionados = [];
        if($rol === 'AGENTE'){
            $filtro['id_agente'] = $_SESSION['user_id'];
        }else{
            if($dataRol === 'CLIENTE' || $dataRol === 'TECNICO'){
                $datosRelacionados = ['id_agente' => 'usuarios'];
            }
        }

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Usuario obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function getAll($usuario){
        $conn = $_SESSION['db_connection'];
        $id = $usuario['id'];
        $rol = $usuario['rol'];

        if($rol === 'ADMINISTRADOR'){
            $tecnicos = [];
            $clientes = [];
            $agentes = [];

            //obtener id y nombre de los tecnicos
            $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'TECNICO'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $tecnicos[] = $row;
                }
            }

            //obtener id y nombre de los clientes
            $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'CLIENTE'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $clientes[] = $row;
                }
            }

            //obtener id y nombre de los agentes
            $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'AGENTE'";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $agentes[] = $row;
                }
            }

            //formatear los resultados en id:nombre
            $tecnicosFormateados = [];
            foreach ($tecnicos as $tecnico) {
                $tecnicosFormateados[$tecnico['id']] = $tecnico['nombre'];
            }

            $clientesFormateados = [];
            foreach ($clientes as $cliente) {
                $clientesFormateados[$cliente['id']] = $cliente['nombre'];
            }

            $agentesFormateados = [];
            foreach ($agentes as $agente) {
                $agentesFormateados[$agente['id']] = $agente['nombre'];
            }

            return [
                'tecnicos' => $tecnicosFormateados,
                'clientes' => $clientesFormateados,
                'agentes' => $agentesFormateados
            ];
        }

        if($rol === 'AGENTE'){
            $tecnicos = [];
            $clientes = [];

            //obtener id y nombre de los tecnicos
            $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'TECNICO' AND id_agente = $id";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $tecnicos[] = $row;
                }
            }

            //obtener id y nombre de los clientes
            $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'CLIENTE' AND id_agente = $id";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $clientes[] = $row;
                }
            }

            //formatear los resultados en id:nombre
            $tecnicosFormateados = [];
            foreach ($tecnicos as $tecnico) {
                $tecnicosFormateados[$tecnico['id']] = $tecnico['nombre'];
            }

            $clientesFormateados = [];
            foreach ($clientes as $cliente) {
                $clientesFormateados[$cliente['id']] = $cliente['nombre'];
            }

            return [
                'tecnicos' => $tecnicosFormateados,
                'clientes' => $clientesFormateados
            ];
        }

        if($rol === 'TECNICO' || $rol === 'CLIENTE'){
            return false;
        }
    }

    //////////////////////////////////////////////// PRIVADAS ////////////////////////////////////////////////

    static private function permisos($funcion, $userRol, $dataRol, $id=0){
        if($userRol === 'ADMINISTRADOR'){ return true;}

        if($funcion === 'create'){
            if($userRol === 'AGENTE' && ($dataRol === 'TECNICO' || $dataRol === 'CLIENTE')){ return true;}
        }

        if($funcion === 'delete' || $funcion === 'update'){
            if($userRol === 'AGENTE' && ($dataRol === 'TECNICO' || $dataRol === 'CLIENTE')){ 
                //comprobar en la tabla de usuarios si la fila con el id del usuario a eliminar tiene el id del agente que lo está eliminando
                $conn = $_SESSION['db_connection'];
                $sql = "SELECT * FROM usuarios WHERE id = $id AND id_agente = " . $_SESSION['user_id'];
                $result = $conn->query($sql);
                if($result->num_rows > 0){
                    return true;
                }
            }
        }

        if($funcion === 'read'){
            if($userRol === 'AGENTE'){ return true;}
            if($userRol === 'TECNICO'){
                //obtener su id_agente y comprobar si es igual al id_agente del usuario que se quiere leer
                $conn = $_SESSION['db_connection'];
                $sql = "SELECT id_agente FROM usuarios WHERE id = " . $_SESSION['user_id'];
                $result = $conn->query($sql);
                $id_agente = $result->fetch_assoc()['id_agente'];

                $sql = "SELECT * FROM usuarios WHERE id = $id AND id_agente = $id_agente";
                $result = $conn->query($sql);
                if($result->num_rows > 0){
                    return true;
                }
                
            }
        }

        return false;
    }

    static private function obtenerRol($id){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT rol FROM usuarios WHERE id = $id";
        $result = $conn->query($sql);
        $rol = $result->fetch_assoc();
        return $rol['rol'];
    }

    static public function checkPassword($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $password = $request['password'];

        //obtener la contraseña del admin
        $adminName = getenv('DB_ADMIN_NAME');
        $sql = "SELECT password FROM usuarios WHERE nombre = '$adminName'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if($row['password'] === $password){
            return [
                'status' => 'success',
                'message' => 'Contraseña correcta'
            ];
        }

        $sql = "SELECT password FROM usuarios WHERE id = $id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        if($row['password'] === $password){
            return [
                'status' => 'success',
                'message' => 'Contraseña correcta'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Contraseña incorrecta'
            ];
        }
    }
}