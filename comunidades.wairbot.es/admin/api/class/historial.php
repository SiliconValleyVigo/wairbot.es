<?php

class Historial{
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
                $tablas[] = "historial_formularios_" . $codigo;
            }

            //eliminar del array los nombres de tablas que no existan de la DB
            $tablas = array_filter($tablas, function($tabla) use ($conn){
                $sql = "SHOW TABLES LIKE '$tabla'";
                $result = $conn->query($sql);
                return $result->num_rows > 0;
            });

            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Historial obtenido correctamente',
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

            $tablas = ["historial_formularios_" . $codigoBot];
            $filtro = [];
            $datosRelacionados = [];

            //comprobar si existe la tabla en la DB y si no existe devolver datos vacios
            $sql = "SHOW TABLES LIKE 'historial_formularios_" . $codigoBot . "'";
            $result = $conn->query($sql);

            if($result->num_rows == 0){
                return [
                    'status' => 'success',
                    'message' => 'Historial obtenido correctamente',
                    'data' => [
                        'paginaActual' => 1,
                        'paginasTotales' => 1,
                        'data' => []
                    ]
                ];
            }

            $result = Tools::read($conn, $nPagina, $tablas[0], $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Historial obtenido correctamente',
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
        $tabla = "historial_formularios_" . $codigoBot;
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

        $filtro = [];
        $datosRelacionados = [];
        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Resultado obtenido correctamente',
            'data' => $result
        ];
    }
}