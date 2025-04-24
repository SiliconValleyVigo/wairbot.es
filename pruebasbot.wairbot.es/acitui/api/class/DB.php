<?php
/* 
USUARIOS:
id
rol
email
password
n_socio
token
estado (PREINSCRITO, INSCRITO, BAJA)
nombre_comercial
razon_social
cif
titular
dni
denominacion_actividad
iae
antiguedad
n_trabajadores_autonomos
n_trabajadores_cuenta_ajena
direccion
numero_direccion
poblacion
codigo_postal
telefono
fax
movil
nombre_banco
localidad_banco
titular_banco
iban
periodo_pago (1,3,6)
forma_pago (EFECTIVO, TRANSFERENCIA, CARGO EN CUENTA)
fecha_ultima_factura
solicitud_baja
motivo_baja
timestamp

FACTURAS:
id
id_socio
n_socio
nombre_comercial
razon_social
cif
forma_pago
iban
importe
concepto
n_factura
fecha_factura
conformada
abonada
pdf
timestamp

*/

class DB {
    static public function initDB(){
        // Datos de conexión a la base de datos  
        //$servername = "wairbot.es";
		$servername = getenv('DB_SERVER_DEFAULT_HOST');
        $username = getenv('ACITUI_DB_USER');
        $password = getenv('ACITUI_DB_PASSWORD');
        $database = getenv('ACITUI_DB_NAME');
        
        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $database);
            
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Error al conectar con la base de datos: " . $conn->connect_error);
        }
        
        /*
        // Consulta para verificar si existe la tabla clientes
        $table_check_query = "SHOW TABLES LIKE 'usuarios'";
        $table_check_result = $conn->query($table_check_query);
        
        if (!$table_check_result->num_rows > 0) {
            self::createTablaUsuarios($conn);
            self::createTablaFacturas($conn);
        }else{
            //comprobar si la columna documento existe en la tabla usuarios y si no existe crearla
            $column_check_query = "SHOW COLUMNS FROM usuarios LIKE 'documento'";
            $column_check_result = $conn->query($column_check_query);

            if (!$column_check_result->num_rows > 0) {
                $sql = "ALTER TABLE usuarios ADD documento VARCHAR(255)";
                //ejecutar la consulta sin devolver resultados
                $conn->query($sql);
            }
        }*/
        
        return $conn;
    }

    static public function createTablaUsuarios($conn){
        $sql = "CREATE TABLE usuarios (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            rol VARCHAR(255),
            email VARCHAR(255),
            password VARCHAR(255),
            n_socio VARCHAR(255),
            token VARCHAR(255),
            estado VARCHAR(255),
            nombre_comercial VARCHAR(255),
            razon_social VARCHAR(255),
            cif VARCHAR(255),
            titular VARCHAR(255),
            dni VARCHAR(255),
            denominacion_actividad VARCHAR(255),
            iae VARCHAR(255),
            antiguedad VARCHAR(255),
            n_trabajadores_autonomos VARCHAR(255),
            n_trabajadores_cuenta_ajena VARCHAR(255),
            direccion VARCHAR(255),
            numero_direccion VARCHAR(255),
            poblacion VARCHAR(255),
            codigo_postal VARCHAR(255),
            telefono VARCHAR(255),
            fax VARCHAR(255),
            movil VARCHAR(255),
            nombre_banco VARCHAR(255),
            localidad_banco VARCHAR(255),
            titular_banco VARCHAR(255),
            iban VARCHAR(255),
            periodo_pago int(12),
            forma_pago VARCHAR(255),
            fecha_ultima_factura VARCHAR(255),
            solicitud_baja VARCHAR(255),
            motivo_baja VARCHAR(255),
            documento VARCHAR(255),
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

    static public function createTablaFacturas($conn){
        $sql = "CREATE TABLE facturas (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_socio INT(12),
            n_socio VARCHAR(255),
            nombre_comercial VARCHAR(255),
            razon_social VARCHAR(255),
            cif VARCHAR(255),
            forma_pago VARCHAR(255),
            iban VARCHAR(255),
            importe VARCHAR(255),
            concepto VARCHAR(255),
            n_factura VARCHAR(255),
            fecha_factura VARCHAR(255),
            conformada VARCHAR(255),
            abonada VARCHAR(255),
            pdf VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                "status" => "success",
                "message" => "Tabla facturas creada con éxito"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al crear la tabla facturas: " . $conn->error
            ]);
        }
    }
}