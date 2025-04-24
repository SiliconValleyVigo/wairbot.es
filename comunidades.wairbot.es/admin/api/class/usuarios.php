<?php
 
class Usuarios{
    public static $tabla = 'usuarios_comunidades_wairbot_central';

    static public function create($request){
        $tabla = self::$tabla;
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $check = ['email'];
        $userRol = $_SESSION['user_rol'];

        //si no es admin echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un usuario'
            ];
        }

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Agente creado correctamente',
                    'data' => [
                        'id' => $dataId
                    ]
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

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $data = $request['data'];
        $tabla = self::$tabla;
        $userRol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar un usuario'
            ];
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
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los usuarios'
            ];
        }

        //sin filtro
        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

        return [
            'status' => 'success',
            'message' => 'Usuarios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function getById($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver un usuario'
            ];
        }

        $filtro = [];
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Usuario obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $check = [];
        $urserRol = $_SESSION['user_rol'];
        $email = $_SESSION['user_email'];
        $id = $request['id'];

        if($urserRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un usuario'
            ];
        }

        //obtener el telefono de la tabla admin_bot por el email
        $sql = "SELECT AD_numero_de_telefono_del_bot__tel FROM admin_bot WHERE AD_email_ = '$email'";
        $result = $conn->query($sql);
        
        //si encuentra alguno ejecutar deleteBot
        if($result->num_rows > 0){
            $adminPhone = $result->fetch_assoc()['AD_numero_de_telefono_del_bot__tel'];
            ToolsWairbot::deleteBot($adminPhone);
        }
        
        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Usuario eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el usuario',
                'error' => $deleted
            ];
        }
    }
}