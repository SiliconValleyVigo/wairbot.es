<?php
 
class IncidenciasCerradas{
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
                $tablas[] = "incidencias_cerradas_" . $codigo;
            }

            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::readFromMultipleTables($conn, $nPagina, $tablas, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Incidencias obtenidas correctamente',
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

            $tabla = "incidencias_cerradas_" . $codigoBot;
            $filtro = [];
            $datosRelacionados = [];

            $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

            return [
                'status' => 'success',
                'message' => 'Incidencias obtenidas correctamente',
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

        $tabla = "incidencias_cerradas_" . $codigoBot;
        $filtro = [];
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si en result el campo tecnico es distinto de null, se obtienen los datos del técnico de la tabla tecnicos_$codigoBot
        if($result['tecnico'] != null){
            $telefono = $result['tecnico'];
            $tablaTecnico = "tecnicos_" . $codigoBot;

            $sql = "SELECT * FROM $tablaTecnico WHERE telefono = '$telefono'";
            $resultTecnico = $conn->query($sql)->fetch_assoc();

            $result['datosTecnico'] = $resultTecnico;
        }

        return [
            'status' => 'success',
            'message' => 'Incidencia obtenida correctamente',
            'data' => $result
        ];
    }
}