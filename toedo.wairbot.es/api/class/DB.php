<?php

/*
    usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
    maquinas: imagen, maquina, marca, modelo, codigo, observaciones, id_cliente, id_agente, visto, tipo, n_serie, foto_2, foto_3, foto_4
    partes: fecha, id_maquina, tipo, notas, estado
    tareas: id_parte, descripcion, tipo, referencia, enviado_cliente, mensajes_cliente, aprobado_cliente
    documentos: id_parte, url
*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos
        /*
        $servername = getenv('WAIRBOT_DB_HOST');
        $username = getenv('REPAMAR_MAQUINAS_DB_USER');
        $password = getenv('REPAMAR_MAQUINAS_DB_PASSWORD');
        $database = getenv('REPAMAR_MAQUINAS_DB_NAME');*/

        
        $servername = getenv('REPAIR_DB_HOST');
        $username = getenv('REPAIR_DB_USER');
        $password = getenv('REPAIR_DB_PASSWORD');
        $database = getenv('REPAIR_DB_NAME');
        

        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);
            
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }

        /*
        self::createTableSelects($conn);

        //si la tabla maquinas no tiene la columna n_serie añadir las columnas n_serie, foto_2, foto_3, foto_4 y tipo añadirlas
        $sql = "SHOW COLUMNS FROM maquinas LIKE 'n_serie'";
        $result = $conn->query($sql);
        if ($result->num_rows == 0) {
            $sql = "ALTER TABLE maquinas ADD n_serie VARCHAR(255)";
            $conn->query($sql);
            $sql = "ALTER TABLE maquinas ADD foto_2 VARCHAR(255)";
            $conn->query($sql);
            $sql = "ALTER TABLE maquinas ADD foto_3 VARCHAR(255)";
            $conn->query($sql);
            $sql = "ALTER TABLE maquinas ADD foto_4 VARCHAR(255)";
            $conn->query($sql);
            $sql = "ALTER TABLE maquinas ADD tipo VARCHAR(255)";
            $conn->query($sql);
            $sql = "ALTER TABLE partes ADD tipo_urgencia VARCHAR(255)";
            $conn->query($sql);
        }*/
        
        /*
        // Consulta para verificar si existe la tabla clientes
        $table_check_query = "SHOW TABLES LIKE 'usuarios'";
        $table_check_result = $conn->query($table_check_query);
        
        if (!$table_check_result->num_rows > 0) {
            self::createTablaUsuarios($conn);
            self::createTablaMaquinas($conn);
            self::createTablaPartes($conn);
            self::createTablaTareas($conn);
            self::createTablaDocumentos($conn);
            self::createTableSelects($conn);
        }*/
        
        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(30),
            imagen VARCHAR(255),
            nombre VARCHAR(255),
            referencia VARCHAR(255),
            identificacion VARCHAR(30),
            direccion VARCHAR(255),
            telefono_contacto VARCHAR(30),
            telefono_whatsapp VARCHAR(30),
            email VARCHAR(50),
            password VARCHAR(255),
            id_agente INT(12),
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
        $defaultName = getenv('DB_ADMIN_NAME');
        $defaultEmail = getenv('DB_WAIRBOT_ADMIN');
        $defaultPassword = getenv('DB_WAIRBOT_PASSWORD');
        $sql = "INSERT INTO usuarios (rol, nombre, email, password) VALUES ('$defaultRol', '$defaultName', '$defaultEmail', '$defaultPassword')";
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

    static public function createTablaMaquinas($conn){
        $sql = "CREATE TABLE maquinas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            imagen VARCHAR(255),
            maquina VARCHAR(255),
            marca VARCHAR(255),
            modelo VARCHAR(255),
            codigo VARCHAR(255),
            observaciones TEXT,
            id_cliente INT(12),
            id_agente INT(12),
            visto VARCHAR(30),
            tipo VARCHAR(30),
            n_serie VARCHAR(255),
            foto_2 VARCHAR(255),
            foto_3 VARCHAR(255),
            foto_4 VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla Maquinas creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla maquinas: " . $conn->error
            ]);
        }
    }

    static public function createTablaPartes($conn){
        $sql = "CREATE TABLE partes (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fecha DATE,
            id_maquina INT(12),
            tipo VARCHAR(255),
            notas TEXT,
            estado VARCHAR(30),
            id_tecnico INT(12),
            enviado_cliente varchar(30),
            mensajes_cliente JSON,
            aprobado_cliente varchar(30),
            tiempos JSON,
            firma VARCHAR(255),
            tipo_urgencia VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla partes creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla partes: " . $conn->error
            ]);
        }
    }

    static public function createTablaTareas($conn){
        $sql = "CREATE TABLE tareas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_parte INT(12),
            descripcion TEXT,
            tipo VARCHAR(30),
            referencia VARCHAR(255),
            coste VARCHAR(30),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla tareas creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla tareas: " . $conn->error
            ]);
        }
    }

    static public function createTablaDocumentos($conn){
        $sql = "CREATE TABLE documentos (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(30),
            id_parte INT(12),
            url VARCHAR(255),
            comentarios LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla documentos creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla documentos: " . $conn->error
            ]);
        }
    }

    /* 
        ESTADOS PARTE:
        Abierto
        Presupuesto
        En Reparación
        Esperando Material
        Finalizado
        Cobrado
        Avisado Cliente

        URGENCIA PARTE:
        Urgentes
        Genéricas
        Inmediatas
        N.C.

        TIPOS TAREA:::0.00
        002 Mano de obra::0.00
        022 Gasolina con mezcla::0.00
        024 Gasolina sola::0.00
        100 Despiezar y limpiar carburador::0.00
        101 Limpiar caja y filtro de aire::0.00
        102 Limpiar y comprobar bujia::0.00
        103 Limpieza general de máquina::0.00
        104 Afilado de cuchilla::0.00
        105 Engrasar puntos móviles::0.00
        106 Engrasar cabezal::0.00
        107 Limpiar deposito combustible::0.00
        108 Afilado cadena con montaje::0.00
        109 Afilado cadena sin montaje::0.00
        110 Soplar motor::0.00
        111 Revisar y limpiar filtro gasolina::0.00
        112 Reparar instalacion eléctrica::0.00
        113 Cambiar aciete motor y bomba::0.00
        114 Cambiar aceite motor::0.00
        115 Sustituir bujia::0.00
        116 Porte Bueu - Marín - Beluso - Cela::0.00
        117 Porte Seijo - (Alrededores)::0.00
        118 Descarbonizar silencioso y cilindro::0.00
        119 Vaciar cuba carburador::0.00
        120 Nº Cuenta: ES51 2100 4730 0302 0018 6837::0.00
        121 Puesta a punto::0.00
        122 Aceite Sae 30 0.6 L::0.00
        123 Limpiar tapa cadena y rectificar espada::0.00
        124 Rectificar y engrasar espada::0.00
        125 Sustituir tubos gasolina::0.00
        126 Regular Carburador::0.00
        127 Revisar cilindro y pistón::0.00
        128 Mano de obra 2::0.00
        129 Aceite cadena::0.00
        130 Junta admision corte::0.00
        131 Planificar toma admisión::0.00
        132 Filtrar gasolina::0.00
        133 Montar y ajuste de embrague::0.00
        134 Aceite motor Sae 30 B&S 0.6 L::0.00
        135 Revisar y engrasar piñones y chavetas traccio::0.00
        136 Tubos Gasolina::0.00
        137 Filtro Gasolina::0.00
        138 Afilado de cuchillas cortasetos::0.00

        TIPO MAQUINA:
        Desbrozadoras
        Motosierra
        Cortacesped
        Sulfatadoras
        Material Eléctrico
        Hidrolimpiadora
        Cortasetos
        Sopladores
        Tractores
        Motocultor
        Generadores
        Otros
    */

    //selects: estados_parte, 
    static public function createTableSelects($conn){
        $sql = "CREATE TABLE selects (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            tabla VARCHAR(255),
            menu JSON,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        //ejecutar sin devolver nada
        $conn->query($sql);

        //insertar selects
        $menu = json_encode([
            "Abierto__",
            "Presupuesto",
            "En Reparación",
            "Esperando Material",
            "Finalizado",
            "Cobrado",
            "Avisado Cliente"
        ]);
        $sql_insert = "INSERT INTO selects (nombre, tabla, menu) VALUES ('estados_parte', 'partes', '$menu')";
        $conn->query($sql_insert);

        $menu = json_encode([
            "Urgentes",
            "Genéricas__",
            "Inmediatas",
            "N.C."
        ]);
        $sql_insert = "INSERT INTO selects (nombre, tabla, menu) VALUES ('urgencia_parte', 'partes', '$menu')";
        $conn->query($sql_insert);

        $menu = json_encode([
            "Desbrozadoras",
            "Motosierra",
            "Cortacesped",
            "Sulfatadoras",
            "Material Eléctrico",
            "Hidrolimpiadora",
            "Cortasetos",
            "Sopladores",
            "Tractores",
            "Motocultor",
            "Generadores",
            "Otros"
        ]);
        $sql_insert = "INSERT INTO selects (nombre, tabla, menu) VALUES ('tipo_maquina', 'maquinas', '$menu')";
        $conn->query($sql_insert);

        $menu = json_encode([
            "002 Mano de obra::0.00",
            "022 Gasolina con mezcla::0.00",
            "024 Gasolina sola::0.00",
            "100 Despiezar y limpiar carburador::0.00",
            "101 Limpiar caja y filtro de aire::0.00",
            "102 Limpiar y comprobar bujia::0.00",
            "103 Limpieza general de máquina::0.00",
            "104 Afilado de cuchilla::0.00",
            "105 Engrasar puntos móviles::0.00",
            "106 Engrasar cabezal::0.00",
            "107 Limpiar deposito combustible::0.00",
            "108 Afilado cadena con montaje::0.00",
            "109 Afilado cadena sin montaje::0.00",
            "110 Soplar motor::0.00",
            "111 Revisar y limpiar filtro gasolina::0.00",
            "112 Reparar instalacion eléctrica::0.00",
            "113 Cambiar aciete motor y bomba::0.00",
            "114 Cambiar aceite motor::0.00",
            "115 Sustituir bujia::0.00",
            "116 Porte Bueu - Marín - Beluso - Cela::0.00",
            "117 Porte Seijo - (Alrededores)::0.00",
            "118 Descarbonizar silencioso y cilindro::0.00",
            "119 Vaciar cuba carburador::0.00",
            "120 Nº Cuenta: ES51 2100 4730 0302 0018 6837::0.00",
            "121 Puesta a punto::0.00",
            "122 Aceite Sae 30 0.6 L::0.00",
            "123 Limpiar tapa cadena y rectificar espada::0.00",
            "124 Rectificar y engrasar espada::0.00",
            "125 Sustituir tubos gasolina::0.00",
            "126 Regular Carburador::0.00",
            "127 Revisar cilindro y pistón::0.00",
            "128 Mano de obra 2::0.00",
            "129 Aceite cadena::0.00",
            "130 Junta admision corte::0.00",
            "131 Planificar toma admisión::0.00",
            "132 Filtrar gasolina::0.00",
            "133 Montar y ajuste de embrague::0.00",
            "134 Aceite motor Sae 30 B&S 0.6 L::0.00",
            "135 Revisar y engrasar piñones y chavetas traccio::0.00",
            "136 Tubos Gasolina::0.00",
            "137 Filtro Gasolina::0.00",
            "138 Afilado de cuchillas cortasetos::0.00",
        ]);
        $sql_insert = "INSERT INTO selects (nombre, tabla, menu) VALUES ('tipo_tarea', 'tareas', '$menu')";
        $conn->query($sql_insert);
    }

    /*
    tipo_parte
    partes
    %5B%22REPARACION__%22%2C%22REVISION%22%2C%5D

    marca_maquina
    maquinas
    %5B%22STIHL%22%2C%22HUSQVARNA%22%2C%5D
    */
}