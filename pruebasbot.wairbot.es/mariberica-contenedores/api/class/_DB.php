<?php
/*
    TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, id_naviera, naviera, identificador_naviera, fecha_embarque(fecha), fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp

    navieras: id, nombre, accesos(json), referencia, notas(textarea), timestamp
*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos  
        $servername = getenv('DB_SERVER_DEFAULT_HOST');
        $username = getenv('MAR_IBERICA_DB_USER');
        $password = getenv('MAR_IBERICA_DB_PASSWORD');
        $database = getenv('MAR_IBERICA_DB_NAME');
        
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

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
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

    static public function createTablaProveedores($conn){
        $sql = "CREATE TABLE IF NOT EXISTS proveedores (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla proveedores: " . $conn->error
            ]);
        }
    }

    static public function createTablaCompras($conn){
        $sql = "CREATE TABLE IF NOT EXISTS compras (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fecha DATE,
            id_proveedor INT(12),
            n_compra VARCHAR(255),
            seguro VARCHAR(255),
            lc LONGTEXT,
            etiquetas LONGTEXT,
            documentos LONGTEXT,
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla compras: " . $conn->error
            ]);
        }
    }

    static public function createTablaContenedores($conn){
        $sql = "CREATE TABLE IF NOT EXISTS contenedores (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_compra INT(12),
            booking VARCHAR(255),
            identificador_contenedor VARCHAR(255),
            id_naviera INT(12),
            naviera VARCHAR(255),
            identificador_naviera VARCHAR(255),
            fecha_embarque VARCHAR(255),
            fecha_llegada_estimada VARCHAR(255),
            estado_de_embarque VARCHAR(255),
            informacion LONGTEXT,
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla contenedores: " . $conn->error
            ]);
        }
    }

    static public function createTablaProductosDeContenedor($conn){
        $sql = "CREATE TABLE IF NOT EXISTS productos_de_contenedor (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_contenedor INT(12),
            id_producto INT(12),
            divisa VARCHAR(255),
            cantidad INT(12),
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla productos_de_contenedor: " . $conn->error
            ]);
        }
    }

    static public function createTablaProductos($conn){
        $sql = "CREATE TABLE IF NOT EXISTS productos (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(255),
            descripcion VARCHAR(255),
            presentacion VARCHAR(255),
            precio DECIMAL(10, 2),
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla productos: " . $conn->error
            ]);
        }
    }

    static public function createTablaNavieras($conn){
        $sql = "CREATE TABLE IF NOT EXISTS navieras (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            accesos JSON,
            referencia VARCHAR(255),
            notas LONGTEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla navieras: " . $conn->error
            ]);
        }
    }


    #######################################################################################################
    /////////////////////////////////////// TABLAS DE CONFIGURACION ///////////////////////////////////////
    #######################################################################################################

    static public function createTablaSelects($conn){
        $sql = "CREATE TABLE IF NOT EXISTS selects (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            tabla VARCHAR(255),
            menu JSON,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        //crear tabla selects sin devolver nada
        $conn->query($sql);

        return true;
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
        $filtros = "[]";
        $columnas = "{}";
        $order_by = "";
        $visualizacion = "[]";

        //// El nombre de las tablas debería ser el nombre de la clase en minúsculas ///////
        $tablas = ['proveedores', 'compras', 'contenedores', 'productos_de_contenedor', 'productos', 'navieras'];

        $sql = "INSERT INTO configuracion_tablas (nombre_tabla, grid_or_table, row_per_page, filtros, columnas, order_by, visualizacion) VALUES ";
        $sql .= implode(", ", array_map(function($tabla) use ($gridOrTable, $row_per_page, $filtros, $columnas, $order_by, $visualizacion){
            return "('$tabla', '$gridOrTable', $row_per_page, '$filtros', '$columnas', '$order_by', '$visualizacion')";
        }, $tablas));

        $conn->query($sql);

        return true;
    }

    static public function createTablas($conn){
        if (!self::createTablaUsuarios($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla usuarios"
            ]);
            return false;
        }
        if (!self::createTablaSelects($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla selects"
            ]);
            return false;
        }
        if (!self::createTablaConfiguracionTablas($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla configuracion_tablas"
            ]);
            return false;
        }
        if (!self::createTablaProveedores($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla proveedores"
            ]);
            return false;
        }
        if (!self::createTablaCompras($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla compras"
            ]);
            return false;
        }
        if (!self::createTablaContenedores($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla contenedores"
            ]);
            return false;
        }
        if (!self::createTablaProductosDeContenedor($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla productos_de_contenedor"
            ]);
            return false;
        }
        if (!self::createTablaProductos($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla productos"
            ]);
            return false;
        }
        if (!self::createTablaNavieras($conn)) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla navieras"
            ]);
            return false;
        }

        self::insertarDatosEjemplo($conn);
        return true;
    }

    //INSERTAR DATOS DE EJEMPLO para simular tres meses de 2024 con 10 compras al mes con 2 contenedores cada una y 5 productos por contenedor con 3 navieras y 5 proveedores usar un bucle para insertar los datos
    /*
    TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, id_naviera, naviera, identificador_naviera, fecha_embarque(fecha), fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp

    navieras: id, nombre, accesos(json), referencia, notas(textarea), timestamp
*/
static public function insertarDatosEjemplo($conn){
    // Insertar proveedores
    foreach (range(1, 5) as $i) {
        $nombre = "Proveedor $i";
        $referencia = "REF-PROV-$i";
        $notas = "Notas del proveedor $i";

        $sql = "INSERT INTO proveedores (nombre, referencia, notas) VALUES ('$nombre', '$referencia', '$notas')";
        if ($conn->query($sql) !== TRUE) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al insertar los proveedores: " . $conn->error
            ]);
        }
    }

    // Insertar navieras
    foreach (range(1, 3) as $i) {
        $nombre = "Naviera $i";
        $accesos = json_encode(["usuario" => "user$i", "password" => "pass$i"]);
        $referencia = "REF-NAV-$i";
        $notas = "Notas de la naviera $i";

        $sql = "INSERT INTO navieras (nombre, accesos, referencia, notas) VALUES ('$nombre', '$accesos', '$referencia', '$notas')";
        if ($conn->query($sql) !== TRUE) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al insertar las navieras: " . $conn->error
            ]);
        }
    }

    // Insertar productos
    foreach (range(1, 5) as $i) {
        $codigo = "CODIGO-$i";
        $descripcion = "Producto $i";
        $presentacion = "Presentación $i";
        $precio = rand(10, 100);
        $referencia = "REF-PROD-$i";
        $notas = "Notas del producto $i";

        $sql = "INSERT INTO productos (codigo, descripcion, presentacion, precio, referencia, notas) VALUES ('$codigo', '$descripcion', '$presentacion', $precio, '$referencia', '$notas')";
        if ($conn->query($sql) !== TRUE) {
            echo json_encode([
                "status" => "error",
                "message" => "Error al insertar los productos: " . $conn->error
            ]);
        }
    }

    // Insertar compras, contenedores y productos de contenedor
    foreach (range(1, 3) as $mes) {
        foreach (range(1, 10) as $compra) {
            $fecha = "2024-$mes-$compra";
            $id_proveedor = rand(1, 5);
            $n_compra = "COMPRA-$mes-$compra";
            $seguro = rand(0, 1) ? 'SI' : 'NO';
            $lc = "LC-$mes-$compra";
            $etiquetas = "ETIQUETAS-$mes-$compra";
            $documentos = "DOCUMENTOS-$mes-$compra";
            $referencia = "REFERENCIA-$mes-$compra";
            $notas = "NOTAS-$mes-$compra";

            $sql = "INSERT INTO compras (fecha, id_proveedor, n_compra, seguro, lc, etiquetas, documentos, referencia, notas) VALUES ('$fecha', $id_proveedor, '$n_compra', '$seguro', '$lc', '$etiquetas', '$documentos', '$referencia', '$notas')";

            if ($conn->query($sql) === TRUE) {
                $id_compra = $conn->insert_id;

                foreach (range(1, 2) as $contenedor) {
                    $booking = "BOOKING-$mes-$compra-$contenedor";
                    $identificador_contenedor = "IDENTIFICADOR_CONTENEDOR-$mes-$compra-$contenedor";
                    $id_naviera = rand(1, 3);
                    $naviera = "NAVIERA-$mes-$compra-$contenedor";
                    $identificador_naviera = "IDENTIFICADOR_NAVIERA-$mes-$compra-$contenedor";
                    $fecha_embarque = "2024-$mes-$compra";
                    $fecha_llegada_estimada = "2024-$mes-" . ($compra + 5);
                    $estado_de_embarque = "ESTADO_DE_EMBARQUE-$mes-$compra-$contenedor";
                    $informacion = "INFORMACION-$mes-$compra-$contenedor";
                    $referencia = "REFERENCIA-$mes-$compra-$contenedor";
                    $notas = "NOTAS-$mes-$compra-$contenedor";

                    $sql = "INSERT INTO contenedores (id_compra, booking, identificador_contenedor, id_naviera, naviera, identificador_naviera, fecha_embarque, fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas) VALUES ($id_compra, '$booking', '$identificador_contenedor', $id_naviera, '$naviera', '$identificador_naviera', '$fecha_embarque', '$fecha_llegada_estimada', '$estado_de_embarque', '$informacion', '$referencia', '$notas')";

                    if ($conn->query($sql) === TRUE) {
                        $id_contenedor = $conn->insert_id;

                        foreach (range(1, 5) as $producto) {
                            $id_producto = rand(1, 5);
                            $divisa = "DIVISA-$mes-$compra-$contenedor-$producto";
                            $cantidad = rand(1, 100);
                            $referencia = "REFERENCIA-$mes-$compra-$contenedor-$producto";
                            $notas = "NOTAS-$mes-$compra-$contenedor-$producto";

                            $sql = "INSERT INTO productos_de_contenedor (id_contenedor, id_producto, divisa, cantidad, referencia, notas) VALUES ($id_contenedor, $id_producto, '$divisa', $cantidad, '$referencia', '$notas')";

                            if ($conn->query($sql) !== TRUE) {
                                echo json_encode([
                                    "status" => "error",
                                    "message" => "Error al insertar los productos de contenedor: " . $conn->error
                                ]);
                            }
                        }
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Error al insertar los contenedores: " . $conn->error
                        ]);
                    }
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al insertar las compras: " . $conn->error
                ]);
            }
        }
    }
}
}

