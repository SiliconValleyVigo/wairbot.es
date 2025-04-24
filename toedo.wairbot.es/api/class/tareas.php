<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision
partes: fecha, id_maquina, tipo, notas, estado
tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente, tiempos
documentos: id_parte, url
*/

class Tareas{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'tareas';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];
        $id_parte = $data['id_parte'];

        //si userRol es CLIENTE, no puede crear tareas
        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear una tarea'
            ];
        }

        //si es TECNICO o AGENTE, comprobar si el parte es suyo
        if($userRol == 'TECNICO' || $userRol == 'AGENTE'){
            $sql = "SELECT id_tecnico, id_maquina FROM partes WHERE id = $id_parte";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $id_tecnico = $row['id_tecnico'];
                $id_maquina = $row['id_maquina'];

                if($userRol == 'TECNICO' && $id_tecnico != $userId){
                    return [
                        'status' => 'error',
                        'message' => 'No tienes permisos para crear una tarea'
                    ];
                }

                if($userRol == 'AGENTE'){
                    $sql = "SELECT id_agente FROM maquinas WHERE id = $id_maquina";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $row = $result->fetch_assoc();
                        $id_agente = $row['id_agente'];

                        if($id_agente != $userId){
                            return [
                                'status' => 'error',
                                'message' => 'No tienes permisos para crear una tarea'
                            ];
                        }
                    }else{
                        return [
                            'status' => 'error',
                            'message' => 'No tienes permisos para crear una tarea'
                        ];
                    }
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para crear una tarea'
                ];
            }
        }

        //si el parte está cerrado, no se puede crear una tarea
        $sql = "SELECT estado FROM partes WHERE id = $id_parte";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado == 'CERRADO'){
                return [
                    'status' => 'error',
                    'message' => 'No puedes crear una tarea en un parte cerrado'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'No puedes crear una tarea en un parte cerrado'
            ];
        }

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                return [
                    'status' => 'success',
                    'message' => 'Tarea creada correctamente',
                    'data' => $created
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'Error al crear la tarea',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la tarea',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'tareas';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar una tarea'
            ];
        }

        $sql = "SELECT id_parte FROM tareas WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $id_parte = $row['id_parte'];

            $sql = "SELECT estado, id_tecnico FROM partes WHERE id = $id_parte";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $estado = $row['estado'];
                $id_tecnico = $row['id_tecnico'];

                if($userRol == 'TECNICO' && $id_tecnico != $userId){
                    return [
                        'status' => 'error',
                        'message' => 'No tienes permisos para eliminar una tarea'
                    ];
                }

                if($estado == 'CERRADO'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes eliminar una tarea en un parte cerrado'
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'Error al eliminar la tarea'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la tarea'
            ];
        }

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Tarea eliminada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la tarea'
            ];
        }
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'tareas';
        $id = $request['id'];
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar una tarea'
            ];
        }

        $sql = "SELECT id_parte FROM tareas WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $id_parte = $row['id_parte'];

            $sql = "SELECT estado, id_tecnico FROM partes WHERE id = $id_parte";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $estado = $row['estado'];
                $id_tecnico = $row['id_tecnico'];

                if($userRol == 'TECNICO' && $id_tecnico != $userId){
                    return [
                        'status' => 'error',
                        'message' => 'No tienes permisos para actualizar una tarea'
                    ];
                }

                if($estado == 'CERRADO'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar una tarea en un parte cerrado'
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'Error al actualizar la tarea'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la tarea'
            ];
        }

        $updated = Tools::update($conn, $tabla, $id, $data, $check);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Tarea actualizada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la tarea'
            ];
        }
    }

    static public function getByParteId($request){
        $conn = $_SESSION['db_connection'];
        $id_parte = $request['id_parte'];
        $tabla = 'tareas';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        $sql = "SELECT id_tecnico FROM partes WHERE id = $id_parte";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $id_tecnico = $row['id_tecnico'];

            if($userRol == 'TECNICO' && $id_tecnico != $userId){
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para obtener las tareas de un parte'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al obtener las tareas de un parte'
            ];
        }

        $sql = "SELECT * FROM $tabla WHERE id_parte = $id_parte";
        $result = $conn->query($sql);
        $tareas = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $tareas[] = $row;
            }
        }

        if($tareas){
            return [
                'status' => 'success',
                'message' => 'Tareas obtenidas correctamente',
                'data' => $tareas
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al obtener las tareas de un parte'
            ];
        }
    }
}