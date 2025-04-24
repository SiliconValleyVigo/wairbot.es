<?php
/* 
CLIENTES: id, nombre, telefono, cartera, bote, timestamp
APUESTAS: id, id_cliente, juego, imagen, fecha, timestamp
PREMIOS: id, id_cliente, id_apuesta, juego, importe, timestamp
*/

require 'vendor/autoload.php';

class Clientes{
    public static $tabla = 'clientes';
    public static $permisos = ['ADMINISTRADOR'];

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para crear un cliente'];
        }

        //si data contiene telefono, eliminar cualquier caracter que no sea número y si comienza por 6 o 7, añadir 34
        if(array_key_exists('telefono', $data)){
            $telefono = $data['telefono'];
            $telefono = preg_replace('/[^0-9]/', '', $telefono);
            if(strlen($telefono) == 9 && (substr($telefono, 0, 1) == '6' || substr($telefono, 0, 1) == '7')){
                $telefono = '34' . $telefono;
            }
            $data['telefono'] = $telefono;
        }

        $check = [];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Cliente creado con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el cliente',
                'error' => $created
            ];
        }
    }

    public static function editar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para editar un cliente'];
        }

        if(array_key_exists('telefono', $data)){
            $telefono = $data['telefono'];
            $telefono = preg_replace('/[^0-9]/', '', $telefono);
            if(strlen($telefono) == 9 && (substr($telefono, 0, 1) == '6' || substr($telefono, 0, 1) == '7')){
                $telefono = '34' . $telefono;
            }
            $data['telefono'] = $telefono;
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Cliente actualizado con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el cliente',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar eliminar un cliente'];
        }

        $check = [
            'apuestas' => 'id_cliente',
        ];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Cliente eliminado con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el cliente',
                'error' => $deleted
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar los clientes'];
        }
        
        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderBy = "";

        $tablaConfiguracion = $tabla;
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => 'Clientes listados con éxito',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar los clientes en excel'];
        }
        
        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $orderBy = "";

        $tablaConfiguracion = $tabla;
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = 100000;

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [];

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

    public static function obtenerPorId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para obtener un cliente'];
        }

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si el coordinador no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El cliente no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Cliente obtenido con éxito',
            'data' => $result,
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

        $sql = "SELECT id, nombre FROM $tabla";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $lista = $stmt->get_result();

        $lista = $lista->fetch_all(MYSQLI_ASSOC);

        //poner coordinadores en formato id => nombre
        $lista = array_column($lista, 'nombre', 'id');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $lista
        ];
    }
}
