<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
motores: imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ulitma_revision, fecha_proxima_revision, aviso_revision_primer_mes (ENVIADO, OK), aviso_segunda_revision_primer_year (ENVIADO, OK), revision_anual (ENVIADO, OK)
partes: fecha, id_motor, tipo, notas, estado, id_tecnico
tareas: id_parte, descripcion, tipo, referencia
documentos: tipo, id_parte, url
solicitudes: id_motor, id_agente, estado
notificaciones: id_cliente, texto
publicidad: url, imagen, nombre, precio, estado
emails: email, texto, fecha
*/

class Notificaciones{
    static public function gestionAvisoRevisiones(){
        //obtener los motores que tengan fecha de proxima revision igual o menor a la fecha actual
        $conn = $_SESSION['db_connection'];
        $hoy = date('Y-m-d');

        $sql = "SELECT * FROM motores WHERE fecha_proxima_revision <= '$hoy'";
        $result = $conn->query($sql);

        $motores = [];
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $motores[] = $row;
            }
        }

        foreach($motores as $motor){
            $id_cliente = $motor['id_cliente'];
            $fecha_proxima_revision = $motor['fecha_proxima_revision'];
            $aviso_revision_primer_mes = $motor['aviso_revision_primer_mes'];
            $aviso_segunda_revision_primer_year = $motor['aviso_segunda_revision_primer_year'];
            $fecha_ultima_revision = $motor['fecha_ultima_revision'];
            $revision_anual = $motor['revision_anual'];

            if($fecha_proxima_revision <= $hoy && $aviso_revision_primer_mes != 'ENVIADO'){
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revision30DiasPrimerAviso($id_cliente, $emailCliente, $motor);
                }
            } elseif (date('Y-m-d', strtotime($fecha_proxima_revision. ' + 30 days')) <= $hoy && $aviso_revision_primer_mes != 'OK') {
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revision30DiasSegundoAviso($id_cliente, $emailCliente, $motor);
                }
            } elseif ($fecha_proxima_revision <= $hoy && $aviso_revision_primer_mes == 'OK' && $aviso_segunda_revision_primer_year != 'ENVIADO') {
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revisionAnualPrimerAviso($id_cliente, $emailCliente, $motor);
                }
            } elseif (date('Y-m-d', strtotime($fecha_proxima_revision. ' + 30 days')) <= $hoy && $aviso_segunda_revision_primer_year != 'OK') {
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revisionAnualSegundoAviso($id_cliente, $emailCliente, $motor);
                }
            } elseif ($fecha_proxima_revision <= $hoy && date('Y-m-d', strtotime($fecha_ultima_revision. ' + 1 year')) <= $hoy && $revision_anual != 'ENVIADO') {
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revisionAnualPrimerAviso($id_cliente, $emailCliente, $motor);
                }
            } elseif ($fecha_proxima_revision <= $hoy && date('Y-m-d', strtotime($fecha_ultima_revision. ' + 1 year + 30 days')) <= $hoy && $revision_anual != 'OK') {
                $emailCliente = self::getEmailClienteById($id_cliente);
        
                if($emailCliente){
                    self::revisionAnualSegundoAviso($id_cliente, $emailCliente, $motor);
                }
            }
        }
    }

    static public function enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto){
        $conn = $_SESSION['db_connection'];
        $from = 'info@repamar.es';

        $to = $emailCliente;
        $subject = 'Notificación Repamar';
        $message = $texto;
        $headers = 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);

        $texto = $conn->real_escape_string($texto);
        $sql = "INSERT INTO notificaciones (id_cliente, texto) VALUES ($id_cliente, '$texto')";
        $conn->query($sql);

        return true;
    }

    static public function getEmailClienteById($id_cliente){
        $conn = $_SESSION['db_connection'];

        $sql = "SELECT email FROM usuarios WHERE id = $id_cliente";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            return $row['email'];
        }

        return false;
    }

    ///////////////////////////////////////////////////////////////////////////////////
    static public function alCrearCliente($emailCliente){
        $texto = "Texto de prueba alCrearCliente $emailCliente";

        //obtener el id del cliente por su email
        $conn = $_SESSION['db_connection'];

        $sql = "SELECT id FROM usuarios WHERE email = '$emailCliente'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $id_cliente = $row['id'];

            self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
        }
    }

    static public function alCrearParteInstalacion($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCrearParteInstalacion $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    static public function alCerrarParteInstalacion($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCerrarParteInstalacion $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    static public function alCrearParteRevision($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCrearParteRevision $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    static public function alCerrarParteRevision($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCerrarParteRevision $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    static public function alCrearParteReparacion($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCrearParteReparacion $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    static public function alCerrarParteReparacion($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba alCerrarParteReparacion $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);
    }

    ////////////////////////////////////////////////////////////////////////////////
    static public function revision30DiasPrimerAviso($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba revision30DiasPrimerAviso $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);

        $motor_id = $motor['id'];
        $conn = $_SESSION['db_connection'];

        $sql = "UPDATE motores SET aviso_revision_primer_mes = 'ENVIADO' WHERE id = $motor_id";
        $conn->query($sql);

        return true;
    }

    static public function revision30DiasSegundoAviso($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba revision30DiasSegundoAviso $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);

        $motor_id = $motor['id'];
        $conn = $_SESSION['db_connection'];

        $sql = "UPDATE motores SET aviso_revision_primer_mes = 'OK' WHERE id = $motor_id";
        $conn->query($sql);

        return true;
    }

    static public function revisionAnualPrimerAviso($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba revisionAnualPrimerAviso $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);

        $motor_id = $motor['id'];
        $conn = $_SESSION['db_connection'];

        $sql = "UPDATE motores SET aviso_segunda_revision_primer_year = 'ENVIADO' WHERE id = $motor_id";
        $conn->query($sql);
    }

    static public function revisionAnualSegundoAviso($id_cliente, $emailCliente, $motor){
        $texto = "Texto de prueba revisionAnualSegundoAviso $emailCliente $id_cliente";

        self::enviarMailAndCrearNotificacion($id_cliente, $emailCliente, $texto);

        $motor_id = $motor['id'];
        $conn = $_SESSION['db_connection'];

        $sql = "UPDATE motores SET aviso_segunda_revision_primer_year = 'OK' WHERE id = $motor_id";
        $conn->query($sql);
    }

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