<?php
 
class IncidenciasAbiertas{
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
                $tablas[] = "incidencias_" . $codigo;
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

            $tabla = "incidencias_" . $codigoBot;
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
    
        $tabla = "incidencias_" . $codigoBot;
        $filtro = [];
        $datosRelacionados = [];
    
        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //obtener los datos del tecnico por la columna "tecnico" buscando en la tabla de tecnicos y tecnicos_auxiliares
        $sql = "SELECT * FROM tecnicos_$codigoBot WHERE telefono = '" . $result['tecnico'] . "'";
        $resultTecnico = $conn->query($sql)->fetch_assoc();

        if($resultTecnico == null){
            $sql = "SELECT * FROM tecnicos_auxiliares_$codigoBot WHERE telefono = '" . $result['tecnico'] . "'";
            $resultTecnico = $conn->query($sql)->fetch_assoc();
        }

        //enviar datos en blanco si no se encuentra el tecnico
        if($resultTecnico == null){
            $resultTecnico = [
                'id' => "",
                'tipo' => "",
                'actividad' => "",
                'nombre' => "Sin Asignar",
                'referencia' => "",
                'telefono' => "",
                'id_Usuario' => "",
                'nombre_Usuario' => "",
                'referencia_Usuario' => "",
                'rol_Usuario' => "",
                'telefono_usuario' => "",
                'comprobado_Usuario' => "",
                'reg_date' => ""
            ];
        }
    
        /*
        //obtener todos los telefonos de los tecnicos y los tecnicos auxiliares
        $sql = "SELECT telefono FROM tecnicos_$codigoBot";
        $resultTelefonos = $conn->query($sql);
    
        $telefonos = [];
        while($row = $resultTelefonos->fetch_assoc()){
            $telefonos[] = $row['telefono'];
        }
    
        $sql = "SELECT telefono FROM tecnicos_auxiliares_$codigoBot";
        $resultTelefonosAux = $conn->query($sql);
    
        while($row = $resultTelefonosAux->fetch_assoc()){
            $telefonos[] = $row['telefono'];
        }
    
        //eliminar los duplicados
        $telefonos = array_unique($telefonos);
    
        //buscar el tecnico que tiene asignada la incidencia
        $tecnico = "";
        foreach($telefonos as $telefono){
            $tablaTecnico = "incidencias_tecnico_" . $telefono . "_" . $codigoBot;
    
            $sql = "SELECT * FROM $tablaTecnico WHERE codigo = '" . $result['codigo'] . "'";
            $resultTecnico = $conn->query($sql);
    
            if($resultTecnico->num_rows > 0){
                $tecnico = $telefono;
                break;
            }
        }
    
        //obtener los datos del tecnico
        $sql = "SELECT * FROM tecnicos_$codigoBot WHERE telefono = '$tecnico'";
        $resultTecnico = $conn->query($sql)->fetch_assoc();

        if($resultTecnico == null){
            $sql = "SELECT * FROM tecnicos_auxiliares_$codigoBot WHERE telefono = '$tecnico'";
            $resultTecnico = $conn->query($sql)->fetch_assoc();
        }*/
    
        $result['datosTecnico'] = $resultTecnico;
        $result['tecnico'] = $resultTecnico['nombre'];

        //obtener las ampliaciones de la incidencia de la tabla incidencias_ampliadas_$codigoBot y añadirlo al texto de acciones de la incidencia en primer lugar
        $tablaAmpliaciones = "incidencias_ampliadas_" . $codigoBot;
        $sql = "SELECT * FROM $tablaAmpliaciones WHERE codigo = '" . $result['codigo'] . "'";
        $resultAmpliaciones = $conn->query($sql);

        //añadir las acciones al texto de acciones de la incidencia en primer lugar
        $acciones = "";
        while($row = $resultAmpliaciones->fetch_assoc()){
            $acciones .= $row['texto'] . "\n\n";
        }

        $result['acciones'] = $acciones;
    
        return [
            'status' => 'success',
            'message' => 'Incidencia obtenida correctamente',
            'data' => $result
        ];
    }

    //obtener el codigoBot de la incidencia por su codigo
    static public function getCodigoBot($request){
        $conn = $_SESSION['db_connection'];
        $codigoIncidencia = $request['codigo'];

        //obtener todos los codigos de los bots del agente
        $id_agente = $_SESSION['user_id'];

        $sql = "SELECT codigo_bot FROM bots_comunidades_wairbot_central WHERE id_agente = $id_agente";

        $result = $conn->query($sql);

        $codigos = [];
        while($row = $result->fetch_assoc()){
            $codigos[] = $row['codigo_bot'];
        }

        $tablas = [];
        foreach($codigos as $codigo){
            $tablas[] = "incidencias_" . $codigo;
        }

        //obtener el nombre de la tabla que contiene el codigo de la incidencia
        $tabla = "";
        foreach($tablas as $tabla){
            $sql = "SELECT codigo FROM $tabla WHERE codigo = '$codigoIncidencia'";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                $tabla = $tabla;
                break;
            }
        }

        if($tabla == ""){
            return [
                'status' => 'error',
                'message' => 'La incidencia no existe'
            ];
        }

        $codigoBot = explode("_", $tabla)[1];

        return [
            'status' => 'success',
            'message' => 'Código obtenido correctamente',
            'data' => $codigoBot
        ];
    }

    static public function cerrar($request){
        $conn = $_SESSION['db_connection'];
        $codigoBot = $request['codigoBot2'];
        $codigoIncidencia = $request['codigoIncidencia'];
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

        //obtener los telefonos de los tecnicos
        $sql = "SELECT telefono FROM tecnicos_$codigoBot";
        $result = $conn->query($sql);

        $telefonos = [];
        while($row = $result->fetch_assoc()){
            $telefonos[] = $row['telefono'];
        }

        $tablasAbiertas = [];
        foreach($telefonos as $telefono){
            $tablasAbiertas[] = "incidencias_tecnico_" . $telefono . "_" . $codigoBot;
        }

        //obtener la incidencia
        $tabla = "incidencias_" . $codigoBot;
        $sql = "SELECT * FROM $tabla WHERE codigo = '$codigoIncidencia'";
        $result = $conn->query($sql);

        //insertar la incidencia en la tabla de cerradas
        $incidencia = $result->fetch_assoc();

        $incidenciaCerrada = [
            'lugar' => $incidencia['lugar'],
            'tipo' => $incidencia['tipo'],
            'descripcion' => $incidencia['descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas'],
            'imagen' => $incidencia['imagen'],
            'codigo' => $incidencia['codigo'],
            'usuario' => $incidencia['usuario'],
            'id_Usuario' => $incidencia['id_Usuario'],
            'nombre_Usuario' => $incidencia['nombre_Usuario'],
            'referencia_Usuario' => $incidencia['referencia_Usuario'],
            'rol_Usuario' => $incidencia['rol_Usuario'],
            'telefono_usuario' => $incidencia['telefono_usuario'],
            'comprobado_Usuario' => $incidencia['comprobado_Usuario'],
            'reg_date' => $incidencia['reg_date'],
            'tecnico' => $incidencia['tecnico'],
            'usuario' => $incidencia['usuario'],
            'acciones' => $incidencia['acciones']
        ];

        $tablaCerradas = "incidencias_cerradas_" . $codigoBot;

        //comprobar si existen las columnas usuario, tecnico y acciones en la tabla de incidencias cerradas
        $sql = "SHOW COLUMNS FROM $tablaCerradas";
        $result = $conn->query($sql);

        $columnas = [];
        while($row = $result->fetch_assoc()){
            $columnas[] = $row['Field'];
        }

        if(!in_array('usuario', $columnas)){
            $sql = "ALTER TABLE $tablaCerradas ADD usuario LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci'";
            $conn->query($sql);
        }

        if(!in_array('tecnico', $columnas)){
            $sql = "ALTER TABLE $tablaCerradas ADD tecnico LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci'";
            $conn->query($sql);
        }

        if(!in_array('acciones', $columnas)){
            $sql = "ALTER TABLE $tablaCerradas ADD acciones LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci'";
            $conn->query($sql);
        }

        $inserted = Tools::create($conn, $tablaCerradas, $incidenciaCerrada, []);

        //eliminar la incidencia de la tabla de abiertas
        $deleted = Tools::delete($conn, $tabla, $incidencia['id'], []);

        //eliminar la incidencia de las tablas de los tecnicos por la columna codigo
        foreach($tablasAbiertas as $tablaAbierta){
            $sql = "DELETE FROM $tablaAbierta WHERE codigo = '$codigoIncidencia'";
            $conn->query($sql);
        }

        if($inserted && $deleted){
            return [
                'status' => 'success',
                'message' => 'Incidencia cerrada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al cerrar la incidencia',
                'error' => $inserted
            ];
        }
    }
}
