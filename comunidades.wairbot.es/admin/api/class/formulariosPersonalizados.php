<?php

class FormulariosPersonalizados{
    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];
        $tabla = "forms";
        $datosRelacionados = [];
        $filtro = [
            "title___A" => "!incidencia",
            "title___B" => "!_in_tec_",
            "title___C" => "!tecnico",
            "title___D" => "!lugares",
        ];

        if($codigoBot != ""){
            $filtro['admin_phone'] = $codigoBot;

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);
        }else{
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while($row = $result->fetch_assoc()){
                $codigos[] = $row['codigo_bot'];
            }

            if($codigos == []){
                return [
                    'status' => 'success',
                    'message' => 'No hay formularios personalizados',
                    'data' => [
                        'paginaActual' => 1,
                        'paginasTotales' => 1,
                        'data' => []
                    ]
                ];
            }

            //añadir cada codigo a filtro con ___ y un número
            $i = 0;
            foreach($codigos as $codigo){
                $codigo = "||" . $codigo;
                $filtro['admin_phone___'.$i] = $codigo;
                $i++;
            }

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);
        }

        return [
            'status' => 'success',
            'message' => 'Formularios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function crear($request){
        $data = $request['data'];
        $codigoBot = $request['codigoBot'];
        $id_agente = $_SESSION['user_id'];

        //comprobar que el codigoBot pertenece al agente
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }

        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }

        $titulo = $data['titulo'];
        $pregunta = $data['pregunta'];
        $tipoRespuesta = $data['tipoRespuesta'];
 
        $nombreTabla = trim($titulo);
        $nombreTabla = strtolower(str_replace(" ", "_", $nombreTabla));

        $nombreTabla = $nombreTabla . "_" . $codigoBot;

        //comprobar que no existe un formulario con el mismo name_table
        $sql = "SELECT * FROM forms WHERE name_table = '$nombreTabla'";
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            return [
                'status' => 'error',
                'message' => 'Esta comunidad ya tiene un formulario con este nombre'
            ];
        }

        //crear formulario
        $crearFormulario = self::crearFormularioIncidencias($codigoBot, $titulo, $pregunta, $tipoRespuesta);

        if($crearFormulario){
            return [
                'status' => 'success',
                'message' => 'Formulario creado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el formulario'
            ];
        }
    }

    static public function crearFormularioIncidencias($codigoBot, $titulo, $pregunta, $tipoRespuesta){
		$adminPhone = $codigoBot;

        //quitar espacios al principio y al final   
        $titulo = trim($titulo);

        //poner titulo en minúsculas y convertir espacios en guiones bajos
        $titulo = strtolower(str_replace(" ", "_", $titulo));

		$nombreTabla = $titulo . "_" . $adminPhone;

		//lugar, tipo, descripción, imagen
		$fields = [
			[
				"id" => 1,
				"label" => "Escribe tu nombre",
				"name_column" => "nombre",
				"type" => "text",
				"required" => "true",
				"status" => "true",
				"filters" => []
			],[
				"id" => 2,
				"label" => "Indicanos tu dirección completa",
				"name_column" => "direccion",
				"type" => "text",
				"required" => "true",
				"status" => "true",
				"filters" => []
			],[
				"id" => 3,
				"label" => $pregunta,
				"name_column" => $tipoRespuesta,
				"type" => "text",
				"required" => "true",
				"status" => "true",
				"filters" => []
			]
		];		

		$crearFormulario = self::crearFormulario($adminPhone, $nombreTabla, $fields, "");

        //activar plugin
        self::agregarConfiguracionDePlugin($adminPhone, "ComunidadesCompletoRespuestaFormularioPersonalizado", $nombreTabla);

		if($crearFormulario){
			return true;
		}else{
			echo json_encode(["status" => false, "message" => "Error al crear el formulario de incidencias"]);
		}
	}

    static public function crearFormulario($adminPhone, $nombreTabla, $fields, $rol){
		$conn = $_SESSION['db_connection'];

		//el titulo es igual al nombre de la tabla sin el sufijo _$adminPhone
		$title = str_replace("_" . $adminPhone, "", $nombreTabla);

		$formulario = [
			"admin_phone" => $adminPhone,
			"title" => $title,
			"name_table" => $nombreTabla,
			"public" => "",
			"role" => $rol,
			"status" => "true",
			"fields" => []
		];

		$formulario['fields'] = $fields;

		//añadir formulario a la base de datos
		$data = [
			"table" => "forms",
			"admin_phone" => $adminPhone,
			"title" => $formulario['title'],
			"name_table" => $nombreTabla,
			"public" => "",
			"role" => $rol,
			"status" => "true",
			"fields" => json_encode($fields)
		];

		$insertarUsuarios = self::insertarElementos($conn, [$data]);

		if($insertarUsuarios && $insertarUsuarios['status'] !== true){
			return false;
		}

		//crear tabla de productos
		$sql = "CREATE TABLE " . $nombreTabla . " (";
		$sql .= "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";

		//usar los name column de fields para crear la tabla
		foreach($fields as $field){
			$sql .= $field['name_column'] . " LONGTEXT NULL,";
		}

		//id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario
		$sql .= "id_Usuario VARCHAR(255) NULL,";
		$sql .= "nombre_Usuario VARCHAR(255) NULL,";
		$sql .= "referencia_Usuario VARCHAR(255) default '$adminPhone',";
		$sql .= "rol_Usuario VARCHAR(255) NULL,";
		$sql .= "telefono_usuario VARCHAR(255) NULL,";
		$sql .= "comprobado_Usuario VARCHAR(255) NULL,";

		$sql .= "reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";

		//hacer consulta y dar mensaje de exito o error
		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

    static public function insertarElementos($conn, $elementos) {
        mysqli_set_charset($conn, "utf8mb4");
        $mensaje = "";
        $status = false;
        $codigoHttp = 200;
    
        if (!empty($elementos)) {
            foreach ($elementos as $elemento) {
                $tabla = $elemento['table'];
                unset($elemento['table']); // Eliminamos la clave 'tabla' del array de elementos
    
                // Preparar la consulta SQL
                $consulta = "INSERT INTO $tabla (";
    
                // Construir la cláusula VALUES con los elementos
                $columnas = array();
                $valores = array();
                foreach ($elemento as $columna => $valor) {
                    $columnas[] = $columna;
                
                    //si el valor es un array convertirlo a string
                    if(is_array($valor)){
                        $valor = json_encode($valor);
                    }
                
                    // Verificar si el valor es null
                    if($valor === null){
                        $valor = "";
                    }
                
                    // Escapar las comillas simples en los valores
                    $valor = mysqli_real_escape_string($conn, $valor);
                
                    $valores[] = "'" . $valor . "'";
                }
                $consulta .= implode(", ", $columnas) . ") VALUES (" . implode(", ", $valores) . ")";
    
                // Ejecutar la consulta
                $resultado = mysqli_query($conn, $consulta);
    
                if ($resultado) {
                    $mensaje = "Inserción exitosa";
                    $status = true;
                    $codigoHttp = 200;
                } else {
                    // Verificar si el error es por valor duplicado
                    if (mysqli_errno($conn) === 1062) {
                        // Obtener el valor duplicado
                        preg_match("/Duplicate entry '(.*)' for key/", mysqli_error($conn), $matches);
                        $valorDuplicado = $matches[1];
                        $mensaje = "Error al insertar los datos. El valor '$valorDuplicado' ya existe en una columna con propiedad UNIQUE.";
                        $status = false;
                        $codigoHttp = 400;
                    } else {
                        $mensaje = "Error al insertar los datos";
                        $codigoHttp = 418;
                        $status = false;
                    }
                }
            }
        } else {
            $mensaje = "No se proporcionaron datos para insertar";
        }
    
        return [
            "sql" => $consulta,
            "mensaje" => $mensaje,
            "status" => $status,
            "codigoHttp" => $codigoHttp
        ];
    }

    //obtener la fila del administrador de la tabla admin_bot por su AD_numero_de_telefono_del_bot__tel
    static public function getAdminData($adminPhone){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM admin_bot WHERE AD_numero_de_telefono_del_bot__tel = '$adminPhone'";
        $result = $conn->query($sql);
        $admin = $result->fetch_assoc();
        return $admin;
    }

    static public function agregarConfiguracionDePlugin($adminPhone, $nombrePlugn, $nombreTabla){
        $conn = $_SESSION['db_connection'];

        $adminData = self::getAdminData($adminPhone);
        $pluginConfig = $adminData['AD_plugings_config_'];

        $pluginConfig = json_decode($pluginConfig, true);

        $pluginConfig[] = [
            "nombre_plugin"           => $nombrePlugn,
            "tabla"                   => $nombreTabla,
            "tipo"                    => "columna",
            "nombre_nueva_columna"    => "",
            "funciones"               => ["handleData"],
            "template"                => ["tabla" => ""],
            "columnas"                => [],
            "valores"                 => [],
            "clase"                   => $nombrePlugn,
            "tipo_resultado"          => "text"
        ];

        $sql = "UPDATE admin_bot SET AD_plugings_config_ = '".json_encode($pluginConfig)."' WHERE AD_numero_de_telefono_del_bot__tel = '$adminPhone'";
        if($conn->query($sql) === TRUE){
            return true;
        }else{
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    static public function verUno($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $id_agente = $_SESSION['user_id'];

        //comprobar que el codigoBot pertenece al agente
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }

        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }

        $tabla = "forms";
        $filtro = [];
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Formulario obtenido correctamente',
            'data' => $result
        ];
    }

    static public function borrar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBotId'];
        $id_agente = $_SESSION['user_id'];

        //comprobar que el codigoBot pertenece al agente
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El código no existe'
            ];
        }

        $ids_agentes_comunidad = [];
        while($row = $result->fetch_assoc()){
            $ids_agentes_comunidad[] = $row['id_agente'];
        }

        if(!in_array($id_agente, $ids_agentes_comunidad) && $_SESSION['user_email'] != "Gesfincas"){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver estos lugares'
            ];
        }

        $tabla = "forms";
        $check = [];

        //obtener de la tabla el name_table y borra la tabla con ese nombre
        $sql = "SELECT name_table FROM $tabla WHERE id = $id";
        $result = $conn->query($sql);
        $nameTable = $result->fetch_assoc()['name_table'];

        $sql = "DROP TABLE $nameTable";
        $conn->query($sql);

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Formulario eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el formulario',
                'error' => $deleted
            ];
        }
    }
}

