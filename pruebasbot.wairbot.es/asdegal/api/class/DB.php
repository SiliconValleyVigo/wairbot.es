<?php
/* 
TABLAS

Usuarios:
id
rol (ADMINISTRADOR, COORDINADOR, VOLUNTARIO)
imagen
nombre
referencia
identificacion
direccion
telefono_contacto
telefono_whatsapp
email
password
estado (ACTIVO, INACTIVO, BAJA)
localizacion
preferencias (JSON)
notas
id_coordinador

Beneficiarios:
id
nombre
referencia
identificacion
direccion
telefono_fijo
telefono_movil
email
nombre_contacto_1
contacto_1
nombre_contacto_2
contacto_2
notas
id_coordinador

Acciones:
id
id_coordinador
id_voluntario
id_beneficiario
fecha
descripcion
observaciones
confirmacion_cita
estado (PENDIENTE, FINALIZADA)
tiempo

Selects
nombre
tabla
menu: 
(descripcion, acciones, [‘Cita Médica’])
(preferencias, usuarios, [‘Tercera Edad’, 'Persona con Discapacidad'])
*/

/*
CAMBIOS EN BENEFICIARIOS
Fecha de nacimiento
Edad (calculado por fecha de nacimiento)
procedencia
Documento Informe Asistencia (documento)
Informe de Asistencia (texto)
estado
*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos  
        $servername = getenv('DB_SERVER_DEFAULT_HOST');
        $username = getenv('ASDEGAL_DB_USER');
        $password = getenv('ASDEGAL_DB_PASSWORD');
        $database = getenv('ASDEGAL_DB_NAME');
        
        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);
            
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }
        
        
        // Consulta para verificar si existe la tabla clientes
        $table_check_query = "SHOW TABLES LIKE 'usuarios'";
        $table_check_result = $conn->query($table_check_query);
        
        if (!$table_check_result->num_rows > 0) {
            self::createTablaUsuarios($conn);
            self::createTablaBeneficiarios($conn);
            self::createTablaAcciones($conn);
            self::createTablaSelects($conn);
            self::createTablaConfiguracionTablas($conn);
        }

        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(255),
            imagen VARCHAR(255),
            nombre VARCHAR(255),
            referencia VARCHAR(255),
            identificacion VARCHAR(255),
            direccion VARCHAR(255),
            telefono_fijo VARCHAR(255),
            telefono_movil VARCHAR(255),
            email VARCHAR(255),
            password VARCHAR(255),
            estado VARCHAR(255),
            horas_disponibles VARCHAR(255),
            dias_disponibles VARCHAR(255),
            delegacion VARCHAR(255),
            localizacion VARCHAR(255),
            preferencias JSON,
            notas LONGTEXT,
            id_coordinador INT(12),
            fecha_alta DATE,
            fecha_baja DATE,
            causa_baja VARCHAR(255),
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
            echo json_encode([
                "status" => "success",
                "message" => "Usuario administrador creado con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear el usuario administrador: " . $conn->error
            ]);
        }
    }

    static public function createTablaBeneficiarios($conn){
        $sql = "CREATE TABLE beneficiarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            referencia VARCHAR(255),
            identificacion VARCHAR(255),
            direccion VARCHAR(255),
            telefono_fijo VARCHAR(255),
            telefono_movil VARCHAR(255),
            email VARCHAR(255),
            nombre_contacto_1 VARCHAR(255),
            contacto_1 VARCHAR(255),
            nombre_contacto_2 VARCHAR(255),
            contacto_2 VARCHAR(255),
            fecha_nacimiento DATE,
            procedencia VARCHAR(255),
            documento_informe_asistencia VARCHAR(255),
            informe_asistencia LONGTEXT,
            estado VARCHAR(255),
            notas LONGTEXT,
            id_coordinador INT(12),
            fecha_alta DATE,
            fecha_baja DATE,
            causa_baja VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla beneficiarios creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla beneficiarios: " . $conn->error
            ]);
        }
    }

    static public function createTablaAcciones($conn){
        $sql = "CREATE TABLE acciones (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_coordinador INT(12),
            id_voluntario INT(12),
            id_beneficiario INT(12),
            fecha DATE,
            hora VARCHAR(255),
            periodo VARCHAR(255),
            lugar1 VARCHAR(255),
            lugar2 VARCHAR(255),
            descripcion LONGTEXT,
            observaciones LONGTEXT,
            confirmacion_cita VARCHAR(255),
            confirmacion_voluntario VARCHAR(255),
            notas LONGTEXT,
            estado VARCHAR(255),
            tiempo VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla acciones creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla acciones: " . $conn->error
            ]);
        }
    }

    static public function createTablaSelects($conn){
        $sql = "CREATE TABLE selects (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            tabla VARCHAR(255),
            menu VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla selects sin devolver nada
        $conn->query($sql);

        // Insertar datos en la tabla selects
        $nombre = 'descripcion';
        $tabla = 'acciones';
        $menu = json_encode(['Cita Médica']);
        $sql = "INSERT INTO selects (nombre, tabla, menu) VALUES ('$nombre', '$tabla', '$menu')";

        $conn->query($sql);

        $nombre = 'preferencias';
        $tabla = 'usuarios';
        $menu = json_encode(['Tercera Edad', 'Persona con Discapacidad']);
        $sql = "INSERT INTO selects (nombre, tabla, menu) VALUES ('$nombre', '$tabla', '$menu')";

        $conn->query($sql);
    }

    static public function createTablaConfiguracionTablas($conn){
        $sql = "CREATE TABLE IF NOT EXISTS configuracion_tablas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre_tabla VARCHAR(255),
            grid_or_table VARCHAR(255),
            row_per_page INT(12),
            filtros JSON,
            order_by LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla configuracion_tablas sin devolver nada
        $conn->query($sql);

        // Insertar datos en la tabla configuracion_tablas
        //$nombre_tabla = 'acciones';
        $gridOrTable = 'grid';
        $row_per_page = 24;
        $filtros = json_encode([]);
        $order_by = "";

        $tablas = ['acciones', 'acciones_periodicas', 'beneficiarios', 'beneficiarios_baja', 'coordinadores', 'voluntarios', 'voluntarios_baja'];

        $sql = "INSERT INTO configuracion_tablas (nombre_tabla, grid_or_table, row_per_page, filtros, order_by) VALUES ";
        $sql .= implode(", ", array_map(function($tabla) use ($gridOrTable, $row_per_page, $filtros, $order_by){
            return "('$tabla', '$gridOrTable', $row_per_page, '$filtros', '$order_by')";
        }, $tablas));

        $conn->query($sql);
    }
}