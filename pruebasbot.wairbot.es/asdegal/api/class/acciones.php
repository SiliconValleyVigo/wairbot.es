<?php

require 'vendor/autoload.php';

class Acciones{
    public static $tabla = 'acciones';
    public static $permisos = ['ADMINISTRADOR', 'COORDINADOR', 'VOLUNTARIO'];

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
        
        $filtro = [];

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

        $tablaConfiguracion = 'acciones';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario'],
            ['beneficiarios', 'nombre', 'id_beneficiario']
        ];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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
        
        $filtro = [];

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

        $tablaConfiguracion = 'acciones';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = 100000;

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario'],
            ['beneficiarios', 'nombre', 'id_beneficiario']
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

    public static function listarPeriodicas($request){
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
            'estado' => 'PERIODICA'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderBy = null;

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario'],
            ['beneficiarios', 'nombre', 'id_beneficiario']
        ];

        $tablaConfiguracion = 'acciones_periodicas';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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

    public static function listarPeriodicasExcel(){
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
            'estado' => 'PERIODICA'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderBy = null;

        $camposRelacionados = [
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario'],
            ['beneficiarios', 'nombre', 'id_beneficiario']
        ];

        $tablaConfiguracion = 'acciones_periodicas';
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
            'id_coordinador' => $idCoordinador
        ];

        //ordernar primero por estado (primero deben ir las acciones con estado PENDIENTE y luego las acciones con estado FINALIZADA) y luego por fecha, las más recientes primero
        $orderBy = "ORDER BY 
                    CASE 
                        WHEN acciones.estado = 'PENDIENTE' THEN 1 
                        WHEN acciones.estado = 'FINALIZADA' THEN 2 
                        ELSE 3 
                    END, 
                    fecha DESC
        ";

        //sin datos relacionados
        $datosRelacionados = [];

        $camposRelacionados = [
            ['beneficiarios', 'nombre', 'id_beneficiario'],
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario']
        ];

        $tablaConfiguracion = 'acciones';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        /*
        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }*/

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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

    public static function listarPeriodicasPorCoordinador($request){
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
            'estado' => 'PERIODICA'
        ];

        //ordernar primero por estado (primero deben ir las acciones con estado PENDIENTE y luego las acciones con estado FINALIZADA) y luego por fecha, las más recientes primero
        $orderBy = null;

        //sin datos relacionados
        $datosRelacionados = [];

        $camposRelacionados = [
            ['beneficiarios', 'nombre', 'id_beneficiario'],
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario']
        ];

        $tablaConfiguracion = 'acciones_periodicas';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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

    public static function listarPorVoluntario($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        //si existe id_voluntario en la request, se obtiene de ahí, si no, se obtiene de la sesión
        $idVoluntario = array_key_exists('id_voluntario', $request) ? $request['id_voluntario'] : $_SESSION['user_id'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'id_voluntario' => $idVoluntario
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

        $camposRelacionados = [
            ['beneficiarios', 'nombre', 'id_beneficiario'],
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario']
        ];

        $tablaConfiguracion = 'acciones';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        /*
        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }*/

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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

    public static function listarPorBeneficiado($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        //si existe id_beneficiario en la request, se obtiene de ahí, si no, se obtiene de la sesión
        $idBeneficiario = array_key_exists('id_beneficiario', $request) ? $request['id_beneficiario'] : $_SESSION['user_id'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }
        
        $filtro = [
            'id_beneficiario' => $idBeneficiario
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

        $camposRelacionados = [
            ['beneficiarios', 'nombre', 'id_beneficiario'],
            ['usuarios', 'nombre', 'id_coordinador'],
            ['usuarios', 'nombre', 'id_voluntario']
        ];

        $tablaConfiguracion = 'acciones';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        /*
        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }*/

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

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