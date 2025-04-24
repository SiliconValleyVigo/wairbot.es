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

class Notificaciones{
    static public function create(){
        //obtener todos los elementos de la tabla emails
        $conn = $_SESSION['db_connection'];

        $emailsPendientes = [];
        $sql = "SELECT * FROM emails";

        $result = $conn->query($sql);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $emailsPendientes[] = $row;
            }
        }


        //obtener los ids de usuario de los emailsPendientes por su email y tengan el rol de CLIENTE
        $idsUsuarios = [];
        foreach($emailsPendientes as $email){
            $mail = $email['email'];

            $sql = "SELECT id FROM usuarios WHERE email = '$mail' AND rol = 'CLIENTE'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                $idsUsuarios[] = [
                    'id' => $row['id'],
                    'email' => $mail,
                    'texto' => $email['texto'],
                ];
            }
        }

        //crear notificaciones con los ids de usuario
        foreach($idsUsuarios as $usuario){
            $id = $usuario['id'];
            $texto = $conn->real_escape_string($usuario['texto']);
            $sql = "INSERT INTO notificaciones (id_cliente, texto) VALUES ($id, '$texto')";
            $conn->query($sql);
        }

        //enviar los mails que sobrepasen la fecha de hoy con cabeceras para utf8
        $hoy = date('Y-m-d');
        $sql = "SELECT * FROM emails WHERE fecha < '$hoy'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $email = $row['email'];
                $texto = $row['texto'];
                $fecha = $row['fecha'];
                $from = 'info@repamar.es';

                $to = $email;
                $subject = 'Notificación';
                $message = $texto;
                $headers = 'From: ' . $from . "\r\n" .
                    'Reply-To: ' . $from . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);

                $sql = "DELETE FROM emails WHERE email = '$email' AND fecha = '$fecha'";
                $conn->query($sql);
            }
        }

        return true;
    }

    static public function getByClienteId(){
        $conn = $_SESSION['db_connection'];
        $user_id = $_SESSION['user_id'];

        $sql = "SELECT * FROM notificaciones WHERE id_cliente = $user_id";
        $result = $conn->query($sql);

        $notificaciones = [];

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $notificaciones[] = $row;
            }
        }

        return [
            'sql' => $sql,
            'status' => 'success',
            'data' => $notificaciones
        ];
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $user_id = $_SESSION['user_id'];
        $ids = $request['ids'];

        foreach($ids as $id){
            $sql = "DELETE FROM notificaciones WHERE id = $id AND id_cliente = $user_id";
            $conn->query($sql);
        }

        return [
            'status' => 'success',
            'message' => 'Notificaciones eliminadas'
        ];
    }
}