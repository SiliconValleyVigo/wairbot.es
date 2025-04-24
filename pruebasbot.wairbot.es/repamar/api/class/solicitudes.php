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

class Solicitudes{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $id_motor = $request['id_motor'];
        $id_agente = $_SESSION['user_id'];
        $rol = $_SESSION['user_rol'];

        if($rol !== 'AGENTE'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear una solicitud'
            ];
        }

        $sql = "INSERT INTO solicitudes (id_motor, id_agente, estado) VALUES ($id_motor, $id_agente, 'PENDIENTE')";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Solicitud creada correctamente'
        ];
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $id_solicitud = $request['id_solicitud'];
        $estado = $request['estado'];
        $rol = $_SESSION['user_rol'];

        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar una solicitud'
            ];
        }

        $sql = "UPDATE solicitudes SET estado = '$estado' WHERE id = $id_solicitud";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Solicitud actualizada correctamente'
        ];
    }

    //obtener todas las solicitudes separadas por sin estado y con estado
    static public function getAll($usuario){
        $conn = $_SESSION['db_connection'];
        $rol = $usuario['rol'];

        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para obtener las solicitudes'
            ];
        }

        $solicitudes = [];
        $sql = "SELECT * FROM solicitudes";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $solicitudes[] = $row;
            }
        }


        //si estado está vacío o null se añade al array estadoVacio sino se añade al array estadoRelleno
        $estadoPendiente = [];
        foreach($solicitudes as $solicitud){
            if($solicitud['estado'] === 'PENDIENTE'){
                $estadoPendiente[] = $solicitud;
            }
        }

        //obtener los motores y el agente de cada solicitud
        foreach($estadoPendiente as $key => $solicitud){
            $id_motor = $solicitud['id_motor'];
            $id_agente = $solicitud['id_agente'];

            $sql = "SELECT * FROM motores WHERE id = $id_motor";
            $result = $conn->query($sql);
            $motor = $result->fetch_assoc();

            $sql = "SELECT * FROM usuarios WHERE id = $id_agente";
            $result = $conn->query($sql);
            $agente = $result->fetch_assoc();

            $estadoPendiente[$key]['motor'] = $motor;
            $estadoPendiente[$key]['agente'] = $agente;
        }

        return [
            'status' => 'success',
            'message' => 'Solicitudes obtenidas correctamente',
            'data' => $estadoPendiente
        ];
    }
}