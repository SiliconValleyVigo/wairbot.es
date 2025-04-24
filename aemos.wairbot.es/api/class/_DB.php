<?php
/* 
alumnos: id, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehiculo, mensajes_enviados, referencia, timestamp

alumnos_baja: id, id_original, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehiculo, mensajes_enviados, referencia, timestamp

cursos: id, nombre (unico), horas, financiacion(NORMAL, SEPE), previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, timestamp

cursos_baja: id, id_original, nombre, horas, financiacion, previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, timestamp

clases: id, id_curso, fecha, hora_inicio, hora_fin, lugar, observaciones, referencia, timestamp

inscripciones: id, id_alumno, id_curso, estado(PROPUESTO, SOLICITADO, ACEPTADO, RECHAZADO, FINALIZADO), timestamp

cita_previa: id, id_alumno, disponibilidad, timestamp

mensajes_programados: id, id_curso, numero_mensajes, intervalo_dias, fecha_primer_mensaje, hora_envio, mensaje, observaciones_internas, referencia, timestamp

alumnos_cursos: id, id alumno, id curso, estado(ACTIVO, EN PREPARACION, FINALIZADO), timestamp

acciones_formativas: id, id alumno, id curso, numero_clase, asistio(SI, NO), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

selects: id, nombre, tabla, menu, timestamp

*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos  
        $servername = getenv('DB_SERVER_DEFAULT_HOST');
        $username = getenv('AEMOS_DB_USER');
        $password = getenv('AEMOS_DB_PASSWORD');
        $database = getenv('AEMOS_DB_NAME');
        
        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);
            
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }

        /*
        //comprobar si existe una tabla con el nombre usuarios
        $sql = "SHOW TABLES LIKE 'usuarios'";
        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            self::createTablas($conn);
        }*/
        
        return $conn;
    }

    static public function initDbWairbot(){
        // Datos de conexión a la base de datos  
        $servername = getenv('WAIRBOT_DB_HOST');
        $username = getenv('WAIRBOT_DB_USER');
        $password = getenv('WAIRBOT_DB_PASSWORD');
        $database = getenv('WAIRBOT_DB_NAME');

        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);

        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }

        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(255),
            email VARCHAR(255),
            password VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla usuarios creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla usuarios: " . $conn->error
            ]);
        }

        // Insertar usuario administrador
        $defaultRol = getenv('DB_DEFAULT_ROL');
        $defaultEmail = getenv('DB_DEFAULT_EMAIL');
        $defaultPassword = getenv('DB_DEFAULT_PASSWORD');
        $sql = "INSERT INTO usuarios (rol, email, password) VALUES ('$defaultRol', '$defaultEmail', '$defaultPassword')";

        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear el usuario administrador: " . $conn->error
            ]);
        }
    }

    static public function createTablaAlumnos($conn){
        $sql = "CREATE TABLE alumnos (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            numero VARCHAR(255),
            apellidos VARCHAR(255),
            nombre VARCHAR(255),
            dni VARCHAR(255),
            telefono VARCHAR(255),
            fecha_nacimiento DATE,
            fecha_seleccion DATE,
            fecha_baja DATE,
            persona_sustituta VARCHAR(255),
            email VARCHAR(255),
            sexo VARCHAR(255),
            colectivo VARCHAR(255),
            grupo VARCHAR(255),
            lugar VARCHAR(255),
            trabajo VARCHAR(255),
            observaciones LONGTEXT,
            numero_cuenta VARCHAR(255),
            orientador VARCHAR(255),
            carnet_conducir VARCHAR(255),
            vehiculo VARCHAR(255),
            mensajes_enviados LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla alumnos: " . $conn->error
            ]);
        }
    }

    static public function createTablaAlumnosBaja($conn){
        $sql = "CREATE TABLE alumnos_baja (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_original INT(12),
            numero VARCHAR(255),
            apellidos VARCHAR(255),
            nombre VARCHAR(255),
            dni VARCHAR(255),
            telefono VARCHAR(255),
            fecha_nacimiento DATE,
            fecha_seleccion DATE,
            fecha_baja DATE,
            persona_sustituta VARCHAR(255),
            email VARCHAR(255),
            sexo VARCHAR(255),
            colectivo VARCHAR(255),
            grupo VARCHAR(255),
            lugar VARCHAR(255),
            trabajo VARCHAR(255),
            observaciones LONGTEXT,
            numero_cuenta VARCHAR(255),
            orientador VARCHAR(255),
            carnet_conducir VARCHAR(255),
            vehiculo VARCHAR(255),
            mensajes_enviados LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla alumnos_baja: " . $conn->error
            ]);
        }
    }

    static public function createTablaCursos($conn){
        $sql = "CREATE TABLE cursos (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            horas INT(12),
            financiacion VARCHAR(255),
            previsto VARCHAR(255),
            asistencias INT(12),
            ausencias INT(12),
            horas_formacion INT(12),
            balance INT(12),
            numero_clases INT(12),
            fechas LONGTEXT,
            horario VARCHAR(255),
            lugar  LONGTEXT,
            observaciones_publicas LONGTEXT,
            observaciones_internas LONGTEXT,
            descripcion LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla cursos: " . $conn->error
            ]);
        }
    }

    static public function createTablaCursosBaja($conn){
        $sql = "CREATE TABLE cursos_baja (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_original INT(12),
            nombre VARCHAR(255),
            horas INT(12),
            financiacion VARCHAR(255),
            previsto VARCHAR(255),
            asistencias INT(12),
            ausencias INT(12),
            horas_formacion INT(12),
            balance INT(12),
            numero_clases INT(12),
            fechas LONGTEXT,
            horario VARCHAR(255),
            lugar  LONGTEXT,
            observaciones_publicas LONGTEXT,
            observaciones_internas LONGTEXT,
            descripcion LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla cursos_baja: " . $conn->error
            ]);
        }
    }

    static public function createTablaClases($conn){
        $sql = "CREATE TABLE clases (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_curso INT(12),
            fecha DATE,
            hora_inicio TIME,
            hora_fin TIME,
            lugar VARCHAR(255),
            observaciones LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla clases: " . $conn->error
            ]);
        }
    }

    static public function createTablaInscripciones($conn){
        $sql = "CREATE TABLE inscripciones (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_alumno INT(12),
            id_curso INT(12),
            estado VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla inscripciones: " . $conn->error
            ]);
        }
    }

    static public function createTablaCitaPrevia($conn){
        $sql = "CREATE TABLE cita_previa (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_alumno INT(12),
            disponibilidad LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla cita_previa: " . $conn->error
            ]);
        }
    }

    static public function createTablaMensajesProgramados($conn){
        $sql = "CREATE TABLE mensajes_programados (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_curso INT(12),
            numero_mensajes INT(12),
            intervalo_dias INT(12),
            fecha_primer_mensaje DATE,
            hora_envio TIME,
            mensaje LONGTEXT,
            observaciones_internas LONGTEXT,
            referencia LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla mensajes_programados: " . $conn->error
            ]);
        }
    }

    static public function createTablaAlumnosCursos($conn){
        $sql = "CREATE TABLE alumnos_cursos (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_alumno INT(12),
            id_curso INT(12),
            estado VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla alumnos_cursos: " . $conn->error
            ]);
        }
    }

    static public function createTablaAccionesFormativas($conn){
        $sql = "CREATE TABLE acciones_formativas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_alumno INT(12),
            id_curso INT(12),
            numero_clase INT(12),
            asistio BOOLEAN,
            notas LONGTEXT,
            curso_igualdad_previo VARCHAR(255),
            entrega_de_material VARCHAR(255),
            alumno_trabajando VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla acciones_formativas: " . $conn->error
            ]);
        }
    }

    static public function createTablas($conn){
        if (
            self::createTablaUsuarios($conn) &&
            self::createTablaAlumnos($conn) &&
            self::createTablaAlumnosBaja($conn) &&
            self::createTablaCursos($conn) &&
            self::createTablaCursosBaja($conn) &&
            self::createTablaInscripciones($conn) &&
            self::createTablaCitaPrevia($conn) &&
            self::createTablaMensajesProgramados($conn) &&
            self::createTablaAlumnosCursos($conn) &&
            self::createTablaAccionesFormativas($conn) &&
            self::createTablaSelects($conn) &&
            self::createTablaConfiguracionTablas($conn)
        ) {
            
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear las tablas"
            ]);
        }
    }

    #######################################################################################################
    /////////////////////////////////////// TABLAS DE CONFIGURACION ///////////////////////////////////////
    #######################################################################################################

    static public function createTablaSelects($conn){
        $sql = "CREATE TABLE selects (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            tabla VARCHAR(255),
            menu JSON,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla selects sin devolver nada
        $conn->query($sql);
    }

    static public function createTablaConfiguracionTablas($conn){
        $sql = "CREATE TABLE IF NOT EXISTS configuracion_tablas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre_tabla VARCHAR(255),
            grid_or_table VARCHAR(255),
            row_per_page INT(12),
            filtros LONGTEXT,
            columnas LONGTEXT,
            order_by LONGTEXT,
            visualizacion LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla configuracion_tablas sin devolver nada
        $conn->query($sql);

        // Insertar datos en la tabla configuracion_tablas
        $gridOrTable = 'grid';
        $row_per_page = 24;
        $filtros = json_encode([]);
        $order_by = "";

        ////////////////////////// ¡¡¡¡¡ ESTO ESTA MAL !!!!////////////////////////////
        //// El nombre de las tablas debería ser el nombre de la clase en minúsculas ///////
        $tablas = ['inscripciones', 'cita_previa', 'mensajes_programados', 'alumnos_cursos', 'acciones_formativas'];//CAMBIAR POR LAS TABLAS QUE SE NECESITEN

        $sql = "INSERT INTO configuracion_tablas (nombre_tabla, grid_or_table, row_per_page, filtros, order_by) VALUES ";
        $sql .= implode(", ", array_map(function($tabla) use ($gridOrTable, $row_per_page, $filtros, $order_by){
            return "('$tabla', '$gridOrTable', $row_per_page, '$filtros', '$order_by')";
        }, $tablas));

        $conn->query($sql);
    }
}

    
