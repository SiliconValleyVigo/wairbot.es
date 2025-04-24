<?php
/* 
CLIENTES: id, nombre, telefono, cartera, bote, timestamp
APUESTAS: id, id_cliente, juego, imagen, fecha, importe, json_date timestamp
PREMIOS: id, id_cliente, id_apuesta, juego, importe, timestamp
*/

require 'vendor/autoload.php';

class Premios{
    public static $tabla = 'premios';
    public static $permisos = ['ADMINISTRADOR'];

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para crear un premio'];
        }

        $check = [];

        //obtener el bote del cliente y sumarle el importe del premio
        $id_cliente = $data['id_cliente'];
        $sql = "SELECT bote FROM clientes WHERE id = $id_cliente";
        $result = $conn->query($sql);
        $cliente = $result->fetch_assoc();
        //convertir a float con 2 decimales
        $bote = number_format($cliente['bote'], 2);
        $importe = number_format($data['importe'], 2);
        $bote = floatval($bote) + floatval($importe);

        //actualizar el bote del cliente
        $sql = "UPDATE clientes SET bote = $bote WHERE id = $id_cliente";
        $conn->query($sql);

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Apuesta creada con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la apuesta',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para editar un premio'];
        }

        //obtener el importe anterior del premio, si la diferencia es positiva se suma al bote del cliente, si es negativa se resta
        $sql = "SELECT importe, id_cliente FROM premios WHERE id = $id";
        $result = $conn->query($sql);
        $premio = $result->fetch_assoc();
        $id_cliente = $premio['id_cliente'];
        $importeAnterior = $premio['importe'];
        $importeNuevo = $data['importe'];
        $diferencia = $importeNuevo - $importeAnterior;

        //obtener el bote del cliente
        $sql = "SELECT bote FROM clientes WHERE id = $id_cliente";
        $result = $conn->query($sql);
        $cliente = $result->fetch_assoc();
        //convertir a float con 2 decimales
        $bote = number_format($cliente['bote'], 2);
        $bote = floatval($bote) + floatval($diferencia);

        //actualizar el bote del cliente
        $sql = "UPDATE clientes SET bote = $bote WHERE id = $id_cliente";
        $conn->query($sql);

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Premio actualizado con éxito'
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el premio',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar eliminar un premio'];
        }

        $check = [];

        //obtener el id_cliente y el importe del premio
        $sql = "SELECT id_cliente, importe FROM premios WHERE id = $id";
        $result = $conn->query($sql);
        $premio = $result->fetch_assoc();
        $id_cliente = $premio['id_cliente'];

        //obtener el bote del cliente y restarle el importe del premio
        $sql = "SELECT bote FROM clientes WHERE id = $id_cliente";
        $result = $conn->query($sql);
        $cliente = $result->fetch_assoc();
        //convertir a float con 2 decimales
        $bote = number_format($cliente['bote'], 2);
        $importe = number_format($premio['importe'], 2);
        $bote = floatval($bote) - floatval($importe);

        //actualizar el bote del cliente
        $sql = "UPDATE clientes SET bote = $bote WHERE id = $id_cliente";
        $conn->query($sql);

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Premio eliminado con éxito'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el premio',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar los premios'];
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

        $camposRelacionados = [
            ['clientes', 'nombre', 'id_cliente'],
            ['apuestas', 'fecha', 'id_apuesta']
        ];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => ' Apuestas listadas con éxito',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar las premios en excel'];
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

        $camposRelacionados = [
            ['clientes', 'nombre', 'id_cliente'],
            ['apuestas', 'juego', 'id_apuesta'],
            ['apuestas', 'fecha', 'id_apuesta']
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

    public static function listarPorCliente($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];
        $id_cliente = $request['id_cliente'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para listar los premios'];
        }

        $filtro = [
            'id_cliente' => $id_cliente
        ];

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

        $camposRelacionados = [
            ['clientes', 'nombre', 'id_cliente'],
            ['apuestas', 'juego', 'id_apuesta'],
            ['apuestas', 'fecha', 'id_apuesta']
        ];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => ' Apuestas listadas con éxito',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => $configuracion['grid_or_table'],
                'data' => $result['data']
            ]
        ];
    }

    public static function listarPorClienteExcel($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $search = null;
        $nPagina = 1;
        $id_cliente = $request['id_cliente'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para listar las premios en excel'];
        }
        
        $filtro = [
            'id_cliente' => $id_cliente
        ];

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

        $camposRelacionados = [
            ['clientes', 'nombre', 'id_cliente'],
            ['apuestas', 'juego', 'id_apuesta'],
            ['apuestas', 'fecha', 'id_apuesta']
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

    public static function obtenerPorId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id = $request['id'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para obtener un premio'];
        }

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si el coordinador no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El premio no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Premio obtenido con éxito',
            'data' => $result,
        ];
    }

    public static function obtenerPorApuestaId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $id_apuesta = $request['id_apuesta'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para obtener un premio'];
        }

        $sql = "SELECT * FROM $tabla WHERE id_apuesta = $id_apuesta";
        $result = $conn->query($sql);

        //debe devolver un solo premio o ninguno pero si no hay ninguno no dar error
        if($result->num_rows > 0){
            $premio = $result->fetch_assoc();
            return [
                'status' => 'success',
                'message' => 'Premio obtenido con éxito',
                'data' => $premio,
            ];
        }else{
            return [
                'status' => 'success',
                'message' => 'No hay premio para esta apuesta',
                'data' => [],
            ];
        }
    }
}
