<?php

require 'vendor/autoload.php';

class Beneficiarios{
    public static $tabla = 'beneficiarios';
    public static $permisos = ['ADMINISTRADOR', 'COORDINADOR', 'VOLUNTARIO'];
    /*
    Crear
    Listar
     ListarPorCoordinador
    ListarNombreMasId
     ListarNombreMasIdPorCoordinador
    ObtenerPorId
    Editar
    Eliminar
    */

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $check = ['email'];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Beneficiario creado correctamente',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'El email ya está en uso',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el Beneficiario',
                'error' => $created
            ];
        }
    }

    public static function listar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'estado' => 'ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderby = null;

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id'],
        ];

        //$tablas = ['acciones', 'acciones_periodicas', 'beneficiarios', 'beneficiarios_baja', 'coordinadores', 'voluntarios', 'voluntarios_baja'];

        $tablaConfiguracion = 'beneficiarios';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderby = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderby, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => 'Beneficiarios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => $configuracion['grid_or_table'],
                'data' => $result['data']
            ]
        ];
    }

    public static function listarExcel(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = null;
        $nPagina = 1;

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'estado' => 'ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderBy = "ORDER BY 
                    CASE 
                        WHEN acciones.estado = 'PENDIENTE' THEN 1 
                        WHEN acciones.estado = 'FINALIZADA' THEN 2 
                        ELSE 3 
                    END, 
                    fecha DESC
        ";

        $tablaConfiguracion = 'beneficiarios';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = 100000;

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id'],
        ];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);
        $result = $result['data'];

       //exportar a excel
        $rows = [];
        if (!empty($result)) {
            // Agregar encabezados
            $headers = array_keys($result[0]);
            $rows[] = $headers;

            // Agregar datos
            foreach ($result as $row) {
                $rows[] = array_values($row);
            }
        }

        $xlsx = Shuchkin\SimpleXLSXGen::fromArray($rows);
        $fileName = 'tablaExportada.xlsx';
        $xlsx->saveAs($fileName);

        //guardar en la carpeta public
        $path = 'public/' . $fileName;
        rename($fileName, $path);

        return [
            'status' => 'success',
            'message' => 'Excel generado correctamente',
            'data' => [
                'path' => $path
            ]
        ];
    }

    public static function listarNoActivos($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'estado' => '!ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [

        ];

        $orderby = null;

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id'],
        ];

        $tablaConfiguracion = 'beneficiarios_baja';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderby = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderby, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => 'Beneficiarios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => $configuracion['grid_or_table'],
                'data' => $result['data']
            ]
        ];  
    }

    public static function listarNoActivosExcel(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = null;
        $nPagina = 1;

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'estado' => '!ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [

        ];

        $orderBy = null;

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id'],
        ];

        $tablaConfiguracion = 'beneficiarios_baja';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = 100000;

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);
        $result = $result['data'];

       //exportar a excel
        $rows = [];
        if (!empty($result)) {
            // Agregar encabezados
            $headers = array_keys($result[0]);
            $rows[] = $headers;

            // Agregar datos
            foreach ($result as $row) {
                $rows[] = array_values($row);
            }
        }

        $xlsx = Shuchkin\SimpleXLSXGen::fromArray($rows);
        $fileName = 'tablaExportada.xlsx';
        $xlsx->saveAs($fileName);

        //guardar en la carpeta public
        $path = 'public/' . $fileName;
        rename($fileName, $path);

        return [
            'status' => 'success',
            'message' => 'Excel generado correctamente',
            'data' => [
                'path' => $path
            ]
        ];
    }

    public static function listarPorCoordinador($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $idCoordinador = array_key_exists('id_coordinador', $request) ? $request['id_coordinador'] : $_SESSION['user_id'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'id_coordinador' => $idCoordinador,
            'estado' => 'ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderby = null;

        $tablaConfiguracion = 'beneficiarios';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        /*
        if($configuracion['grid_or_table'] == 'table'){
            $orderby = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }*/

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderby);

        return [
            'status' => 'success',
            'message' => 'Beneficiarios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => 'grid',
                'data' => $result['data']
            ]
        ];
    }

    public static function listarNoActivosPorCoordinador($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $idCoordinador = array_key_exists('id_coordinador', $request) ? $request['id_coordinador'] : $_SESSION['user_id'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'id_coordinador' => $idCoordinador,
            'estado' => '!ACTIVO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderby = null;

        $tablaConfiguracion = 'beneficiarios_baja';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderby = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderby);

        return [
            'status' => 'success',
            'message' => 'Beneficiarios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => $configuracion['grid_or_table'],
                'data' => $result['data']
            ]
        ];
    }

    public static function listarNombreMasId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $sql = "SELECT id, nombre FROM $tabla WHERE estado = 'ACTIVO'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $coordinadores = $stmt->get_result();

        $coordinadores = $coordinadores->fetch_all(MYSQLI_ASSOC);

        //poner coordinadores en formato id => nombre
        $coordinadores = array_column($coordinadores, 'nombre', 'id');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $coordinadores
        ];
    }

    public static function listarNombreMasIdPorCoordinador($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $idCoordinador = $_SESSION['user_id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $sql = "SELECT id, nombre FROM $tabla WHERE id_coordinador = ? AND estado = 'ACTIVO'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCoordinador);
        $stmt->execute();
        $coordinadores = $stmt->get_result();

        $coordinadores = $coordinadores->fetch_all(MYSQLI_ASSOC);

        //poner coordinadores en formato id => nombre
        $coordinadores = array_column($coordinadores, 'nombre', 'id');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $coordinadores
        ];
    }

    public static function listarNombreMasIdPorVoluntario($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $idVoluntario = $_SESSION['user_id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        //obtener el id del coordinador del voluntario
        $sql = "SELECT id_coordinador FROM usuarios WHERE id = ? AND estado = 'ACTIVO'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idVoluntario);
        $stmt->execute();
        $result = $stmt->get_result();
        $result = $result->fetch_assoc();
        $idCoordinador = $result['id_coordinador'];

        $sql = "SELECT id, nombre FROM $tabla WHERE id_coordinador = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCoordinador);
        $stmt->execute();
        $coordinadores = $stmt->get_result();

        $coordinadores = $coordinadores->fetch_all(MYSQLI_ASSOC);

        //poner coordinadores en formato id => nombre
        $coordinadores = array_column($coordinadores, 'nombre', 'id');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $coordinadores
        ];
    }

    public static function obtenerPorId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si el coordinador no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El Beneficiario no existe'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Beneficiario obtenido correctamente',
            'data' => $result,
        ];
    }

    public static function editar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Beneficiario actualizado correctamente'
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el Beneficiario',
                'error' => $updated
            ];
        }        
    }

    public static function eliminar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $check = ['acciones' => 'id_beneficiario'];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Beneficiario eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el Beneficiario',
                'error' => $deleted
            ];
        }
    }
}