<?php
/* 
CLIENTES: id, nombre, telefono, cartera, bote, timestamp
APUESTAS: id, id_cliente, juego, imagen, fecha, timestamp
PREMIOS: id, id_cliente, id_apuesta, juego, importe, timestamp
*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos  
        $servername = "wairbot.es";
        $username = "loterias_37_clientes";
        $password = "6xH7b14o_";
        $database = "loterias_37_clientes";
        
        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);
            
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }
        
        /*
        // Consulta para verificar si existe la tabla usuarios
        $table_check_query = "SHOW TABLES LIKE 'usuarios'";
        $table_check_result = $conn->query($table_check_query);
        
        if (!$table_check_result->num_rows > 0) {
            self::createTablaUsuarios($conn);
            self::createTablaApuestas($conn);
            self::createTablaClientes($conn);
            self::createTablaPremios($conn);
            self::createTablaSelects($conn);
            self::createTablaConfiguracionTablas($conn);
        }*/
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

    static public function createTablaClientes($conn){
        $sql = "CREATE TABLE clientes (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            telefono VARCHAR(255),
            cartera TEXT,
            bote TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla clientes creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla clientes: " . $conn->error
            ]);
        }
    }

    static public function createTablaApuestas($conn){
        $sql = "CREATE TABLE apuestas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT(12),
            juego VARCHAR(255),
            imagen TEXT,
            fecha DATE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla apuestas creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla apuestas: " . $conn->error
            ]);
        }
    }

    static public function createTablaPremios($conn){
        $sql = "CREATE TABLE premios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT(12),
            id_apuesta INT(12),
            juego VARCHAR(255),
            importe TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla premios creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla premios: " . $conn->error
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
            menu VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla selects sin devolver nada
        $conn->query($sql);

        // Insertar datos en la tabla selects
        $nombre = 'juegos';
        $tabla = 'apuestas';
        $menu = json_encode(['EUROMILLONES', 'PRIMITIVA', 'BONOLOTO', 'El GORDO', 'EURO DREAMS', 'LA QUINIELA', 'LOTOTURF', 'EL QUINIGOL', 'QUINTUPLE PLUS']);
        $sql = "INSERT INTO selects (nombre, tabla, menu) VALUES ('$nombre', '$tabla', '$menu')";

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

        $tablas = ['clientes', 'apuestas', 'premios'];//CAMBIAR POR LAS TABLAS QUE SE NECESITEN

        $sql = "INSERT INTO configuracion_tablas (nombre_tabla, grid_or_table, row_per_page, filtros, order_by) VALUES ";
        $sql .= implode(", ", array_map(function($tabla) use ($gridOrTable, $row_per_page, $filtros, $order_by){
            return "('$tabla', '$gridOrTable', $row_per_page, '$filtros', '$order_by')";
        }, $tablas));

        $conn->query($sql);
    }
}