<?php
 
 
class Lugares{
    static public function listar($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $codigoBot = array_key_exists('filtro', $request) ? $request['filtro'] : "";
        $id_agente = $_SESSION['user_id'];

        //si codigoBot es vacio, se obtienen las tablas y se buscan por readFromMultipleTables si no se busca por read
        if($codigoBot == ""){
            //obtener los codigos de los bots del agente
            $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";
            $result = $conn->query($sql);

            $codigos = [];
            while($row = $result->fetch_assoc()){
                $codigos[] = $row['codigo_bot'];
            }

            $tablas = [];
            foreach($codigos as $codigo){
                $tablas[] = "lugares_" . $codigo;
            }

            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Lugares obtenidos correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
                ]
            ];

        }else{
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

            $tabla = "lugares_" . $codigoBot;
            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Lugares obtenidos correctamente',
                'data' => [
                    'paginaActual' => $result['paginaActual'],
                    'paginasTotales' => $result['paginasTotales'],
                    'data' => $result['data']
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

        $tabla = "lugares_" . $codigoBot;
        $filtro = [];
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

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

        $tabla = "lugares_" . $codigoBot;
        $check = [];

        $created = Tools::create($conn, $tabla, $data, $check);

        //obtener el json de la columna fields en tabla forms donde title es "incidencias" y admin_phone es codigoBot
        $sql = "SELECT fields FROM forms WHERE title = 'incidencias' AND admin_phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $codigoBot);
        $stmt->execute();
        $result = $stmt->get_result();

        $fields = [];
        while($row = $result->fetch_assoc()){
            $fields = json_decode($row['fields'], true);
        }

        //obtener elemento con name_column = lugar
        $lugar = [];
        foreach($fields as $field){
            if($field['name_column'] == 'lugar'){
                $lugar = $field;
            }
        }

        //añadir a label el nombre del lugar creado 
        $lugar1 = $data['lugar_1'];
        $lugar2 = $data['lugar_2'];

        $lugarParaAñadir = " ,$lugar1 ($lugar2)";

        //añadir a label el nombre del lugar creado
        $lugar['label'] .= $lugarParaAñadir;

        //si hay ": ," ó ":  ," ó ":," quitar la coma y los espacios extras
        $lugar['label'] = str_replace(": ,", ": ", $lugar['label']);
        $lugar['label'] = str_replace(":  ,", ": ", $lugar['label']);
        $lugar['label'] = str_replace(":,", ":", $lugar['label']);

        //susituir el elemento con name_column = lugar por el nuevo
        foreach($fields as $key => $field){
            if($field['name_column'] == 'lugar'){
                $fields[$key] = $lugar;
            }
        }

        //actualizar el json de la columna fields en tabla forms donde title es "incidencias" y admin_phone es codigoBot
        $fields = json_encode($fields);

        $sql = "UPDATE forms SET fields = ? WHERE title = 'incidencias' AND admin_phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fields, $codigoBot);
        $stmt->execute();

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Lugar creado correctamente',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'Error al crear el lugar',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el lugar',
                'error' => $created
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
        $sql = "SELECT id_agente FROM bots_comunidades_wairbot_central WHERE codigo_bot = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $codigoBot);
        $stmt->execute();
        $result = $stmt->get_result();

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

        $tabla = "lugares_" . $codigoBot;

        $updated = Tools::update($conn, $tabla, $id, $data);

        //obtener el json de la columna fields en tabla forms donde title es "incidencias" y admin_phone es codigoBot
        $sql = "SELECT fields FROM forms WHERE title = 'incidencias' AND admin_phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $codigoBot);
        $stmt->execute();
        $result = $stmt->get_result();

        $fields = [];
        while($row = $result->fetch_assoc()){
            $fields = json_decode($row['fields'], true);
        }

        //obtener elemento con name_column = lugar
        $lugar = [];
        foreach($fields as $field){
            if($field['name_column'] == 'lugar'){
                $lugar = $field;
            }
        }

        //añadir a label el nombre del lugar creado 
        $lugar1 = $data['lugar_1'];
        $lugar2 = $data['lugar_2'];

        $lugarParaAñadir = " ,$lugar1 ($lugar2)";

        //añadir a label el nombre del lugar creado
        $lugar['label'] .= $lugarParaAñadir;

        //si hay ": ," ó ":  ," ó ":," quitar la coma y los espacios extras
        $lugar['label'] = str_replace(": ,", ": ", $lugar['label']);
        $lugar['label'] = str_replace(":  ,", ": ", $lugar['label']);
        $lugar['label'] = str_replace(":,", ":", $lugar['label']);

        //susituir el elemento con name_column = lugar por el nuevo
        foreach($fields as $key => $field){
            if($field['name_column'] == 'lugar'){
                $fields[$key] = $lugar;
            }
        }

        //actualizar el json de la columna fields en tabla forms donde title es "incidencias" y admin_phone es codigoBot
        $fields = json_encode($fields);

        $sql = "UPDATE forms SET fields = ? WHERE title = 'incidencias' AND admin_phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fields, $codigoBot);
        $stmt->execute();

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Lugar actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el lugar',
                'error' => $updated
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

        $tabla = "lugares_" . $codigoBot;
        $check = [];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Lugar eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el lugar',
                'error' => $deleted
            ];
        }
    }
}

/*
BACKEND:
Clase: Lugares
Funciones:
    listar
        datos: nPagina, search, filtro

    verUno:
        datos: id, codigoBot

    crear
        datos: data, codigoBot

    editar
        datos: id, data, codigoBot

    borrar
        datos: id, codigoBot

data: lugar_1, lugar_2, id_Usuario, nombre_Usuario, referencia_Usuario, rol_Usuario, telefono_usuario, comprobado_Usuario, reg_date
*/