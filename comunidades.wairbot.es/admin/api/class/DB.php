<?php

/*
## PRINCIPALES ##
Tabla: usuarios_comunidades_wairbot_central:
Campos: id, rol, email, password, limite, timestamp

Tabla: bots_comunidades_wairbot_central:
Campos: id, id_agente, email, password, prefijo, telefono, codigo_bot, tiempo, idioma, reg_date

Tabla: tecnicos_comunidades_wairbot_central:
Campos: id, id_agente, tipo, actividad, nombre, referencia, telefono, codigos_bots, reg_date

Tabla: formularios_comunidades_wairbot_central:
Campos: id, id_agent, id_form, titulo, pregunta, tipo, respuesta_positiva, respuesta_negativa

## DE BOTS ##
Tabla: incidencias_ampliadas_$codigoBot:
Campos: id, codigo, texto, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date

Tabla: incidencias_cerradas_$codigoBot:
Campos: id, lugar, tipo, descripcion, imagen, codigo, usuario, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date, acciones, tecnico

Tabla: incidencias_$codigoBot:
Campos: id, lugar, incidencia_abierta_plugin_ComunidadesSelectorIncidenciasAbiertas, descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas, tipo, imagen, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date, codigo, usuario, tecnico, acciones

Tabla: lugares_$codigoBot:
Campos: id, lugar_1, lugar_2, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date

Tabla: tecnicos_$codigoBot:
Campos: id, tipo, actividad, nombre, referencia, telefono, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date

Tabla: tecnicos_auxiliares_$codigoBot:
Campos: id, tipo, actividad, nombre, referencia, telefono, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date

Tabla: forms:
Campos: id, admin_phone, title, name_table, public, role, fields, status, timestamp

Tabla: admin:
Campos: AD_id_, AD_email_, AD_password_, AD_tipo_servicio_, AD_validated_, AD_nombre_del_servicio__text, AD_numero_de_telefono_del_bot__tel, AD_numero_de_telefono_con_permiso_de_administrador__text, AD_prefijo_internacional__number, AD_idioma__text, AD_url_envio_de_estados__text, timestamp, AD_interface_data_, AD_permisos_, AD_contador_conversaciones_, AD_plugings_config_, AD_mostrar_columnas_usuario_userinsertdata__text, AD_configuracion_tienda__text
*/

class DB {
    static public function initDB(){
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
        
        // Consulta para verificar si existe la tabla clientes
        $table_check_query = "SHOW TABLES LIKE 'usuarios_comunidades_wairbot_central'";
        $table_check_result = $conn->query($table_check_query);
        
        if (!$table_check_result->num_rows > 0) {
            self::createTablaUsuarios($conn);
            //self::createUsuariosEjemplo($conn);
            self::createTablaBotsAgentesComunidades($conn);
            self::createTablaTecnicosAgentesComunidades($conn);
            self::createTablaFormulariosAgentesComunidades($conn);
        }
        
        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios_comunidades_wairbot_central (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(255),
            email VARCHAR(255),
            password VARCHAR(255),
            limite INT(12),
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
        $sql = "INSERT INTO usuarios_comunidades_wairbot_central (rol, email, password, limite) VALUES ('$defaultRol', '$defaultEmail', '$defaultPassword', 1000)";

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

    //funcion para crear 35 usuarios numerados de ejemplo
    static public function createUsuariosEjemplo($conn){
        for ($i = 1; $i <= 35; $i++) {
            $sql = "INSERT INTO usuarios_comunidades_wairbot_central (rol, email, password, limite) VALUES ('AGENTE', 'agente$i', 'agente$i', 1000)";
            if ($conn->query($sql) === TRUE) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Usuario agente$i creado con éxito"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Error al crear el usuario agente$i: " . $conn->error
                ]);
            }
        }
    }


    static public function createTablaBotsAgentesComunidades($conn){
        $sql = "CREATE TABLE bots_comunidades_wairbot_central (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_agente INT(12),
            email VARCHAR(255),
            password VARCHAR(255),
            prefijo VARCHAR(10),
            telefono VARCHAR(15),
            codigo_bot VARCHAR(255),
            tiempo INT(12),
            idioma VARCHAR(10),
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla bots_comunidades_wairbot_central creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla bots_comunidades_wairbot_central: " . $conn->error
            ]);
        }
    }

    static public function createTablaTecnicosAgentesComunidades($conn){
        $sql = "CREATE TABLE tecnicos_comunidades_wairbot_central (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_agente INT(12),
            tipo VARCHAR(255),
            actividad VARCHAR(255),
            nombre VARCHAR(255),
            referencia VARCHAR(255),
            prefijo VARCHAR(10),
            telefono VARCHAR(255),
            codigos_bots JSON,
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla tecnicos_agentes_comunidades creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla tecnicos_agentes_comunidades: " . $conn->error
            ]);
        }
    }

    static public function createTablaFormulariosAgentesComunidades($conn){
        $sql = "CREATE TABLE formularios_comunidades_wairbot_central (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_agent INT(12),
            id_form INT(12),
            titulo VARCHAR(255),
            pregunta VARCHAR(255),
            tipo VARCHAR(255),
            respuesta_positiva LONGTEXT,
            respuesta_negativa LONGTEXT
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla formularios_agentes_comunidades creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla formularios_agentes_comunidades: " . $conn->error
            ]);
        }
    }
}