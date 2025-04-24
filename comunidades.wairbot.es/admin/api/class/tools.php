<?php

class Tools{
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
                        return [
                            'status' => 'error',
                            'result' => 'El valor de la columna ' . $column . ' ya existe'
                        ];
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
     * @param array $filtro Filtros a aplicar en la consulta, en formato de clave => valor. Ejemplo: ['activo' => "1"] para filtrar de forma negativa ['activo' => "!0"]
     * @param array $datosRelacionados Datos relacionados a incluir en la consulta. Array asociativo donde la clave es el nombre de la columna en la tabla principal que relaciona con la tabla secundaria, y el valor es el nombre de la tabla secundaria. Ejemplo: ['id_departamento' => 'departamentos']
     * @param string|null $search Cadena de búsqueda para aplicar en todos los campos de la tabla principal y las tablas relacionadas. Opcional. Ejemplo: 'Juan'
     * @param string|null $orderBy Cadena de ordenamiento SQL para aplicar en la consulta. Opcional. Ejemplo: 'ORDER BY nombre ASC'
     * @return array Array asociativo que contiene la página actual, el total de páginas y los datos de la consulta. Ejemplo de retorno: ['paginaActual' => 1, 'paginasTotales' => 5, 'data' => [...]]
     */
    static public function read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search = null, $orderBy = null) {
        $sql = "SELECT $tabla.*";
    
        if (!empty($datosRelacionados)) {
            foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                $alias = $tablaExtra . "_" . $nombreColumna;
                $result = $conn->query("SHOW COLUMNS FROM $tablaExtra");
                while ($row = $result->fetch_assoc()) {
                    $sql .= ", $alias." . $row['Field'] . " AS " . $alias . "_" . $row['Field'];
                }
            }
        }
    
        $sql .= " FROM $tabla";
    
        if (!empty($datosRelacionados)) {
            foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                $alias = $tablaExtra . "_" . $nombreColumna;
                $sql .= " LEFT JOIN $tablaExtra AS $alias ON $tabla.$nombreColumna = $alias.id";
            }
        }
    
        $where = [];
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
                    $where[] = "$tabla.$key NOT LIKE '%$value%'";
                } else {
                    // Asegúrate de escapar el valor para seguridad
                    $value = $conn->real_escape_string($value);
                    // Construye la condición con LIKE o =
                    $where[] = "$tabla.$key = '$value'";
                }
            }
        }
    
        if (!empty($search)) {
            $searchConditions = [];
            $result = $conn->query("SHOW COLUMNS FROM $tabla");
            while ($row = $result->fetch_assoc()) {
                $searchConditions[] = "$tabla." . $row['Field'] . " LIKE '%$search%'";
            }
        
            if (!empty($datosRelacionados)) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $result = $conn->query("SHOW COLUMNS FROM $tablaExtra");
                    while ($row = $result->fetch_assoc()) {
                        $alias = $tablaExtra . "_" . $nombreColumna;
                        $searchConditions[] = "$alias." . $row['Field'] . " LIKE '%$search%'";
                    }
                }
            }
        
            if (!empty($searchConditions)) {
                $where[] = '(' . implode(' OR ', $searchConditions) . ')';
            }
        }
        
        if (!empty($where)) {
            $whereAnd = [];
            $whereOr = [];
            foreach ($where as $key => $value) {
                if (strpos($value, '||') !== false) {
                    // Eliminar '||' y agregar a $whereOr
                    $value = str_replace('||', '', $value);
                    $whereOr[] = $value;
                } else {
                    // Agregar a $whereAnd
                    $whereAnd[] = $value;
                }
            }
        
            // Construir la parte AND de la consulta
            if (!empty($whereAnd)) {
                $sql .= " WHERE " . implode(' AND ', $whereAnd);
            }
        
            // Construir la parte OR de la consulta y asegurarse de que esté correctamente agrupada
            if (!empty($whereOr)) {
                $orCondition = "(" . implode(' OR ', $whereOr) . ")";
                if (!empty($whereAnd)) {
                    $sql .= " AND " . $orCondition;
                } else {
                    $sql .= " WHERE " . $orCondition;
                }
            }
        }
    
        if (!empty($orderBy)) {
            $sql .= $orderBy;
        } else {
            $sql .= " ORDER BY $tabla.id DESC";
        }
    
        $filasPorPagina = $_SESSION['rows_per_page'];
    
        $resultTotalRows = $conn->query("SELECT COUNT(*) as total FROM $tabla");
        $totalRows = $resultTotalRows->fetch_assoc()['total'];
    
        $totalPaginas = ceil($totalRows / $filasPorPagina);

        $sql .= " LIMIT $filasPorPagina";
    
        $offset = ($nPagina - 1) * $filasPorPagina;

        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        /*
        SELECT forms.* FROM forms
        WHERE forms.title NOT LIKE '%incidencia%'
        AND forms.title NOT LIKE '%_in_tec_%'
        AND forms.title NOT LIKE '%tecnico%'
        AND forms.title NOT LIKE '%lugares%'
        AND (
            forms.admin_phone = 'agente1prueba'
            OR forms.admin_phone = 'agente2prueba'
            OR forms.admin_phone = 'agente3prueba'
            OR forms.admin_phone = 'agente4prueba'
        ) 
        ORDER BY forms.id DESC
        LIMIT 24
        */
    
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
    
        $dataFormatPending = $data;
        foreach ($dataFormatPending as &$d) {
            foreach ($d as $key => $value) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $keyCheck = $tablaExtra . "_" . $nombreColumna . "_";
                    if (strpos($key, $keyCheck) !== false) {
                        //eliminar de key el nombre de la tabla y la columna
                        $newKey = str_replace($tablaExtra . "_" . $nombreColumna . "_", "", $key);
                        $d[$tablaExtra][$nombreColumna][$newKey] = $value;
                        unset($d[$key]);
                    }
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
    static public function readFromMultipleTables($conn, $nPagina, $tablas, $filtro = [], $datosRelacionados = [], $search = null, $columnasUnicas = []) {
        // Paso 1: Normalizar columnas en todas las tablas
        $columnas = [];
        foreach ($tablas as $tabla) {
            $result = $conn->query("SHOW COLUMNS FROM $tabla");
            while ($row = $result->fetch_assoc()) {
                $columnas[$tabla][] = $row['Field'];
            }
            $result->free();
        }
    
        // Encontrar columnas faltantes y normalizar tablas
        $todasLasColumnas = array_unique(array_merge(...array_values($columnas)));
        foreach ($tablas as $tabla) {
            $columnasFaltantes = array_diff($todasLasColumnas, $columnas[$tabla]);
            foreach ($columnasFaltantes as $columnaFaltante) {
                $sql = "ALTER TABLE $tabla ADD COLUMN `$columnaFaltante` VARCHAR(255) DEFAULT NULL";
                try {
                    $conn->query($sql);
                } catch (mysqli_sql_exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                        throw $e;
                    }
                }
            }
        }
    
        // Paso 2: Construir las consultas para unir todas las tablas
        $unionQueries = [];
        foreach ($tablas as $tabla) {
            $selectClause = "SELECT '$tabla' AS source_table, " . implode(", ", $todasLasColumnas);
            $selectClause .= " FROM $tabla";
    
            // Filtros
            $whereConditions = [];
            foreach ($filtro as $key => $value) {
                if (strpos($key, '___') !== false) {
                    $key = explode('___', $key)[0];
                }
    
                $value = $conn->real_escape_string($value);
                if (strpos($value, '!') === 0) {
                    $value = substr($value, 1);
                    $whereConditions[] = "`$key` NOT LIKE '%$value%'";
                } else {
                    $whereConditions[] = "`$key` LIKE '%$value%'";
                }
            }
    
            // Búsqueda global
            if ($search) {
                $searchConditions = [];
                foreach ($todasLasColumnas as $columna) {
                    $searchConditions[] = "`$columna` LIKE '%" . $conn->real_escape_string($search) . "%'";
                }
                $whereConditions[] = "(" . implode(" OR ", $searchConditions) . ")";
            }
    
            if (!empty($whereConditions)) {
                $selectClause .= " WHERE " . implode(" AND ", $whereConditions);
            }
    
            $unionQueries[] = $selectClause;
        }
    
        if (empty($unionQueries)) {
            return [
                'paginaActual' => $nPagina,
                'paginasTotales' => 0,
                'data' => []
            ];
        }
    
        $sql = implode(' UNION ALL ', $unionQueries);
    
        // Paso 3: Manejo de columnas únicas
        if (!empty($columnasUnicas)) {
            $columnasUnicasSql = implode(", ", $columnasUnicas);
            $sql = "SELECT * FROM ($sql) AS combined GROUP BY $columnasUnicasSql";
        }
    
        // Paso 4: Ordenar por reg_date si existe
        $regDateExists = true;
        foreach ($tablas as $tabla) {
            $result = $conn->query("SHOW COLUMNS FROM $tabla LIKE 'reg_date'");
            if ($result->num_rows == 0) {
                $regDateExists = false;
                break;
            }
            $result->free();
        }
    
        if ($regDateExists) {
            $sql .= " ORDER BY reg_date DESC";
        }
    
        // Paso 5: Aplicar paginación
        $filasPorPagina = $_SESSION['rows_per_page'] ?? 10;
        $offset = ($nPagina - 1) * $filasPorPagina;
        $sql .= " LIMIT $filasPorPagina OFFSET $offset";
    
        // Ejecutar la consulta y devolver resultados
        $result = $conn->query($sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    
        // Calcular total de filas
        $totalRows = 0;
        foreach ($tablas as $tabla) {
            $resultTotalRows = $conn->query("SELECT COUNT(*) as total FROM $tabla");
            $totalRows += $resultTotalRows->fetch_assoc()['total'];
            $resultTotalRows->free();
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

    static public function getById($conn, $tabla, $id, $filtro, $datosRelacionados){
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
    
        if(!empty($datosRelacionados)){
            $data = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($data as $key => $value) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $idRelacionado = $value[$nombreColumna];
                    $sqlExtra = "SELECT * FROM $tablaExtra WHERE id = $idRelacionado";
                    $resultExtra = $conn->query($sqlExtra);
                    $dataExtra = $resultExtra->fetch_assoc();
                    $data[$key][$tablaExtra][$nombreColumna] = $dataExtra;
                }
            }
        }else{
            // Formatear los resultados en un array
            $data = $result->fetch_all(MYSQLI_ASSOC);
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
}
