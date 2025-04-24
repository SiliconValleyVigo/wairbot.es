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

class Partes{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'partes';
        $check = [];
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];
        $id_motor = $data['id_motor'];

        if($userRol === 'CLIENTE' || $userRol === 'TECNICO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear un parte'
            ];
        }

        if($userRol === 'AGENTE'){
            $sql = "SELECT id_agente FROM motores WHERE id = $id_motor";
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
                'descripcion' => 'Instalación del motor',
                'tipo' => 'REPARAR',
                'referencia' => ''
            ]];
        }

        if($tipoParte === 'REPARACION'){
            //crear tarea de revisión id_parte, descripcion, tipo, referencia
            $tareas = [[
                'id_parte' => $idParte,
                'descripcion' => '',
                'tipo' => '',
                'referencia' => ''
            ]];
        }

        if($tipoParte === 'REVISION'){
            //crear tarea de revisión id_parte, descripcion, tipo, referencia
            $tareas = [
                [
                    'id_parte' => $idParte,
                    'descripcion' => 'Aceite Motor',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Filtro Aceite',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Aceite Cola',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Engrase General',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Engrase Transmisión',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Limpieza Motor',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Anodos',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Filstros Gasolina',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Impulsor',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Bujías',
                    'tipo' => '',
                    'referencia' => ''
                ],[
                    'id_parte' => $idParte,
                    'descripcion' => 'Sistema Eléctrico',
                    'tipo' => '',
                    'referencia' => ''
                ],
            ];
        }

        if($tareas){
            foreach($tareas as $key => $tarea){
                $id_parte = $tarea['id_parte'];
                $descripcion = $tarea['descripcion'];
                $tipo = $tarea['tipo'];
                $referencia = $tarea['referencia'];
            
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, '$descripcion', '$tipo', '$referencia')";
            
                // Suponiendo que $conn es tu conexión a la base de datos
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

        //comprobar si el estado del parte es CERRADO
        $sql = "SELECT estado FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $estado = $row['estado'];

            if($estado === 'CERRADO'){
                //comprobar si existe data['estado'] y si no es ABIERTO devolver error
                if(isset($data['estado']) && $data['estado'] !== 'ABIERTO' && $userRol !== 'ADMINISTRADOR'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar un parte cerrado'
                    ];
                }
                
            }
        }

        //si data contiene estado y estado es igual a CERRADO, actualizar el motor con la fecha de instalación si el tipo del parte es INSTALACION o con la fecha de próxima revisión y última revisión si el tipo del parte es REVISION
        if(isset($data['estado']) && $data['estado'] === 'CERRADO'){
            $sql = "SELECT id_motor, tipo FROM partes WHERE id = $id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            $id_motor = $row['id_motor'];
            $tipo = $row['tipo'];

            if($tipo === 'INSTALACION'){
                //actualizar la fecha de instalación del motor con la fecha del parte
                $hoy = date('Y-m-d');

                //la fecha de revisión es de 30 días después de la fecha de instalación
                $fecha_revision = date('Y-m-d', strtotime($hoy . ' + 30 days'));

                $sql = "UPDATE motores SET fecha_instalacion = '$hoy', fecha_proxima_revision = '$fecha_revision' WHERE id = $id_motor";
                $conn->query($sql);
            }

            if ($tipo === 'REVISION') {
                $sql = "SELECT fecha_instalacion FROM motores WHERE id = $id_motor";
                $result = $conn->query($sql);
            
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $fecha_instalacion = new DateTime($row['fecha_instalacion']);
                    $hoy = new DateTime(); // Fecha de revisión es hoy
            
                    $interval = $fecha_instalacion->diff($hoy);
                    $mesesTotales = $interval->y * 12 + $interval->m; // Convertir años a meses y sumar
            
                    if ($mesesTotales < 11) {
                        // Si hay menos de 11 meses, sumar 12 meses a la fecha de instalación
                        $fecha_proxima_revision = $fecha_instalacion->add(new DateInterval('P12M'));
                    } else {
                        // Si hay 11 meses o más, sumar 12 meses a la fecha actual
                        $fecha_proxima_revision = $hoy->add(new DateInterval('P12M'));
                    }
            
                    // Formatear fechas para SQL
                    $fecha_proxima_revision_str = $fecha_proxima_revision->format('Y-m-d');
                    $fecha_revision_str = $hoy->format('Y-m-d');
            
                    $sql = "UPDATE motores SET fecha_proxima_revision = '$fecha_proxima_revision_str', fecha_ultima_revision = '$fecha_revision_str' WHERE id = $id_motor";
                    $conn->query($sql);
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

    static public function getPartesByMotorId($request){
        $conn = $_SESSION['db_connection'];
        $id_motor = $request['id_motor'];

        $sql = "SELECT * FROM partes WHERE id_motor = $id_motor";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $partes = [];

            while($row = $result->fetch_assoc()){
                $partes[] = $row;
            }

            /*
            //obtener las tareas de cada parte
            foreach($partes as $key => $parte){
                $id_parte = $parte['id'];

                $sql = "SELECT * FROM tareas WHERE id_parte = $id_parte";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $tareas = [];

                    while($row = $result->fetch_assoc()){
                        $tareas[] = $row;
                    }

                    $partes[$key]['tareas'] = $tareas;
                }
            }*/

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
                if(isset($data['estado']) && $data['estado'] !== 'ABIERTO' && $userRol !== 'ADMINISTRADOR'){
                    return [
                        'status' => 'error',
                        'message' => 'No puedes actualizar un parte cerrado'
                    ];
                }
                
            }
        }

        $updated = Tools::update($conn, $tabla, $id, ['estado' => 'CERRADO']);

        //si es un parte de instalación, iniciar la garantía del motor, enviar emails y notificación al cliente
        //obtener el parte
        $sql = "SELECT * FROM partes WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $parte = $result->fetch_assoc();
            $id_motor = $parte['id_motor'];
            $tipo = $parte['tipo'];

            if($tipo === 'INSTALACION'){
                //iniciar la garantía del motor
                //actualizar la fecha de instalación del motor con la fecha del parte
                $hoy = date('Y-m-d');

                //la fecha de revisión es de 30 días después de la fecha de instalación
                $fecha_revision = date('Y-m-d', strtotime($hoy . ' + 30 days'));

                $sql = "UPDATE motores SET fecha_instalacion = '$hoy', fecha_proxima_revision = '$fecha_revision' WHERE id = $id_motor";
                $conn->query($sql);

                //programar email de revisión en tabla emails
                //obtener el id_cliente y el id_agente del motor
                $sql = "SELECT id_cliente, id_agente, codigo FROM motores WHERE id = $id_motor";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $row = $result->fetch_assoc();
                    $id_cliente = $row['id_cliente'];
                    $id_agente = $row['id_agente'];
                    $codigo = $row['codigo'];
                    $emailAdmin = "info@repamar.es";

                    //obtener los mails del cliente y del agente de la tabla usuarios
                    $sql = "SELECT email FROM usuarios WHERE id = $id_cliente OR id = $id_agente";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $emails = [];

                        while($row = $result->fetch_assoc()){
                            $emails[] = $row['email'];
                        }

                        $emails[] = $emailAdmin;

                        $fecha = $fecha_revision;

                        //mensaje predefinido para envíar en la fecha, con el código del motor y la fecha avisando de una posible perdida de garantía si no realiza la revisión de los 30 días, con el logo de la empresa y el contacto: Tel: +34 956 92 60 30 - +34 645 11 38 29 Email: info@repamar.es Dirección: Carril Vega La Preciada, 12
                        //https://repamar.es/img/tienda-nautica-repamar-logo-1648458066.jpg
                        $texto = "<p>Estimado cliente, le recordamos que su motor con código $codigo necesita una revisión en los próximos 30 días. Si no realiza la revisión, podría perder la garantía del motor.</p><p>Para más información, contacte con nosotros en:</p><p>Tel: +34 956 92 60 30 - +34 645 11 38 29</p><p>Email: info@repamar.es</p><p>Dirección: Carril Vega La Preciada, 12</p><img src='https://repamar.es/img/tienda-nautica-repamar-logo-1648458066.jpg' alt='Logo Repamar'>";

                        $texto = $conn->real_escape_string($texto);

                        $sql = "INSERT INTO emails (email, texto, fecha) VALUES ";
                        $values = '';

                        foreach($emails as $email){
                            $values .= "('$email', '$texto', '$fecha'),";
                        }

                        $values = rtrim($values, ',');
                        $sql .= $values;

                        $conn->query($sql);

                        //si hay error al insertar los emails, devolver error
                        if($conn->error){
                            return [
                                'status' => 'error',
                                'message' => 'Error al crear los emails',
                                'error' => $conn->error
                            ];
                        }
                    }
                }
            }

            if($tipo === 'REVISION'){
                //actualizar revisiones a partir de la fecha de instalación y la fecha de revisión, si entre la fecha de instalación y la de revisión hay menos de 11 meses, actualizar la fecha de próxima revisión a 12 meses después de la fecha de instalación y si hay más de 11 meses, actualizar la fecha de próxima revisión a 12 meses después de la fecha de revisión
                $sql = "SELECT fecha_instalacion, fecha_proxima_revision FROM motores WHERE id = $id_motor";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $row = $result->fetch_assoc();
                    $fecha_instalacion = $row['fecha_instalacion'];
                    $fecha_proxima_revision = $row['fecha_proxima_revision'];

                    $fecha_revision = $parte['fecha'];

                    $fecha_instalacion = new DateTime($fecha_instalacion);
                    $fecha_revision = new DateTime($fecha_revision);

                    $interval = $fecha_instalacion->diff($fecha_revision);

                    $meses = $interval->m;

                    if($meses < 11){
                        $fecha_proxima_revision = date('Y-m-d', strtotime($fecha_instalacion->format('Y-m-d') . ' + 12 months'));
                    }else{
                        $fecha_proxima_revision = date('Y-m-d', strtotime($fecha_revision->format('Y-m-d') . ' + 12 months'));
                    }

                    $sql = "UPDATE motores SET fecha_proxima_revision = '$fecha_proxima_revision' WHERE id = $id_motor";
                    $conn->query($sql);

                    //programar email de revisión en tabla emails
                    //obtener el id_cliente y el id_agente del motor
                    $sql = "SELECT id_cliente, id_agente, codigo FROM motores WHERE id = $id_motor";
                    $result = $conn->query($sql);

                    if($result->num_rows > 0){
                        $row = $result->fetch_assoc();
                        $id_cliente = $row['id_cliente'];
                        $id_agente = $row['id_agente'];
                        $codigo = $row['codigo'];
                        $emailAdmin = "info@repamar.es";

                        //obtener los mails del cliente y del agente de la tabla usuarios
                        $sql = "SELECT email FROM usuarios WHERE id = $id_cliente OR id = $id_agente";
                        $result = $conn->query($sql);

                        if($result->num_rows > 0){
                            $emails = [];

                            while($row = $result->fetch_assoc()){
                                $emails[] = $row['email'];
                            }

                            $emails[] = $emailAdmin;

                            $fecha = $fecha_proxima_revision;

                            //mensaje predefinido para envíar en la fecha, con el código del motor y la fecha avisando de una posible perdida de garantía si no realiza la revisión de los 30 días, con el logo de la empresa y el contacto: Tel: +34 956 92 60 30 - +34 645 11 38 29 Email: info@repamar.es Dirección: Carril Vega La Preciada, 12
                            //https://repamar.es/img/tienda-nautica-repamar-logo-1648458066.jpg

                            $texto = "<p>Estimado cliente, le recordamos que su motor con código $codigo necesita una revisión en los próximos 30 días. Si no realiza la revisión, podría perder la garantía del motor.</p><p>Para más información, contacte con nosotros en:</p><p>Tel: +34 956 92 60 30 - +34 645 11 38 29</p><p>Email:info@repamar.es </p><p>Dirección: Carril Vega La Preciada, 12</p><img src='https://repamar.es/img/tienda-nautica-repamar-logo-1648458066.jpg' alt='Logo Repamar'>";

                            $texto = $conn->real_escape_string($texto);
                            
                            $sql = "INSERT INTO emails (email, texto, fecha) VALUES ";
                            $values = '';

                            foreach($emails as $email){
                                $values .= "('$email', '$texto', '$fecha'),";
                            }

                            $values = rtrim($values, ',');
                            $sql .= $values;

                            $conn->query($sql);

                            //si hay error al insertar los emails, devolver error
                            if($conn->error){
                                return [
                                    'status' => 'error',
                                    'message' => 'Error al crear los emails',
                                    'error' => $conn->error
                                ];
                            }
                        }
                    }
                }
            }
        }

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

        $sql = "SELECT * FROM partes WHERE id_tecnico = $id_tecnico AND estado = 'ABIERTO'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            $partes = [];

            while($row = $result->fetch_assoc()){
                $partes[] = $row;
            }

            //obtener motor y cliente de cada parte
            foreach($partes as $key => $parte){
                $id_motor = $parte['id_motor'];

                $sql = "SELECT * FROM motores WHERE id = $id_motor";
                $result = $conn->query($sql);

                if($result->num_rows > 0){
                    $motor = $result->fetch_assoc();
                    $partes[$key]['motor'] = $motor;

                    $id_cliente = $motor['id_cliente'];

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
}