<?php
 
 
class Residentes{
    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];

        // Construir la cláusula de búsqueda si $search no está vacío
        $searchClause = "";
        if ($search) {
            $searchClause = " AND (name LIKE '%$search%' OR user_phone LIKE '%$search%' OR reference LIKE '%$search%' OR role LIKE '%$search%')";
        }

        // Si codigoBot es vacío, se obtienen las tablas y se buscan por readFromMultipleTables si no se busca por read
        if ($codigoBot == "") {
            // Obtener los códigos de los bots del agente
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while ($row = $result->fetch_assoc()) {
                $codigos[] = $row['codigo_bot'];
            }

            // Obtener de la tabla users las filas donde admin_phone sea igual a uno de los códigos de los bots y role sea igual a residente paginados
            $resultadosPorPagina = $_SESSION['rows_per_page'];
            $offset = ($nPagina - 1) * $resultadosPorPagina;
            $sql = "SELECT * FROM users WHERE role = 'residente' AND (";
            foreach ($codigos as $key => $codigo) {
                $sql .= "admin_phone = '$codigo'";
                if ($key < count($codigos) - 1) {
                    $sql .= " OR ";
                }
            }
            $sql .= ") $searchClause LIMIT $resultadosPorPagina OFFSET $offset";
            $result = $conn->query($sql);

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            // Obtener el número de páginas totales
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'residente' AND (";
            foreach ($codigos as $key => $codigo) {
                $sql .= "admin_phone = '$codigo'";
                if ($key < count($codigos) - 1) {
                    $sql .= " OR ";
                }
            }
            $sql .= ") $searchClause";
            $result = $conn->query($sql);
            $total = $result->fetch_assoc()['total'];

            $paginasTotales = ceil($total / $resultadosPorPagina);

            return [
                'status' => 'success',
                'message' => 'Residentes obtenidos correctamente',
                'data' => [
                    'paginaActual' => $nPagina,
                    'paginasTotales' => $paginasTotales,
                    'data' => $data
                ]
            ];

        } else {
            // Comprobar que el codigoBot pertenece al agente
            $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = '$codigoBot'";
            $result = $conn->query($sql);

            if ($result->num_rows == 0) {
                return [
                    'status' => 'error',
                    'message' => 'El código no existe'
                ];
            }

            $id_agente_comunidad = $result->fetch_assoc()['id_agente'];

            if ($id_agente_comunidad != $id_agente) {
                return [
                    'status' => 'error',
                    'message' => 'No tienes permisos para ver estos lugares'
                ];
            }

            // Obtener de la tabla users las filas donde admin_phone sea igual a codigoBot y role sea igual a residente paginados
            $resultadosPorPagina = $_SESSION['rows_per_page'];
            $offset = ($nPagina - 1) * $resultadosPorPagina;
            $sql = "SELECT * FROM users WHERE role = 'residente' AND admin_phone = '$codigoBot' $searchClause LIMIT $resultadosPorPagina OFFSET $offset";
            $result = $conn->query($sql);

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            // Obtener el número de páginas totales
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'residente' AND admin_phone = '$codigoBot' $searchClause";
            $result = $conn->query($sql);
            $total = $result->fetch_assoc()['total'];

            $paginasTotales = ceil($total / $resultadosPorPagina);

            return [
                'status' => 'success',
                'message' => 'Residentes obtenidos correctamente',
                'data' => [
                    'paginaActual' => $nPagina,
                    'paginasTotales' => $paginasTotales,
                    'data' => $data
                ]
            ];
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

        $sql = "SELECT * FROM users WHERE id = $id";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'El lugar no existe'
            ];
        }

        $result = $result->fetch_assoc();

        return [
            'status' => 'success',
            'message' => 'Lugar obtenido correctamente',
            'data' => $result
        ];
    }
    
    static public function crear($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $codigoBot = $request['codigoBot'];
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

        $name = $data['name'];
        $user_phone = $data['user_phone'];
        $reference = $data['reference'];
        $role = "residente";
        $admin_phone = $codigoBot;
        $status = "true";

        $sql = "INSERT INTO users (name, user_phone, reference, role, admin_phone, status) VALUES ('$name', '$user_phone', '$reference', '$role', '$admin_phone', '$status')";
        $result = $conn->query($sql);

        //en la tabla forms donde admin_phone sea igual a codigoBot y role sea vacio o null role se actualiza a residente
        $sql = "UPDATE forms SET role = 'residente' WHERE admin_phone = '$codigoBot' AND (role = '' OR role IS NULL)";
        $result = $conn->query($sql);

        if($result){
            return [
                'status' => 'success',
                'message' => 'Lugar creado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el lugar'
            ];
        }
    }

    static public function editar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $data = $request['data'];
        $codigoBot = $request['codigoBot'];
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

        $name = $data['name'];
        $user_phone = $data['user_phone'];
        $reference = $data['reference'];
        $role = "residente";
        $admin_phone = $codigoBot;
        $status = "true";

        $sql = "UPDATE users SET name = '$name', user_phone = '$user_phone', reference = '$reference', role = '$role', admin_phone = '$admin_phone', status = '$status' WHERE id = $id";
        $result = $conn->query($sql);

        if($result){
            return [
                'status' => 'success',
                'message' => 'Lugar editado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al editar el lugar'
            ];
        }
    }

    static public function borrar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $codigoBot = $request['codigoBot'];
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

        $sql = "DELETE FROM users WHERE id = $id";
        $result = $conn->query($sql);

        if($result){
            return [
                'status' => 'success',
                'message' => 'Lugar borrado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al borrar el lugar'
            ];
        }
    }
}

/*
BACKEND:
Clase: Residentes
Funciones:
- listar:
    - Request: nPagina, search, filtro
- verUno:
    - Request: id, codigoBotId
- crear:
    - Request: data, codigoBot
    - data: nombre, telefono, referencia
- editar:
    - Request: id, data, codigoBot
- borrar:
    - Request: id, codigoBot


*/