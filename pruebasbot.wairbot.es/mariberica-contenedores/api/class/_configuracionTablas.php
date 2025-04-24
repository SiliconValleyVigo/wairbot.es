<?php

class ConfiguracionTablas {
    static public function cambiarGridOrTabla($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $gridOrTable = $request['grid_or_table'];

        $sql = "UPDATE configuracion_tablas SET grid_or_table = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $gridOrTable, $tabla);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    static public function cambiarRowPerPage($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $rowPerPage = $request['row_per_page'];

        $sql = "UPDATE configuracion_tablas SET row_per_page = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $rowPerPage, $tabla);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    //cambiar order_by
    static public function cambiarOrderBy($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $orderBy = $request['order_by'];

        $sql = "UPDATE configuracion_tablas SET order_by = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $orderBy, $tabla);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    static public function cambiarVisualizacion($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $visualizacion = $request['visualizacion'];

        $sql = "UPDATE configuracion_tablas SET visualizacion = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $visualizacion, $tabla);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    //guardar columnas
    static public function guardarColumnas($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $columnas = $request['columnas'];

        $columnas = json_encode($columnas);

        $sql = "UPDATE configuracion_tablas SET columnas = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $columnas, $tabla);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    //guardar filtros
    static public function guardarFiltros($request){
        $conn = $_SESSION['db_connection'];
        
        // Asegurarse de que la conexión use UTF-8
        $conn->set_charset("utf8");
    
        $tabla = $request['tabla'];
        $filtros = $request['filtros'];
    
        $filtros = json_encode($filtros);
    
        $sql = "UPDATE configuracion_tablas SET filtros = ? WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $filtros, $tabla);
        $stmt->execute();
    
        return [
            'status' => 'success',
            'message' => 'Configuración actualizada correctamente'
        ];
    }

    static public function getConfiguracionTablas($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];

        if($tabla == 'productosdecontenedor'){
            $tabla = 'productos_de_contenedor';
        }

        $sql = "SELECT * FROM configuracion_tablas WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tabla);
        $stmt->execute();
        $result = $stmt->get_result();
        $configuracion = $result->fetch_assoc();

        return [
            'status' => 'success',
            'data' => $configuracion
        ];

    }

    // cambiar la variable de sesión $_SESSION['rows_per_page'] por el valor de row_per_page de la tabla configuracion_tablas
    static public function cambiarRowPerPagePorTabla($conn, $tabla){
        $sql = "SELECT row_per_page FROM configuracion_tablas WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tabla);
        $stmt->execute();
        $result = $stmt->get_result();
        $rowPerPage = $result->fetch_assoc()['row_per_page'];

        $_SESSION['rows_per_page'] = $rowPerPage;
    }

    //obtener la configuración de una tabla
    static public function obtenerConfiguracionTabla($conn, $tabla){
        $sql = "SELECT * FROM configuracion_tablas WHERE nombre_tabla = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tabla);
        $stmt->execute();
        $result = $stmt->get_result();
        $configuracion = $result->fetch_assoc();

        return $configuracion;
    }

}