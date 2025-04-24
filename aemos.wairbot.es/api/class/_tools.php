<?php

class Tools{
    /**
    * Crea un nuevo registro en la base de datos después de verificar que ciertos valores no existan ya.
    *
    * @param mysqli $conn Conexión a la base de datos.
    * @param string $tabla Nombre de la tabla donde se insertará el nuevo registro.
    * @param array $data Datos a insertar en la tabla. Debe ser un array asociativo donde las claves son los nombres de las columnas.
    * @param array $check Columnas que deben ser verificadas para evitar duplicados. Debe ser un array de nombres de columnas.
    * @return array Resultado de la operación. Si hay un error, devuelve el nombre de la columna duplicada. Si tiene éxito, devuelve el ID del nuevo registro.
    * @throws Exception Si hay un error en la consulta SQL.
    *
    * Ejemplo de uso:
    * 
    * $conn = new mysqli('localhost', 'usuario', 'contraseña', 'base_de_datos');
    * $tabla = 'usuarios';
    * $data = [
    *     'nombre' => 'Juan',
    *     'email' => 'juan@example.com',
    *     'edad' => 30
    * ];
    * $check = ['email'];
    * 
    * $resultado = MiClase::create($conn, $tabla, $data, $check);
    * 
    * if ($resultado['status'] == 'success') {
    *     echo "Nuevo registro creado con ID: " . $resultado['id'];
    * } else {
    *     echo "Error: " . $resultado['result'];
    * }
    */
    static public function create($conn, $tabla, $data, $check){
        // Crear una consulta SQL para verificar todas las columnas a la vez
        $conditions = [];
        $params = [];
        $types = '';
        foreach ($check as $column) {
            if (isset($data[$column])) {
                $conditions[] = "$column = ?";
                $params[] = $data[$column];
                $types .= 's';
            }
        } 

        if (!empty($conditions)) {
            $sql = "SELECT * FROM $tabla WHERE " . implode(' OR ', $conditions);
            $stmt = $conn->prepare($sql);
        
            // Llamar dinámicamente a bind_param con los parámetros
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        
            // Comprobar si alguna columna ya existe
            if ($result->num_rows > 0) {
                $existingRow = $result->fetch_assoc();
                foreach ($check as $column) {
                    if (isset($existingRow[$column]) && $existingRow[$column] == $data[$column]) {
                        // Devolver el nombre de la columna que ya existe
                        $respuesta =  [
                            'status' => 'error',
                            'message' => 'El valor de la columna ' . $column . ' ya existe'
                        ];

                        echo json_encode($respuesta);
                        exit();
                    }
                }
            }
        }
    
        // Inicializar la consulta SQL
        $sql = "INSERT INTO $tabla (";
        $sql .= implode(", ", array_keys($data));
        $sql .= ") VALUES (";
        $sql .= "'" . implode("', '", array_values($data)) . "'";
        $sql .= ")";
    
        // Ejecutar la consulta SQL
        $result = $conn->query($sql);
    
        // Comprobar si las columnas y los ID existen
        if (!$result) {
            throw new Exception("Error en la consulta SQL: " . $conn->error);
        }
    
        // Devolver el ID del nuevo registro
        return [
            'status' => 'success',
            'id' => $conn->insert_id
        ];
    }

    /**
     * Realiza una consulta a la base de datos para leer registros de una tabla específica, con opciones para incluir datos relacionados, aplicar filtros, realizar búsquedas, ordenar los resultados y paginarlos.
     *
     * @param mysqli $conn Conexión activa a la base de datos. Ejemplo: $conn = new mysqli("localhost", "mi_usuario", "mi_contraseña", "mi_bd");
     * @param int $nPagina Número de la página actual para la paginación. Ejemplo: 1
     * @param string $tabla Nombre de la tabla principal de donde se leerán los datos. Ejemplo: 'usuarios'
     * @param array $filtro Filtros a aplicar en la consulta, en formato de clave => valor. Ejemplo: ['activo' => "1"] para filtrar de forma negativa ['activo' => "!0"] para buscar por LIKE ['nombre' => "?Juan"]
     * @param array $datosRelacionados Datos relacionados a incluir en la consulta. Array asociativo donde la clave es el nombre de la columna en la tabla principal que relaciona con la tabla secundaria, y el valor es el nombre de la tabla secundaria. Ejemplo: ['id_departamento' => 'departamentos']
     * @param string|null $search Cadena de búsqueda para aplicar en todos los campos de la tabla principal y las tablas relacionadas. Opcional. Ejemplo: 'Juan'
     * @param string|null $orderBy Cadena de ordenamiento SQL para aplicar en la consulta. Opcional. Ejemplo: 'ORDER BY nombre ASC'
     * @param array $camposRelacionados (Opcional) Array de campos relacionados a incluir en la consulta. Cada elemento del array debe ser un array con tres elementos: nombre de la tabla, nombre de la columna, nombre de la columna condicional. Ejemplo: [['departamentos', 'nombre', 'id_departamento']]
     * @return array Array asociativo que contiene la página actual, el total de páginas y los datos de la consulta. Ejemplo de retorno: ['paginaActual' => 1, 'paginasTotales' => 5, 'data' => [...]]
     */
    static public function read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search = null, $orderBy = null, $camposRelacionados = []) {
        $sql = "SELECT $tabla.*";
        $countSql = "SELECT COUNT(*) as total";
    
        if (!empty($datosRelacionados)) {
            foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                $alias = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                $result = $conn->query("SHOW COLUMNS FROM $tablaExtra");
                while ($row = $result->fetch_assoc()) {
                    $sql .= ", $tablaExtra." . $row['Field'] . " AS " . $alias . $row['Field'];
                }
            }
        }
    
        if (!empty($camposRelacionados)) {
            foreach ($camposRelacionados as $relacion) {
                list($tablaExtra, $columna, $idCondicional) = $relacion;
                $alias = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                $sql .= ", $tablaExtra.$columna AS " . $alias . $columna;
            }
        }
    
        $sql .= " FROM $tabla";
        $countSql .= " FROM $tabla";
    
        if (!empty($datosRelacionados)) {
            foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                $sql .= " LEFT JOIN $tablaExtra ON $tabla.$nombreColumna = $tablaExtra.id";
                $countSql .= " LEFT JOIN $tablaExtra ON $tabla.$nombreColumna = $tablaExtra.id";
            }
        }
    
        if (!empty($camposRelacionados)) {
            foreach ($camposRelacionados as $relacion) {
                list($tablaExtra, $columna, $idCondicional) = $relacion;
                $sql .= " LEFT JOIN $tablaExtra ON $tabla.$idCondicional = $tablaExtra.id";
                $countSql .= " LEFT JOIN $tablaExtra ON $tabla.$idCondicional = $tablaExtra.id";
            }
        }
    
        $where = [];
        if (!empty($filtro)) {
            foreach ($filtro as $key => $value) {
                if (strpos($key, '___') !== false) {
                    $key = explode('___', $key)[0];
                }
        
                if (strpos($value, '!') === 0) {
                    $value = substr($value, 1);
                    $value = $conn->real_escape_string($value);
                    $where[] = "$tabla.$key NOT LIKE '%$value%'";
                } else if (strpos($value, '?') !== false) {
                    $value = str_replace('?', '', $value);
                    $value = $conn->real_escape_string($value);
                    $where[] = "$tabla.$key LIKE '%$value%'"; 
                } else {
                    $value = $conn->real_escape_string($value);
                    // Verificar si la columna pertenece a una tabla relacionada
                    $columnFound = false;
                    foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                        $alias = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                        if (strpos($key, $alias) === 0) {
                            $where[] = "$tablaExtra." . str_replace($alias, '', $key) . " = '$value'";
                            $columnFound = true;
                            break;
                        }
                    }
                    if (!$columnFound) {
                        $where[] = "$tabla.$key = '$value'";
                    }
                }
            }
        }
    
        if (!empty($search)) {
            if (strpos($search, '::') !== false) {
                list($nombre_columna, $valor_a_buscar) = explode('::', $search);
                $valor_a_buscar = $conn->real_escape_string($valor_a_buscar);
    
                if ($valor_a_buscar === '') {
                    $where[] = "$tabla.$nombre_columna = ''";
                } elseif (strtolower($valor_a_buscar) === 'null') {
                    $where[] = "$tabla.$nombre_columna IS NULL";
                } else {
                    $where[] = "$tabla.$nombre_columna LIKE '%$valor_a_buscar%'";
                }
            } else {
                $searchConditions = [];
                $result = $conn->query("SHOW COLUMNS FROM $tabla");
                while ($row = $result->fetch_assoc()) {
                    $searchConditions[] = "$tabla." . $row['Field'] . " LIKE '%$search%'";
                }
    
                if (!empty($datosRelacionados)) {
                    foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                        $result = $conn->query("SHOW COLUMNS FROM $tablaExtra");
                        while ($row = $result->fetch_assoc()) {
                            $alias = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                            $searchConditions[] = "$tablaExtra." . $row['Field'] . " LIKE '%$search%'";
                        }
                    }
                }
    
                if (!empty($searchConditions)) {
                    $where[] = '(' . implode(' OR ', $searchConditions) . ')';
                }
            }
        }
    
        if (!empty($where)) {
            $whereAnd = [];
            $whereOr = [];
            foreach ($where as $key => $value) {
                if (strpos($value, '||') !== false) {
                    $value = str_replace('||', '', $value);
                    $whereOr[] = $value;
                } else {
                    $whereAnd[] = $value;
                }
            }
    
            if (!empty($whereAnd)) {
                $sql .= " WHERE " . implode(' AND ', $whereAnd);
                $countSql .= " WHERE " . implode(' AND ', $whereAnd);
            }
    
            if (!empty($whereOr)) {
                $orCondition = "(" . implode(' OR ', $whereOr) . ")";
                if (!empty($whereAnd)) {
                    $sql .= " AND " . $orCondition;
                    $countSql .= " AND " . $orCondition;
                } else {
                    $sql .= " WHERE " . $orCondition;
                    $countSql .= " WHERE " . $orCondition;
                }
            }
        }
    
        if (!empty($orderBy)) {
            $sql .= " ";
            $sql .= $orderBy;
        } else {
            $sql .= " ORDER BY $tabla.id DESC";
        }
    
        $filasPorPagina = $_SESSION['rows_per_page'];
    
        $resultTotalRows = $conn->query($countSql);
        $totalRows = $resultTotalRows->fetch_assoc()['total'];
    
        $totalPaginas = ceil($totalRows / $filasPorPagina);
    
        $sql .= " LIMIT $filasPorPagina";
    
        $offset = ($nPagina - 1) * $filasPorPagina;
    
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }
    
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
    
        $dataFormatPending = $data;
        foreach ($dataFormatPending as &$d) {
            foreach ($d as $key => $value) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $keyCheck = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                    if (strpos($key, $keyCheck) === 0) {
                        $newKey = str_replace($keyCheck, "", $key);
                        $d[$keyCheck . $newKey] = $value;
                        //unset($d[$key]);
                    }
                }
            }
            // Agregar campos relacionados vacíos si no existen
            foreach ($camposRelacionados as $relacion) {
                list($tablaExtra, $columna, $idCondicional) = $relacion;
                $alias = strtoupper(substr($tablaExtra, 0, 3)) . "_";
                $keyCheck = $alias . $columna;
                if (!isset($d[$keyCheck])) {
                    $d[$keyCheck] = "";
                }
            }
        }
        unset($d);
    
        $data = $dataFormatPending;
    
        return [
            'paginaActual' => $nPagina,
            'paginasTotales' => $totalPaginas,
            'data' => $data
        ];
    }

    /**
    * Lee datos de múltiples tablas en una base de datos y los une en una sola respuesta paginada.
    * 
    * Esta función realiza consultas a múltiples tablas, opcionalmente aplicando filtros y búsqueda,
    * y une los resultados. También soporta la inclusión de datos relacionados de otras tablas.
    * La paginación se maneja basada en el número de página proporcionado y el número de filas por página almacenado en la sesión.
    * 
    * @param mysqli $conn Conexión a la base de datos.
    * @param int $nPagina Número de la página actual para la paginación.
    * @param array $tablas Array de nombres de tablas de donde leer los datos.
    * @param array $filtro (Opcional) Array asociativo de condiciones para filtrar los datos (columna => valor).
    * @param array $datosRelacionados (Opcional) Array asociativo que especifica los datos relacionados a incluir (nombreColumna => tablaExtra).
    * @param string|null $search (Opcional) Cadena de búsqueda para aplicar a todas las columnas de las tablas.
    * 
    * @return array Retorna un array asociativo con la página actual, el total de páginas y los datos leídos.
    * 
    * Ejemplo de uso:
    * 
    * $conn = new mysqli('localhost', 'usuario', 'contraseña', 'mi_base_de_datos');
    * $tablas = ['usuarios', 'productos'];
    * $filtro = ['estado' => 'activo'];
    * $datosRelacionados = ['categoria_id' => 'categorias'];
    * $resultado = readFromMultipleTables($conn, 1, $tablas, $filtro, $datosRelacionados, 'búsqueda');
    * 
    */
    static public function readFromMultipleTables($conn, $nPagina, $tablas, $filtro = [], $datosRelacionados = [], $search = null) {
        //comprobar que todas las tablas tienen la misma estructura y si no la tienen añadir las columnas que faltan a las tablas que no las tienen
        $columnas = [];
        foreach ($tablas as $tabla) {
            $result = $conn->query("SHOW COLUMNS FROM $tabla");
            while ($row = $result->fetch_assoc()) {
                $columnas[$tabla][] = $row['Field'];
            }
        }
        
        //obtener las columnas que no están en todas las tablas
        $columnasFaltantes = [];
        foreach ($columnas as $tabla => $columnasTabla) {
            foreach ($columnasTabla as $columna) {
                foreach ($columnas as $tabla2 => $columnasTabla2) {
                    if (!in_array($columna, $columnasTabla2)) {
                        $columnasFaltantes[$tabla2][] = $columna;
                    }
                }
            }
        }

        //añadir las columnas faltantes a las tablas que no las tienen
        $sqlIgualarTablas = "";
        foreach ($columnasFaltantes as $tabla => $columnasFaltantesTabla) {
            foreach ($columnasFaltantesTabla as $columna) {
                $sqlIgualarTablas .= "ALTER TABLE $tabla ADD COLUMN $columna VARCHAR(255);";                
            }
        }

        if($sqlIgualarTablas != ""){
            $conn->multi_query($sqlIgualarTablas);
        }

        $unionQueries = [];
        foreach ($tablas as $tabla) {
            $selectClause = "SELECT '$tabla' AS source_table, $tabla.*";
            if (!empty($datosRelacionados)) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $alias = $tablaExtra . "_" . $nombreColumna;
                    $result = $conn->query("SHOW COLUMNS FROM $tablaExtra");
                    while ($row = $result->fetch_assoc()) {
                        $selectClause .= ", $alias." . $row['Field'] . " AS " . $alias . "_" . $row['Field'];
                    }
                }
            }
            $selectClause .= " FROM $tabla";
            $whereConditions = [];
            if (!empty($filtro)) {
                foreach ($filtro as $key => $value) {
                    if (strpos($key, '___') !== false) {
                        //eliminar ___ y lo que sigue
                        $key = explode('___', $key)[0];
                    }

                    if (strpos($value, '!') === 0) {
                        // Remueve el prefijo "!" del valor
                        $value = substr($value, 1);
                        // Asegúrate de escapar el valor para seguridad
                        $value = $conn->real_escape_string($value);
                        // Construye la condición con NOT LIKE o !=
                        $whereConditions[] = "$tabla.$key NOT LIKE '%$value%'";
                    } else {
                        // Asegúrate de escapar el valor para seguridad
                        $value = $conn->real_escape_string($value);
                        // Construye la condición con LIKE o =
                        $whereConditions[] = "$tabla.$key = '$value'";
                    }
                }
            }
            if (!empty($search)) {
                $searchConditions = [];
                $result = $conn->query("SHOW COLUMNS FROM $tabla");
                while ($row = $result->fetch_assoc()) {
                    $searchConditions[] = "$tabla." . $row['Field'] . " LIKE '%$search%'";
                }
                if (!empty($searchConditions)) {
                    $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
                }
            }
            if (!empty($whereConditions)) {
                $selectClause .= " WHERE " . implode(' AND ', $whereConditions);
            }
            $unionQueries[] = $selectClause;
        }
    
        if (empty($unionQueries)) {
            // Manejar el caso cuando $unionQueries está vacío
            // Por ejemplo, podría ser retornar un arreglo vacío o un mensaje de error específico
            return [
                'paginaActual' => $nPagina,
                'paginasTotales' => 0,
                'data' => []
            ];
        }
    
        $sql = implode(' UNION ALL ', $unionQueries) . " ORDER BY reg_date DESC";
        $filasPorPagina = $_SESSION['rows_per_page'] ?? 10; // Default to 10 if not set
    
        $sql .= " LIMIT $filasPorPagina";
    
        $offset = ($nPagina - 1) * $filasPorPagina;
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }
    
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        // Assuming all tables have a 'timestamp' column for total count calculation
        $totalRows = 0;
        foreach ($tablas as $tabla) {
            $resultTotalRows = $conn->query("SELECT COUNT(*) as total FROM $tabla");
            $totalRows += $resultTotalRows->fetch_assoc()['total'];
        }
        $totalPaginas = ceil($totalRows / $filasPorPagina);
        return [
            'paginaActual' => $nPagina,
            'paginasTotales' => $totalPaginas,
            'data' => $data
        ];
    }

    static public function update($conn, $tabla, $id, $data){
        // Inicializar la consulta SQL
        $sql = "UPDATE $tabla SET ";
        $parts = array();
        foreach ($data as $key => $value) {
            $parts[] = "$key = '$value'";
        }
        $sql .= implode(", ", $parts);
        $sql .= " WHERE id = $id";
    
        // Ejecutar la consulta SQL
        $result = $conn->query($sql);
    
        // Comprobar si las columnas y los ID existen
        if (!$result) {
            throw new Exception("Error en la consulta SQL: " . $conn->error);
        }
    
        // Devolver el ID del nuevo registro
        return true;
    }

    /**
    * Elimina un registro de la base de datos después de verificar que el ID no exista en otras tablas.
    *
    * @param mysqli $conn Conexión a la base de datos.
    * @param string $tabla Nombre de la tabla de la cual se eliminará el registro.
    * @param int $id ID del registro a eliminar.
    * @param array $check Tablas y columnas que deben ser verificadas para evitar eliminar un ID que esté referenciado en otras tablas. Debe ser un array  asociativo donde las claves son los nombres de las tablas y los valores son los nombres de las columnas.
    * @return bool Devuelve true si la eliminación fue exitosa, false si el ID está referenciado en otras tablas.
    * @throws Exception Si hay un error en la consulta SQL.
    *
    * Ejemplo de uso:
    * 
    * $conn = new mysqli('localhost', 'usuario', 'contraseña', 'base_de_datos');
    * $tabla = 'usuarios';
    * $id = 123;
    * $check = [
    *     'pedidos' => 'usuario_id',
    *     'comentarios' => 'autor_id'
    * ];
    * 
    * try {
    *     $resultado = MiClase::delete($conn, $tabla, $id, $check);
    *     if ($resultado) {
    *         echo "Registro eliminado exitosamente.";
    *     } else {
    *         echo "El ID está referenciado en otras tablas.";
    *     }
    * } catch (Exception $e) {
    *     echo "Error: " . $e->getMessage();
    * }
    */
    static public function delete($conn, $tabla, $id, $check){
        //antes de eliminar una fila por su id se debe comprobar si el id existe en otras columnas de otras tablas definidas en check {tablaPrincipal:nombreColumna}, si eso ocurre devolver false
        
        //comprobar si el id existe en otras columnas de otras tablas
        foreach ($check as $tablaCheck => $columna) {
            $sql = "SELECT * FROM $tablaCheck WHERE $columna = $id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                return false;
            }
        }

        // Inicializar la consulta SQL
        $sql = "DELETE FROM $tabla WHERE id = $id";

        // Ejecutar la consulta SQL
        $result = $conn->query($sql);

        // Comprobar si las columnas y los ID existen
        if (!$result) {
            throw new Exception("Error en la consulta SQL: " . $conn->error);
        }

        return true;
    }

    /**
     * Realiza una consulta a la base de datos para obtener un solo registro de una tabla específica, con opciones para incluir datos relacionados y aplicar filtros.
     * 
     * @param mysqli $conn Conexión activa a la base de datos.
     * @param string $tabla Nombre de la tabla de donde leer los datos.
     * @param int $id ID del registro a obtener.
     * @param array $filtro (Opcional) Filtros a aplicar en la consulta, en formato de clave => valor.
     * @param array $datosRelacionados (Opcional) Datos relacionados a incluir en la consulta. Array asociativo donde la clave es el nombre de la columna en la tabla principal que relaciona con la tabla secundaria, y el valor es el nombre de la tabla secundaria.
     * 
     * @return array|null Retorna un array asociativo con los datos del registro encontrado, o null si no se encontraron resultados.
     * 
     * Ejemplo de uso:
     * 
     * $conn = new mysqli('localhost', 'usuario', 'contraseña', 'mi_base_de_datos');
     * $tabla = 'usuarios';
     * $id = 1;
     * $filtro = ['activo' => '1'];
     * $datosRelacionados = ['id_departamento' => 'departamentos']; 
     */
    static public function getById($conn, $tabla, $id, $filtro, $datosRelacionados) {
        $sql = "SELECT * FROM $tabla WHERE id = $id";
    
        // Aplicar el filtro si existe
        if (!empty($filtro)) {
            $sql .= " AND ";
            foreach ($filtro as $key => $value) {
                $sql .= "$key = '$value' AND ";
            }
    
            // Eliminar el último 'AND'
            $sql = substr($sql, 0, -5);
        }
    
        // Ejecutar la consulta SQL
        $result = $conn->query($sql);
    
        // Verificar si se encontraron resultados
        if ($result->num_rows == 0) {
            // No se encontraron resultados
            return null; // O puedes devolver un array vacío dependiendo de tus necesidades
        }
    
        // Formatear los resultados en un array
        $data = $result->fetch_all(MYSQLI_ASSOC);
    
        if (!empty($datosRelacionados)) {
            foreach ($data as &$row) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    // Obtener el id de la tabla principal
                    $id = $row[$nombreColumna];
                    $sqlExtra = "SELECT * FROM $tablaExtra WHERE id = $id";
                    $resultExtra = $conn->query($sqlExtra);
    
                    while ($rowExtra = $resultExtra->fetch_assoc()) {
                        foreach ($rowExtra as $key => $value) {
                            $prefixedKey = strtoupper(substr($tablaExtra, 0, 3)) . '_' . $key;
                            $row[$prefixedKey] = $value;
                        }
                    }
                }
            }
        }
    
        // Asumiendo que siempre queremos devolver el primer elemento si existe
        $data = $data[0] ?? null; // Usar el operador de fusión de null en caso de que $data esté vacío
    
        // Devolver los resultados
        return $data;
    }

    static public function search($conn, $nPagina, $tabla, $busqueda, $filtro, $datosRelacionados){
        //obtener un array de tablas relacionadas con la tabla principal
        $tablasRelacionadas = [];
        foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
            $tablasRelacionadas[] = $tablaExtra;
        }
     
        //buscar en las tablas relacionadas guardar nombreDeTabla => [ids] en $resultadosRelacionados
        $resultadosRelacionados = [];
        foreach ($tablasRelacionadas as $tablaExtra) {
            //$busqueda NO ES UN ARRAY es un string con el valor a buscar en todas las columnas de la tabla
            $sql = "SELECT * FROM $tablaExtra WHERE ";
            $columnas = $conn->query("SHOW COLUMNS FROM $tablaExtra");
            while($columna = $columnas->fetch_assoc()){
                if (strpos($columna['Type'], 'char') !== false || strpos($columna['Type'], 'text') !== false) {
                    $sql .= $columna['Field'] . " LIKE '%" . $busqueda . "%' OR ";
                }
            }

            //eliminar el último 'OR'
            $sql = substr($sql, 0, -4);

            $result = $conn->query($sql);
            $resultadosRelacionados[$tablaExtra] = [];

            while($row = $result->fetch_assoc()){
                $resultadosRelacionados[$tablaExtra][] = $row['id'];
            }
        }

        //buscar en todas las columnas en la tabla principal ($busqueda NO ES UN ARRAY es un string con el valor a buscar)
        $resultadosTablaPrincipal = [];

        $sql = "SELECT * FROM $tabla WHERE ";
        $columnas = $conn->query("SHOW COLUMNS FROM $tabla");
        while($columna = $columnas->fetch_assoc()){
            if (strpos($columna['Type'], 'char') !== false || strpos($columna['Type'], 'text') !== false) {
                $sql .= $columna['Field'] . " LIKE '%" . $busqueda . "%' OR ";
            }
        }

        //eliminar el último 'OR'
        $sql = substr($sql, 0, -4);

        //aplicar el filtro si existe
        if (!empty($filtro)) {
            $sql .= " AND ";
            foreach ($filtro as $key => $value) {
                $sql .= "$key = '$value' AND ";
            }

            //eliminar el último 'AND'
            $sql = substr($sql, 0, -5);
        }

        // Ejecutar la consulta SQL
        $result = $conn->query($sql);

        //insertar las filas completas en $resultadosTablaPrincipal
        while($row = $result->fetch_assoc()){
            $resultadosTablaPrincipal[] = $row;
        }

        //buscar en las tablas relacionadas guardar nombreDeTabla => [ids] en $resultadosRelacionados
        $resultadosRelacionadosTablaPrincipal = [];
        foreach ($resultadosRelacionados as $nombreTabla => $ids) {
            //nombre de la columna relacionada con la tabla principal de $datosRelacionados
            $nombreColumna = array_search($nombreTabla, $datosRelacionados);

            foreach ($ids as $id) {
                $sql = "SELECT * FROM $tabla WHERE $nombreColumna = $id";

                //aplicar el filtro si existe
                if (!empty($filtro)) {
                    $sql .= " AND ";
                    foreach ($filtro as $key => $value) {
                        $sql .= "$key = '$value' AND ";
                    }

                    //eliminar el último 'AND'
                    $sql = substr($sql, 0, -5);
                }

                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()){
                    $resultadosRelacionadosTablaPrincipal[] = $row;
                }
            }
        }

        //unir los resultados de la tabla principal y las tablas relacionadas
        $resultados = array_merge($resultadosTablaPrincipal, $resultadosRelacionadosTablaPrincipal);

        //eliminar duplicados
        $resultados = array_map("unserialize", array_unique(array_map("serialize", $resultados)));

        // Calcular el número de filas por página
        $filasPorPagina = $_SESSION['rows_per_page'];

        // Calcular el número de páginas
        $iniciar = ($nPagina-1) * $filasPorPagina;
        $finalizar = $iniciar + $filasPorPagina;

        //obtener de resultados d
        $resultados = array_slice($resultados, $iniciar, $finalizar);

        //obtener los datos relacionados
        foreach ($resultados as $key => $value) {
            foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                $id = $value[$nombreColumna];
                $sql = "SELECT * FROM $tablaExtra WHERE id = $id";
                $result = $conn->query($sql);
                $dataExtra = $result->fetch_assoc();
                $resultados[$key][$tablaExtra] = $dataExtra;
            }
        }

        return [
            'paginaActual' => $nPagina,
            'paginasTotales' => ceil(count($resultados) / $filasPorPagina),
            'data' => $resultados
        ];
    }

    static public function fileToUrl($base64, $nombreArchivo){
        //eliminar del nombre del archivo cualquier caracter que no sea alfanumérico o un punto
        $nombreArchivo = preg_replace("/[^a-zA-Z0-9.]/", "", $nombreArchivo);
        //añadir al nombre del archivo un código unico
        $nombreArchivo = uniqid() . $nombreArchivo;
        //los archivos se guardan en la carpeta api/public
        $ruta = "public/" . $nombreArchivo;
        $archivo = fopen($ruta, "w");
        fwrite($archivo, base64_decode($base64));
        fclose($archivo);
        return $ruta;
    }

    /**
     * Envia un mensaje de WhatsApp a un usuario.
     *
     * @param string $userPhone Número de teléfono del usuario.
     * @param string $message Mensaje a enviar.
     * @param string $file Archivo a enviar (opcional).
     * @param string $codigoAdministradorBot Código del administrador del bot.
     * @param string $passwordAdministradorBot Contraseña del administrador del bot.
     * @return string Respuesta del servidor.
    */
    static public function sendMessageWhatsapp($userPhone, $message, $file, $codigoAdministradorBot, $passwordAdministradorBot){
        $url = "https://wairbothook.wairbot.es/send";
        $data = [
            'userPhone' => $userPhone,
            'message' => $message,
            'file' => $file,
            'codigoAdministradorBot' => $codigoAdministradorBot,
            'passwordAdministradorBot' => $passwordAdministradorBot,
        ];
        $data = json_encode($data);
        
        //curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
