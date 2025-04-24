<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, 
partes: fecha, id_maquina, tipo, notas, estado
tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente
documentos: id_parte, url
*/

class Documentos{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'documentos';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];
        $id_parte = $data['id_parte'];

        //si userRol es CLIENTE, no puede crear documentos
        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un documento'
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
                        'message' => 'No tienes permisos para crear un documento'
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
                                'message' => 'No tienes permisos para crear un documento'
                            ];
                        }
                    }else{
                        return [
                            'status' => 'error',
                            'message' => 'No tienes permisos para crear un documento'
                        ];
                    }
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para crear un documento'
                ];
            }
        }

        //si el parte está cerrado, no se puede crear un documento
        $sql = "SELECT estado FROM partes WHERE id = $id_parte";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado == 'CERRADO'){
                return [
                    'status' => 'error',
                    'message' => 'No puedes crear un documento en un parte cerrado'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'No puedes crear un documento en un parte cerrado'
            ];
        }

        //obtener de data el documento donde esta nombre y base64
        $documento = $data['documento'];
        $nombre = $documento['nombre'];
        $base64 = $documento['base64'];

        $documento = Tools::fileToUrl($base64, $nombre);

        if($documento){
            $data['documento'] = $documento;
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al subir el documento'
            ];
        }

        //eliminar documento de data y añadir url
        unset($data['documento']);

        $data['url'] = $documento;

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                return [
                    'status' => 'success',
                    'message' => 'Documento creado correctamente',
                    'data' => $created
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'Error al crear el documento',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el documento',
                'error' => $created
            ];
        }
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'documentos';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un documento'
            ];
        }

        $sql = "SELECT id_parte FROM documentos WHERE id = $id";
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
                        'message' => 'No tienes permisos para eliminar un documento'
                    ];
                }

                if($estado == 'CERRADO'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes eliminar un documento en un parte cerrado'
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'Error al eliminar el documento'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el documento'
            ];
        }

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Documento eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el documento'
            ];
        }
    }

    //actualizar solo comentarios
    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $id = $request['id'];
        $tabla = 'documentos';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        if($userRol == 'CLIENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar un documento'
            ];
        }

        $sql = "SELECT id_parte FROM documentos WHERE id = $id";
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
                        'message' => 'No tienes permisos para actualizar un documento'
                    ];
                }

                if($estado == 'CERRADO'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar un documento en un parte cerrado'
                    ];
                }
            }else{
                return [
                    'status' => 'error',
                    'message' => 'Error al actualizar el documento'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el documento'
            ];
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Documento actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el documento'
            ];
        }
    }

    static public function getByParteId($request){
        $conn = $_SESSION['db_connection'];
        $id_parte = $request['id_parte'];
        $tabla = 'documentos';
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
                    'message' => 'No tienes permisos para obtener los documentos de un parte'
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al obtener los documentos de un parte'
            ];
        }

        $sql = "SELECT * FROM $tabla WHERE id_parte = $id_parte";
        $result = $conn->query($sql);
        $documentos = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $documentos[] = $row;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Documentos obtenidos correctamente',
            'data' => $documentos
        ];
    }
}