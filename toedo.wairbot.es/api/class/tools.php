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

    static public function read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search = null, $orderBy = null, $filtroNegativo = null) {
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
                $where[] = "$tabla.$key = '$value'";
            }
        }

        if (!empty($filtroNegativo)) {
            foreach ($filtroNegativo as $key => $value) {
                $where[] = "$tabla.$key != '$value'";
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
            $sql .= " WHERE " . implode(' AND ', $where);
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
    
        $offset = ($nPagina - 1) * $filasPorPagina;
    
        $sql .= " LIMIT $filasPorPagina OFFSET $offset";
    
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
        $result = $conn->query($sql); //SELECT * FROM maquinas WHERE id = 107 AND id_agente = '2'

        //$datosRelacionados = ['nombreColumna' => 'tablaExtra'] // [id_maquina => 'maquinas']
        //si datosRelacionados no está vacío obtener el id de la tabla principal y buscar en la tablaExtra y añadir la fila encontrada a la fila principal en la columna nombreColumna
        if(!empty($datosRelacionados)){
            $data = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($data as $key => $value) {
                foreach ($datosRelacionados as $nombreColumna => $tablaExtra) {
                    $id = $value[$nombreColumna];
                    $sql = "SELECT * FROM $tablaExtra WHERE id = $id";
                    $resultExtra = $conn->query($sql);
                    $dataExtra = $resultExtra->fetch_assoc();
                    $data[$key][$tablaExtra][$nombreColumna] = $dataExtra;
                }
            }
        }else{
            // Formatear los resultados en un array
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }

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