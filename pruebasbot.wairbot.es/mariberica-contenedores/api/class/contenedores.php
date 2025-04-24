<?php

require 'vendor/autoload.php';

    /*
    TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, naviera, identificador_naviera, fecha_embarque(fecha), fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp
    */

class Contenedores{
    
    ###################################################################################################    
    ///////////////////////////////////////////// BASICAS /////////////////////////////////////////////
    ###################################################################################################

    public static $tabla = 'contenedores';

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $data = $request['data'];

        $check = ['identificador_contenedor'];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Contenedor creado con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el Contenedor',
                'error' => $created
            ];
        }
    }

    public static function editar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];
        $data = $request['data'];

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Contenedor actualizado con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el Contenedor',
                'error' => $updated
            ];
        }
    }

    public static function eliminar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];

        $check = [];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Contenedor eliminado con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el Contenedor',
                'error' => $deleted
            ];
        }
    }

    public static function listar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

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
            'message' => 'Contenedores listados con éxito',
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
        $search = null;
        $nPagina = 1;

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

    public static function plantillaExcel(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;

        $sql = "SHOW COLUMNS FROM $tabla";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $columnas = $stmt->get_result();
        $columnas = $columnas->fetch_all(MYSQLI_ASSOC);

        $columnas = array_column($columnas, 'Field');

        //exportar a excel
        $rows = [];
        $rows[] = $columnas;

        $xlsx = Shuchkin\SimpleXLSXGen::fromArray($rows);
        $fileName = 'plantillaExcel.xlsx';
        $xlsx->saveAs($fileName);

        //guardar en la carpeta public
        $path = 'public/' . $fileName;
        rename($fileName, $path);

        return [
            'status' => 'success',
            'message' => 'Plantilla Excel generada correctamente',
            'data' => [
                'path' => $path
            ]
        ];
    }

    public static function insertarDatosDesdeExcel($request){
        Utilidades::insertarDatosDesdeExcel($request);

        return [
            'status' => 'success',
            'message' => 'Datos insertados correctamente'
        ];
    }

    public static function obtenerPorId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si el Contenedor no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El Contenedor no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Contenedor obtenido con éxito',
            'data' => $result,
        ];
    }

    ###################################################################################################
    ///////////////////////////////////////////// PROPIAS /////////////////////////////////////////////
    ###################################################################################################

    public static function listarIdentificadorContenedorConNavierasMasId(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;

        $sql = "SELECT id, CONCAT(identificador_contenedor, ' - ', naviera) AS identificador_contenedor FROM $tabla";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $lista = $stmt->get_result();

        $lista = $lista->fetch_all(MYSQLI_ASSOC);

        //poner Contenedores en formato id => identificador_contenedor
        $lista = array_column($lista, 'identificador_contenedor', 'id');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $lista
        ];
    }

    public static function listarPorCompra($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];
        $idCompra = $request['id_compra'];

        $filtro = ['id_compra' => $idCompra];

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
            'message' => 'Contenedores listados con éxito',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'rows_per_page' => $_SESSION['rows_per_page'],
                'grid_or_table' => $configuracion['grid_or_table'],
                'data' => $result['data']
            ]
        ];
    }

    public static function listarPorCompraExcel($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $search = null;
        $nPagina = 1;
        $idCompra = $request['idCompra'];

        $filtro = ['id_compra' => $idCompra];

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

}