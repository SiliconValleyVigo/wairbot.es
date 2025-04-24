<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
motores: imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ulitma_revision, fecha_proxima_revision
partes: fecha, id_motor, tipo, notas, estado
tareas: id_parte, descripcion, tipo, referencia
documentos: id_parte, url
solicitudes: id_motor, id_agente, estado
notificaciones: id_cliente, texto
publicidad: url, imagen, nombre, precio, estado
*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos
        $servername = "getenv('DB_SERVER_DEFAULT_HOST')";
        $username = "repamar1motores";
        $password = "nb0W3o62*";
        $database = "repamar_motores";

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
            self::createTablaMotores($conn);
            self::createTablaPartes($conn);
            self::createTablaTareas($conn);
            self::createTablaDocumentos($conn);
            self::createTablaSolictudes($conn);
            self::createTablaNotificaciones($conn);
            self::createTablaPublicidad($conn);
            self::createTablaEmails($conn);

            //self::insertarDatosDeEjemplo($conn);
        }
        
        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(30),
            imagen VARCHAR(255),
            nombre VARCHAR(255),
            referencia VARCHAR(255),
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
        $defaultEmail = getenv('DB_REPAMAR_EMAIL');
        $defaultPassword = getenv('DB_DEFAULT_PASSWORD');
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

    static public function createTablaMotores($conn){
        $sql = "CREATE TABLE motores (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            imagen VARCHAR(255),
            marca VARCHAR(255),
            modelo VARCHAR(255),
            codigo VARCHAR(255),
            observaciones TEXT,
            id_cliente INT(12),
            id_agente INT(12),
            fecha_instalacion DATE,
            fecha_ultima_revision DATE,
            fecha_proxima_revision DATE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla motores creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla motores: " . $conn->error
            ]);
        }
    }

    static public function createTablaPartes($conn){
        $sql = "CREATE TABLE partes (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fecha DATE,
            id_motor INT(12),
            tipo VARCHAR(30),
            notas TEXT,
            estado VARCHAR(30),
            id_tecnico INT(12),
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

    static public function createTablaSolictudes($conn){
        $sql = "CREATE TABLE solicitudes (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_motor INT(12),
            id_agente INT(12),
            estado VARCHAR(30),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla solicitudes creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla solicitudes: " . $conn->error
            ]);
        }
    }

    static public function createTablaNotificaciones($conn){
        $sql = "CREATE TABLE notificaciones (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT(12),
            texto TEXT,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla notificaciones creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla notificaciones: " . $conn->error
            ]);
        }
    }

    static public function createTablaPublicidad($conn){
        $sql = "CREATE TABLE publicidad (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            url VARCHAR(255),
            imagen VARCHAR(255),
            nombre VARCHAR(255),
            precio VARCHAR(30),
            estado VARCHAR(30),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla publicidad creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla publicidad: " . $conn->error
            ]);
        }
    }

    static public function createTablaEmails($conn){
        $sql = "CREATE TABLE emails (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255),
            texto LONGTEXT,
            fecha DATE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla emails creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla emails: " . $conn->error
            ]);
        }
    }

    //insertar datos de ejemplo
    //{AGENTES}, {TECNICOS}, {CLIENTES}, {MOTORES}, {PARTES}, {TAREAS}, {DOCUMENTOS}, NOTIFICACIONES, SOLICITUDES, PUBLICIDAD
    static public function insertarDatosDeEjemplo($conn){
        
        $id_agente = 0;
        $id_cliente1 = 0;
        $id_cliente2 = 0;
        $id_tecnico1 = 0;
        $id_tecnico2 = 0;
        for ($i = 2; $i <= 50; $i++) {
            $agenteCambiado = false;
            //si i es igual a 2 o multiplo de 5, crear un agente y un cliente
            if ($i == 2 || $i % 5 == 0) {
                $sql = "INSERT INTO usuarios (rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente) VALUES ('AGENTE', 'imagenAgente$i', 'nombreAgente$i', 'referenciaAgente$i', 'direccionAgente$i', 'telefonoAgente$i', 'whatsappAgente$i', 'emailAgente$i', 'passwordAgente$i', 0)";
                $conn->query($sql);
                $id_agente = $conn->insert_id;
                
                $sql = "INSERT INTO usuarios (rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente) VALUES ('TECNICO', 'imagenTecnico1$i', 'nombreTecnico1$i', 'referenciaTecnico1$i', 'direccionTecnico1$i', 'telefonoTecnico1$i', 'whatsappTecnico1$i', 'emailTecnico1$i', 'passwordTecnico1$i', $id_agente)";
                $conn->query($sql);
                $id_tecnico1 = $conn->insert_id;

                $sql = "INSERT INTO usuarios (rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente) VALUES ('TECNICO', 'imagenTecnico2$i', 'nombreTecnico2$i', 'referenciaTecnico2$i', 'direccionTecnico2$i', 'telefonoTecnico2$i', 'whatsappTecnico2$i', 'emailTecnico2$i', 'passwordTecnico2$i', $id_agente)";
                $conn->query($sql);
                $id_tecnico2 = $conn->insert_id;

                $sql = "INSERT INTO usuarios (rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente) VALUES ('CLIENTE', 'imagenCliente1$i', 'nombreCliente1$i', 'referenciaCliente1$i', 'direccionCliente1$i', 'telefonoCliente1$i', 'whatsappCliente1$i', 'emailCliente1$i', 'passwordCliente1$i', $id_agente)";
                $conn->query($sql);
                $id_cliente1 = $conn->insert_id;

                $sql = "INSERT INTO usuarios (rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente) VALUES ('CLIENTE', 'imagenCliente2$i', 'nombreCliente2$i', 'referenciaCliente2$i', 'direccionCliente2$i', 'telefonoCliente2$i', 'whatsappCliente2$i', 'emailCliente2$i', 'passwordCliente2$i', $id_agente)";
                $conn->query($sql);
                $id_cliente2 = $conn->insert_id;
                
                $agenteCambiado = true;
            }

            //crear motor
            $id_cliente = rand($id_cliente1, $id_cliente2);
            $sql = "INSERT INTO motores (imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision) VALUES ('imagen2$i', 'marc2a$i', 'modelo2$i', 'codigo2$i', 'observaciones2$i', $id_cliente, $id_agente, NOW(), NOW(), NOW())";

            $conn->query($sql);
            $id_motor = $conn->insert_id;

            //crear 2 partes
            //obtener un tecnico aleatorio
            $id_tecnico = rand($id_tecnico1, $id_tecnico2);
            $tipos = ["INSTALACION", "REPARACION", "REVISION"];
            $estados = ["ABIERTO", "CERRADO"];
            $tipo = $tipos[rand(0, 2)];
            $estado = $estados[rand(0, 1)];

            $sql = "INSERT INTO partes (fecha, id_motor, tipo, notas, estado, id_tecnico) VALUES (NOW(), $id_motor, '$tipo', 'notas$i', '$estado', $id_tecnico)";
            $conn->query($sql);
            $id_parte = $conn->insert_id;

            //crear 2 tareas y 2 documentos
            for ($j = 1; $j <= 2; $j++) {
                $tiposTarea = ["REPARACION", "REVISION", "SUSTITUCION"];
                $tipoTarea = $tiposTarea[rand(0, 2)];
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, 'descripcion$i$j', '$tipoTarea', 'referencia$i$j')";
                $conn->query($sql);

                $tiposDocumentos = ['DOCUMENTO', 'IMAGEN', 'VIDEO'];
                $tipoDocumento = $tiposDocumentos[rand(0, 2)];
                $sql = "INSERT INTO documentos (tipo, id_parte, url) VALUES ('$tipoDocumento', $id_parte, 'url$i$j')";
                $conn->query($sql);
            }

            $id_tecnico = rand($id_tecnico1, $id_tecnico2);
            $tipos = ["INSTALACION", "REPARACION", "REVISION"];
            $estados = ["ABIERTO", "CERRADO"];
            $tipo = $tipos[rand(0, 2)];
            $estado = $estados[rand(0, 1)];

            $sql = "INSERT INTO partes (fecha, id_motor, tipo, notas, estado, id_tecnico) VALUES (NOW(), $id_motor, '$tipo', 'notas$i', '$estado', $id_tecnico)";
            $conn->query($sql);
            $id_parte = $conn->insert_id;

            //crear 2 tareas y 2 documentos
            for ($j = 1; $j <= 2; $j++) {
                $tiposTarea = ["REPARACION", "REVISION", "SUSTITUCION"];
                $tipoTarea = $tiposTarea[rand(0, 2)];
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, 'descripcion$i$j', '$tipoTarea', 'referencia$i$j')";
                $conn->query($sql);

                $tiposDocumentos = ['DOCUMENTO', 'IMAGEN', 'VIDEO'];
                $tipoDocumento = $tiposDocumentos[rand(0, 2)];
                $sql = "INSERT INTO documentos (tipo, id_parte, url) VALUES ('$tipoDocumento', $id_parte, 'url$i$j')";
                $conn->query($sql);
            }

            $id_cliente = rand($id_cliente1, $id_cliente2);
            $sql = "INSERT INTO motores (imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision) VALUES ('imagen2$i', 'marc2a$i', 'modelo2$i', 'codigo2$i', 'observaciones2$i', $id_cliente, $id_agente, NOW(), NOW(), NOW())";

            $conn->query($sql);
            $id_motor = $conn->insert_id;

            //crear 2 partes
            //obtener un tecnico aleatorio
            $id_tecnico = rand($id_tecnico1, $id_tecnico2);
            $tipos = ["INSTALACION", "REPARACION", "REVISION"];
            $estados = ["ABIERTO", "CERRADO"];
            $tipo = $tipos[rand(0, 2)];
            $estado = $estados[rand(0, 1)];

            $sql = "INSERT INTO partes (fecha, id_motor, tipo, notas, estado, id_tecnico) VALUES (NOW(), $id_motor, '$tipo', 'notas$i', '$estado', $id_tecnico)";
            $conn->query($sql);
            $id_parte = $conn->insert_id;

            //crear 2 tareas y 2 documentos
            for ($j = 1; $j <= 2; $j++) {
                $tiposTarea = ["REPARACION", "REVISION", "SUSTITUCION"];
                $tipoTarea = $tiposTarea[rand(0, 2)];
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, 'descripcion$i$j', '$tipoTarea', 'referencia$i$j')";
                $conn->query($sql);

                $tiposDocumentos = ['DOCUMENTO', 'IMAGEN', 'VIDEO'];
                $tipoDocumento = $tiposDocumentos[rand(0, 2)];
                $sql = "INSERT INTO documentos (tipo, id_parte, url) VALUES ('$tipoDocumento', $id_parte, 'url$i$j')";
                $conn->query($sql);
            }

            $id_tecnico = rand($id_tecnico1, $id_tecnico2);
            $tipos = ["INSTALACION", "REPARACION", "REVISION"];
            $estados = ["ABIERTO", "CERRADO"];
            $tipo = $tipos[rand(0, 2)];
            $estado = $estados[rand(0, 1)];

            $sql = "INSERT INTO partes (fecha, id_motor, tipo, notas, estado, id_tecnico) VALUES (NOW(), $id_motor, '$tipo', 'notas$i', '$estado', $id_tecnico)";
            $conn->query($sql);
            $id_parte = $conn->insert_id;

            //crear 2 tareas y 2 documentos
            for ($j = 1; $j <= 2; $j++) {
                $tiposTarea = ["REPARACION", "REVISION", "SUSTITUCION"];
                $tipoTarea = $tiposTarea[rand(0, 2)];
                $sql = "INSERT INTO tareas (id_parte, descripcion, tipo, referencia) VALUES ($id_parte, 'descripcion$i$j', '$tipoTarea', 'referencia$i$j')";
                $conn->query($sql);

                $tiposDocumentos = ['DOCUMENTO', 'IMAGEN', 'VIDEO'];
                $tipoDocumento = $tiposDocumentos[rand(0, 2)];
                $sql = "INSERT INTO documentos (tipo, id_parte, url) VALUES ('$tipoDocumento', $id_parte, 'url$i$j')";
                $conn->query($sql);
            }
            
            //si el agente ha sido cambiado, crear una solicitud
            if ($agenteCambiado) {
                $sql = "INSERT INTO solicitudes (id_motor, id_agente, estado) VALUES ($id_motor, $id_agente, 'PENDIENTE')";
                $conn->query($sql);

                //crear notificacion
                $id_cliente = rand($id_cliente1, $id_cliente2);
                $sql = "INSERT INTO notificaciones (id_cliente, texto) VALUES ($id_cliente, 'texto$i')";
                $conn->query($sql);
            }
        }

        //crear 10 motores con id agente 0 y id cliente 0 a partir de i = 51
        for ($i = 51; $i <= 60; $i++) {
            $sql = "INSERT INTO motores (imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision) VALUES ('imagen$i', 'marca$i', 'modelo$i', 'codigo$i', 'observaciones$i', 0, 0, NOW(), NOW(), NOW())";
            $conn->query($sql);
        }

        //crear 10 publicidades
        $publicidades = [
            "https://repamar.es/fuerabordas-suzuki-marine/642-fueraborda-suzuki-df25s-0000000015899-df25s.html",
            "https://repamar.es/fuerabordas-suzuki-marine/671-324-motor-fueraborda-suzuki-df4a-.html#/156-modelo-df4al_eje_largo",
            "https://repamar.es/fuerabordas-suzuki-marine/673-327-fueraborda-suzuki-df5al-20-21-df5a.html#/158-modelo-df5al",
            "https://repamar.es/fuerabordas-suzuki-marine/643-1075-motor-fueraborda-suzuki-df6a-.html#/160-modelo-df6al_eje_largo",
            "https://repamar.es/fuerabordas-suzuki-marine/4340-fuerabordasuzukidf6asblanco2022-0000000065900-df6as-w.html",
            "https://repamar.es/fuerabordas-suzuki-marine/675-1077-motor-fueraborda-suzuki-df8a-.html#/162-modelo-df8as_eje_corto",
            "https://repamar.es/fuerabordas-suzuki-marine/28548-motor-fueraborda-suzuki-df8arl-0000000015998-df8arl.html",
            "https://repamar.es/fuerabordas-suzuki-marine/644-1073-fueraborda-suzuki-df9-9al-20-21-.html#/163-modelo-df99al_eje_largo",
            "https://repamar.es/fuerabordas-suzuki-marine/646-1149-fueraborda-suzuki-df15al-20-21-.html#/396-modelo-eje_corto",
            "https://repamar.es/fuerabordas-suzuki-marine/30645-1470-motor-fueraborda-suzuki-df30ats-.html#/172-modelo-df30ats",
        ];

        foreach ($publicidades as $url) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTMLFile($url);
            $xpath = new DOMXPath($dom);
            $dataZoomImageNodes = $xpath->query('//*[@data-zoom-image]');
            if ($dataZoomImageNodes->length > 0) {
                $firstNode = $dataZoomImageNodes->item(0);
                if ($firstNode instanceof DOMElement) {
                    $dataZoomImage = $firstNode->getAttribute('data-zoom-image');
                } else {
                    echo "El primer nodo no es un DOMElement.\n";
                }
            } else {
                echo "No se encontró el atributo data-zoom-image.\n";
            }
            $pageHeadingNodes = $xpath->query('//*[contains(@class, "page-heading")]');
            $pageHeading = $pageHeadingNodes->item(0)->nodeValue;
            $currentPriceValueNodes = $xpath->query('//*[contains(@class, "current-price-value")]');
            $currentPriceValue = $currentPriceValueNodes->item(0)->nodeValue;
            $currentPriceValue = preg_replace('/[^0-9,.]/', '', $currentPriceValue);
            $currentPriceValue = $currentPriceValue . " €";
            $sql = "INSERT INTO publicidad (url, imagen, nombre, precio, estado) VALUES ('$url', '$dataZoomImage', '$pageHeading', '$currentPriceValue', 'ACTIVO')";
            $conn->query($sql);
        }
    }
}