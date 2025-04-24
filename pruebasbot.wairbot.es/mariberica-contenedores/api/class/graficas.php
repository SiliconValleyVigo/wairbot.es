<?php

/*
    /*
    TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, naviera, identificador_naviera, fecha_embarque(fecha), fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp

GRAFICAS

Se envía en request para todas las gráficas:
fecha_inicial
fecha_final

RANGO DE MESES
Compras por mes
Valor de las compras por mes
Contenedores por mes
Promedio de productos por contenedor
Número de productos comprados por mes


GLOBALES
Proveedores por número de compras
Presentaciones por número de compras
Productos por número de compras
Número de contenedores por estado de embarque
Número de productos por estado de embarque de contenedor
Número de contenedores por rango de valor
Número de Contenedores por naviera

Ejemplo de respuesta para globales:
{
    "status": "success",
    "message": "Gráfica creada con éxito",
    "data": {
        "labels": ["Calamares", "Pulpos", "Camarones", "Pescados", "Mariscos"],
        "data": [10, 20, 30, 40, 50],
    }
}
*/

class Graficas {
    //obtener todas las gráficas
    public static function obtenerGraficas($request){
        $comprasPorMes = self::comprasPorMes($request);
        $valorComprasPorMes = self::valorComprasPorMes($request);
        $contenedoresPorMes = self::contenedoresPorMes($request);
        $promedioProductosPorContenedor = self::promedioProductosPorContenedor($request);
        $productosCompradosPorMes = self::productosCompradosPorMes($request);
        $proveedoresPorCompras = self::proveedoresPorCompras($request);
        $presentacionesPorCompras = self::presentacionesPorCompras($request);
        $productosPorCompras = self::productosPorCompras($request);
        $contenedoresPorEstadoEmbarque = self::contenedoresPorEstadoEmbarque($request);
        $productosPorEstadoEmbarqueContenedor = self::productosPorEstadoEmbarqueContenedor($request);
        $contenedoresPorNaviera = self::contenedoresPorNaviera($request);
        
        return [
            $comprasPorMes,
            $valorComprasPorMes,
            $contenedoresPorMes,
            $promedioProductosPorContenedor,
            $productosCompradosPorMes,
            $proveedoresPorCompras,
            $presentacionesPorCompras,
            $productosPorCompras,
            $contenedoresPorEstadoEmbarque,
            $productosPorEstadoEmbarqueContenedor,
            $contenedoresPorNaviera
        ];
    }
        

    /////////////////////////////////////////// POR RANGO DE MESES ///////////////////////////////////////////
    /*
    {
        "status": "success",
        "message": "Gráfica creada con éxito",
        "data": {
            "labels": ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio"],
            "periodoSolicitado": {
                "data": [10, 20, 30, 40, 50, 60],
                "name": "Compras por mes de 01/2021 a 06/2021"
            },
            "periodoAnterior": {
                "data": [10, 20, 30, 40, 50, 60],
                "name": "Compras por mes de 01/2020 a 06/2020"
            }
        }
    }
    */
    static private function comprasPorMes($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'compras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];
    
        // Calcular la diferencia en meses entre las fechas inicial y final
        $fecha_inicio = new DateTime($fecha_inicial);
        $fecha_fin = new DateTime($fecha_final);
        $intervalo = $fecha_inicio->diff($fecha_fin);
        $meses_diferencia = ($intervalo->y * 12) + $intervalo->m;
    
        // Calcular el periodo anterior
        $fecha_inicio_anterior = (clone $fecha_inicio)->modify("-$meses_diferencia months");
        $fecha_fin_anterior = (clone $fecha_inicio)->modify('-1 day');
    
        // Consultar datos del periodo solicitado
        $query = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total FROM $tabla WHERE fecha BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY mes";
        $result = $conn->query($query);

        $compras = [];
        while ($row = $result->fetch_assoc()) {
            $compras[] = $row;
        }
    
        // Consultar datos del periodo anterior
        $query_anterior = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as total FROM $tabla WHERE fecha BETWEEN '{$fecha_inicio_anterior->format('Y-m-d')}' AND '{$fecha_fin_anterior->format('Y-m-d')}' GROUP BY mes";
        
        $result_anterior = $conn->query($query_anterior);
        $compras_anterior = [];
        while ($row = $result_anterior->fetch_assoc()) {
            $compras_anterior[] = $row;
        }
    
        $labels = [];
        $data = [];
        $data_anterior = [];
    
        $fecha_temp = clone $fecha_inicio;
        while ($fecha_temp <= $fecha_fin) {
            $mes = self::traducirMes($fecha_temp->format('F-y'));
            $labels[] = $mes;
            $data[$mes] = 0;
            $fecha_temp->modify('+1 month');
        }
    
        $fecha_temp_anterior = clone $fecha_inicio_anterior;
        while (count($labels) > count($data_anterior)) {
            $mes = 'Comparativa con ' . self::traducirMes($fecha_temp_anterior->format('F-y'));
            $data_anterior[$mes] = 0;
            $fecha_temp_anterior->modify('+1 month');
        }
    
        foreach($compras as $compra){
            $mes = self::traducirMes(DateTime::createFromFormat('Y-m', $compra['mes'])->format('F-y'));
            $data[$mes] = $compra['total'];
        }
    
        foreach($compras_anterior as $compra){
            $mes = 'Comparativa con ' . self::traducirMes(DateTime::createFromFormat('Y-m', $compra['mes'])->format('F-y'));
            $data_anterior[$mes] = $compra['total'];
        }
    
        return [
            'tipo' => 'line',
            'titulo' => 'Compras por mes',
            'periodoSolicitado' => [
                'labels' => $labels,
                'data' => array_values($data),
                'name' => "Compras por mes de $fecha_inicial a $fecha_final"
            ],
            'periodoAnterior' => [
                'labels' => array_keys($data_anterior),
                'data' => array_values($data_anterior),
                'name' => "Compras por mes de {$fecha_inicio_anterior->format('Y-m-d')} a {$fecha_fin_anterior->format('Y-m-d')}"
            ]
        ];
    }

    //Valor de las compras por mes
    static private function valorComprasPorMes($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'compras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];
    
        // Calcular la diferencia en meses entre las fechas inicial y final
        $fecha_inicio = new DateTime($fecha_inicial);
        $fecha_fin = new DateTime($fecha_final);
        $intervalo = $fecha_inicio->diff($fecha_fin);
        $meses_diferencia = ($intervalo->y * 12) + $intervalo->m;
    
        // Calcular el periodo anterior
        $fecha_inicio_anterior = (clone $fecha_inicio)->modify("-$meses_diferencia months");
        $fecha_fin_anterior = (clone $fecha_inicio)->modify('-1 day');
    
        // Nueva consulta para obtener el valor medio de las compras por mes
        $query = "SELECT 
                    DATE_FORMAT(c.fecha, '%Y-%m') AS mes, 
                    AVG(p.precio) AS valor_medio_compras
                  FROM 
                    compras c
                  JOIN 
                    productos_de_contenedor pc ON c.id = pc.id_contenedor
                  JOIN 
                    productos p ON pc.id_producto = p.id
                  WHERE 
                    c.fecha BETWEEN '$fecha_inicial' AND '$fecha_final'
                  GROUP BY 
                    mes";
        $result = $conn->query($query);
        $compras = [];
        while ($row = $result->fetch_assoc()) {
            $compras[] = $row;
        }
    
        // Consultar datos del periodo anterior con la nueva consulta
        $query_anterior = "SELECT 
                            DATE_FORMAT(c.fecha, '%Y-%m') AS mes, 
                            AVG(p.precio) AS valor_medio_compras
                          FROM 
                            compras c
                          JOIN 
                            productos_de_contenedor pc ON c.id = pc.id_contenedor
                          JOIN 
                            productos p ON pc.id_producto = p.id
                          WHERE 
                            c.fecha BETWEEN '{$fecha_inicio_anterior->format('Y-m-d')}' AND '{$fecha_fin_anterior->format('Y-m-d')}'
                          GROUP BY 
                            mes";
        $result_anterior = $conn->query($query_anterior);
        $compras_anterior = [];
        while ($row = $result_anterior->fetch_assoc()) {
            $compras_anterior[] = $row;
        }
        
        $labels = [];
        $data = [];
        $data_anterior = [];
    
        $fecha_temp = clone $fecha_inicio;
        while ($fecha_temp <= $fecha_fin) {
            $mes = self::traducirMes($fecha_temp->format('F-y'));
            $labels[] = $mes;
            $data[$mes] = 0;
            $fecha_temp->modify('+1 month');
        }
    
        $fecha_temp_anterior = clone $fecha_inicio_anterior;
        while (count($labels) > count($data_anterior)) {
            $mes = 'Comparativa con ' . self::traducirMes($fecha_temp_anterior->format('F-y'));
            $data_anterior[$mes] = 0;
            $fecha_temp_anterior->modify('+1 month');
        }
    
        foreach($compras as $compra){
            $mes = self::traducirMes(DateTime::createFromFormat('Y-m', $compra['mes'])->format('F-y'));
            $data[$mes] = $compra['valor_medio_compras'];
        }
    
        foreach($compras_anterior as $compra){
            $mes = 'Comparativa con ' . self::traducirMes(DateTime::createFromFormat('Y-m', $compra['mes'])->format('F-y'));
            $data_anterior[$mes] = $compra['valor_medio_compras'];
        }
    
        return [
            'tipo' => 'line',
            'titulo' => 'Valor medio de las compras por mes',
            'periodoSolicitado' => [
                'labels' => $labels,
                'data' => array_values($data),
                'name' => "Valor medio de las compras por mes de $fecha_inicial a $fecha_final"
            ],
            'periodoAnterior' => [
                'labels' => array_keys($data_anterior),
                'data' => array_values($data_anterior),
                'name' => "Valor medio de las compras por mes de {$fecha_inicio_anterior->format('Y-m-d')} a {$fecha_fin_anterior->format('Y-m-d')}"
            ]
        ];
    }

    //Contenedores por mes
    static private function contenedoresPorMes($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'contenedores';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];
    
        // Calcular la diferencia en meses entre las fechas inicial y final
        $fecha_inicio = new DateTime($fecha_inicial);
        $fecha_fin = new DateTime($fecha_final);
        $intervalo = $fecha_inicio->diff($fecha_fin);
        $meses_diferencia = ($intervalo->y * 12) + $intervalo->m;
    
        // Calcular el periodo anterior
        $fecha_inicio_anterior = (clone $fecha_inicio)->modify("-$meses_diferencia months");
        $fecha_fin_anterior = (clone $fecha_inicio)->modify('-1 day');
    
        // Obtener todos los contenedores de cada compra en el periodo solicitado y contarlos
        $query = "SELECT 
                    MONTH(c.fecha) AS mes, 
                    COUNT(co.id) AS numero_contenedores
                  FROM 
                    compras c
                  JOIN 
                    contenedores co ON c.id = co.id_compra
                  WHERE 
                    c.fecha BETWEEN '$fecha_inicial' AND '$fecha_final'
                  GROUP BY 
                    MONTH(c.fecha)
                  ORDER BY 
                    mes";
        $result = $conn->query($query);
        $contenedores = [];
        while ($row = $result->fetch_assoc()) {
            $contenedores[] = $row;
        }
        
        // Consultar datos del periodo anterior
        $query_anterior = "SELECT 
                            MONTH(c.fecha) AS mes, 
                            COUNT(co.id) AS numero_contenedores
                          FROM 
                            compras c
                          JOIN 
                            contenedores co ON c.id = co.id_compra
                          WHERE 
                            c.fecha BETWEEN '{$fecha_inicio_anterior->format('Y-m-d')}' AND '{$fecha_fin_anterior->format('Y-m-d')}'
                          GROUP BY 
                            MONTH(c.fecha)
                          ORDER BY 
                            mes";
        $result_anterior = $conn->query($query_anterior);
        $contenedores_anterior = [];
        while ($row = $result_anterior->fetch_assoc()) {
            $contenedores_anterior[] = $row;
        }
    
        $labels = [];
        $data = [];
        $data_anterior = [];
    
        $fecha_temp = clone $fecha_inicio;
        while ($fecha_temp <= $fecha_fin) {
            $mes = self::traducirMes($fecha_temp->format('F-y'));
            $labels[] = $mes;
            $data[$mes] = 0;
            $fecha_temp->modify('+1 month');
        }
    
        $fecha_temp_anterior = clone $fecha_inicio_anterior;
        while (count($labels) > count($data_anterior)) {
            $mes = 'Comparativa con ' . self::traducirMes($fecha_temp_anterior->format('F-y'));
            $data_anterior[$mes] = 0;
            $fecha_temp_anterior->modify('+1 month');
        }
    
        foreach($contenedores as $contenedor){
            $mes = self::traducirMes(DateTime::createFromFormat('m', $contenedor['mes'])->format('F-y'));
            $data[$mes] = $contenedor['numero_contenedores'];
        }
    
        foreach($contenedores_anterior as $contenedor){
            $mes = 'Comparativa con ' . self::traducirMes(DateTime::createFromFormat('m', $contenedor['mes'])->format('F-y'));
            $data_anterior[$mes] = $contenedor['numero_contenedores'];
        }
    
        return [
            'tipo' => 'line',
            'titulo' => 'Contenedores por mes',
            'periodoSolicitado' => [
                'labels' => $labels,
                'data' => array_values($data),
                'name' => "Contenedores por mes de $fecha_inicial a $fecha_final"
            ],
            'periodoAnterior' => [
                'labels' => array_keys($data_anterior),
                'data' => array_values($data_anterior),
                'name' => "Contenedores por mes de {$fecha_inicio_anterior->format('Y-m-d')} a {$fecha_fin_anterior->format('Y-m-d')}"
            ]
        ];
    }
    
    //Promedio de productos por contenedor
    static private function promedioProductosPorContenedor($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'productos_de_contenedor';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];
    
        // Calcular la diferencia en meses entre las fechas inicial y final
        $fecha_inicio = new DateTime($fecha_inicial);
        $fecha_fin = new DateTime($fecha_final);
        $intervalo = $fecha_inicio->diff($fecha_fin);
        $meses_diferencia = ($intervalo->y * 12) + $intervalo->m;
    
        // Calcular el periodo anterior
        $fecha_inicio_anterior = (clone $fecha_inicio)->modify("-$meses_diferencia months");
        $fecha_fin_anterior = (clone $fecha_inicio)->modify('-1 day');
    
        //obtener una lista del numero de productos de cada contenedor y calcular la media de productos por contenedor en el periodo solicitado
        $query = "SELECT DATE_FORMAT(fecha_embarque, '%Y-%m') as mes, AVG(cantidad) as total FROM contenedores co JOIN productos_de_contenedor pc ON co.id = pc.id_contenedor WHERE fecha_embarque BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY mes";
        $result = $conn->query($query);
        $contenedores = [];
        while ($row = $result->fetch_assoc()) {
            $contenedores[] = $row;
        }

        // Consultar datos del periodo anterior
        $query_anterior = "SELECT DATE_FORMAT(fecha_embarque, '%Y-%m') as mes, AVG(cantidad) as total FROM contenedores co JOIN productos_de_contenedor pc ON co.id = pc.id_contenedor WHERE fecha_embarque BETWEEN '{$fecha_inicio_anterior->format('Y-m-d')}' AND '{$fecha_fin_anterior->format('Y-m-d')}' GROUP BY mes";
        $result_anterior = $conn->query($query_anterior);
        $contenedores_anterior = [];
        while ($row = $result_anterior->fetch_assoc()) {
            $contenedores_anterior[] = $row;
        }        

        $labels = [];
        $data = [];
        $data_anterior = [];

        $fecha_temp = clone $fecha_inicio;
        while ($fecha_temp <= $fecha_fin) {
            $mes = self::traducirMes($fecha_temp->format('F-y'));
            $labels[] = $mes;
            $data[$mes] = 0;
            $fecha_temp->modify('+1 month');
        }

        $fecha_temp_anterior = clone $fecha_inicio_anterior;
        while (count($labels) > count($data_anterior)) {
            $mes = 'Comparativa con ' . self::traducirMes($fecha_temp_anterior->format('F-y'));
            $data_anterior[$mes] = 0;
            $fecha_temp_anterior->modify('+1 month');
        }

        foreach($contenedores as $contenedor){
            $mes = self::traducirMes(DateTime::createFromFormat('Y-m', $contenedor['mes'])->format('F-y'));
            $data[$mes] = $contenedor['total'];
        }

        foreach($contenedores_anterior as $contenedor){
            $mes = 'Comparativa con ' . self::traducirMes(DateTime::createFromFormat('Y-m', $contenedor['mes'])->format('F-y'));
            $data_anterior[$mes] = $contenedor['total'];
        }

        return [
            'tipo' => 'line',
            'titulo' => 'Promedio de productos por contenedor',
            'periodoSolicitado' => [
                'labels' => $labels,
                'data' => array_values($data),
                'name' => "Promedio de productos por contenedor de $fecha_inicial a $fecha_final"
            ],
            'periodoAnterior' => [
                'labels' => array_keys($data_anterior),
                'data' => array_values($data_anterior),
                'name' => "Promedio de productos por contenedor de {$fecha_inicio_anterior->format('Y-m-d')} a {$fecha_fin_anterior->format('Y-m-d')}"
            ]
        ];
    }

    //Número de productos comprados por mes
    static private function productosCompradosPorMes($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'productos_de_contenedor';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];
    
        // Calcular la diferencia en meses entre las fechas inicial y final
        $fecha_inicio = new DateTime($fecha_inicial);
        $fecha_fin = new DateTime($fecha_final);
        $intervalo = $fecha_inicio->diff($fecha_fin);
        $meses_diferencia = ($intervalo->y * 12) + $intervalo->m;
    
        // Calcular el periodo anterior
        $fecha_inicio_anterior = (clone $fecha_inicio)->modify("-$meses_diferencia months");
        $fecha_fin_anterior = (clone $fecha_inicio)->modify('-1 day');
    
        //obtener una lista de productos de cada contenedor y contarlos en el periodo solicitado
        $query = "SELECT DATE_FORMAT(fecha_embarque, '%Y-%m') as mes, SUM(cantidad) as total FROM contenedores co JOIN productos_de_contenedor pc ON co.id = pc.id_contenedor WHERE fecha_embarque BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY mes";
        $result = $conn->query($query);
        $contenedores = [];
        while ($row = $result->fetch_assoc()) {
            $contenedores[] = $row;
        }

        // Consultar datos del periodo anterior
        $query_anterior = "SELECT DATE_FORMAT(fecha_embarque, '%Y-%m') as mes, SUM(cantidad) as total FROM contenedores co JOIN productos_de_contenedor pc ON co.id = pc.id_contenedor WHERE fecha_embarque BETWEEN '{$fecha_inicio_anterior->format('Y-m-d')}' AND '{$fecha_fin_anterior->format('Y-m-d')}' GROUP BY mes";
        $result_anterior = $conn->query($query_anterior);
        $contenedores_anterior = [];
        while ($row = $result_anterior->fetch_assoc()) {
            $contenedores_anterior[] = $row;
        }

        $labels = [];
        $data = [];
        $data_anterior = [];

        $fecha_temp = clone $fecha_inicio;
        while ($fecha_temp <= $fecha_fin) {
            $mes = self::traducirMes($fecha_temp->format('F-y'));
            $labels[] = $mes;
            $data[$mes] = 0;
            $fecha_temp->modify('+1 month');
        }

        $fecha_temp_anterior = clone $fecha_inicio_anterior;
        while (count($labels) > count($data_anterior)) {
            $mes = 'Comparativa con ' . self::traducirMes($fecha_temp_anterior->format('F-y'));
            $data_anterior[$mes] = 0;
            $fecha_temp_anterior->modify('+1 month');
        }

        foreach($contenedores as $contenedor){
            $mes = self::traducirMes(DateTime::createFromFormat('Y-m', $contenedor['mes'])->format('F-y'));
            $data[$mes] = $contenedor['total'];
        }

        foreach($contenedores_anterior as $contenedor){
            $mes = 'Comparativa con ' . self::traducirMes(DateTime::createFromFormat('Y-m', $contenedor['mes'])->format('F-y'));
            $data_anterior[$mes] = $contenedor['total'];
        }

        return [
            'tipo' => 'line',
            'titulo' => 'Número de productos comprados por mes',
            'periodoSolicitado' => [
                'labels' => $labels,
                'data' => array_values($data),
                'name' => "Número de productos comprados por mes de $fecha_inicial a $fecha_final"
            ],
            'periodoAnterior' => [
                'labels' => array_keys($data_anterior),
                'data' => array_values($data_anterior),
                'name' => "Número de productos comprados por mes de {$fecha_inicio_anterior->format('Y-m-d')} a {$fecha_fin_anterior->format('Y-m-d')}"
            ]
        ];
    }

    /*
        proveedores: id, nombre, referencia, notas(textarea), timestamp

        compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

        contenedores: id, id_compra(n_compra), identificador_contenedor, id_naviera, fecha_embarque(fecha), fecha_llegada_minima(fecha), fecha_llegada_maxima(fecha), observaciones, booking, estado_de_embarque, referencia, notas(textarea), timestamp
        
        productos_de_contenedor: id, id_contenedor, id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
        
        productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp
        
        navieras: id, nombre, accesos(textarea), referencia, notas(textarea), timestamp
        */


    /////////////////////////////////////////// GLOBALES ///////////////////////////////////////////
    //Proveedores por número de compras
    static private function proveedoresPorCompras($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'proveedores';
        $tabla_compras = 'compras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT p.nombre, COUNT(c.id_proveedor) as total FROM $tabla p JOIN $tabla_compras c ON p.id = c.id_proveedor WHERE c.fecha BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY p.nombre";
        $result = $conn->query($query);
        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }

        $labels = [];
        $data = [];

        foreach($proveedores as $proveedor){
            $labels[] = $proveedor['nombre'];
            $data[] = $proveedor['total'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Proveedores por número de compras',
            'labels' => $labels,
            'data' => $data
        ];
    }

    /*
        proveedores: id, nombre, referencia, notas(textarea), timestamp

        compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

        contenedores: id, id_compra(n_compra), identificador_contenedor, id_naviera, fecha_embarque(fecha), fecha_llegada_minima(fecha), fecha_llegada_maxima(fecha), observaciones, booking, estado_de_embarque, referencia, notas(textarea), timestamp
        
        productos_de_contenedor: id, id_contenedor, id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
        
        productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp
        
        navieras: id, nombre, accesos(textarea), referencia, notas(textarea), timestamp
        */
    //Presentaciones por número de compras
    static private function presentacionesPorCompras($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'productos';
        $tabla_productos_de_contenedor = 'productos_de_contenedor';
        $tabla_contenedores = 'contenedores';
        $tabla_compras = 'compras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT p.presentacion, COUNT(c.id) AS numero_compras FROM $tabla p JOIN $tabla_productos_de_contenedor pc ON p.id = pc.id_producto JOIN $tabla_contenedores co ON pc.id_contenedor = co.id JOIN $tabla_compras c ON co.id_compra = c.id WHERE c.fecha BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY p.presentacion";
        $result = $conn->query($query);
        $presentaciones = [];
        while ($row = $result->fetch_assoc()) {
            $presentaciones[] = $row;
        }
        
        $labels = [];
        $data = [];

        foreach($presentaciones as $presentacion){
            $labels[] = $presentacion['presentacion'];
            $data[] = $presentacion['numero_compras'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Presentaciones por número de compras',
            'labels' => $labels,
            'data' => $data
        ];
    }

    //SELECT p.presentacion, COUNT(pc.id_producto) as total FROM productos p JOIN productos_de_contenedor pc ON p.id = pc.id_producto JOIN contenedores co ON pc.id_contenedor = co.id JOIN compras c ON co.id_compra = c.n_compra WHERE c.fecha BETWEEN '2025-01-01' AND '2025-01-12' GROUP BY p.presentacion


    //Productos por número de compras
    static private function productosPorCompras($request){
        $conn = $_SESSION['db_connection'];
        $tabla_productos_de_contenedor = 'productos_de_contenedor';
        $tabla_contenedores = 'contenedores';
        $tabla_compras = 'compras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT pc.id_contenedor, AVG(pc.cantidad) AS media_productos 
                  FROM $tabla_productos_de_contenedor pc 
                  JOIN $tabla_contenedores c ON pc.id_contenedor = c.id 
                  JOIN $tabla_compras co ON c.id_compra = co.id 
                  WHERE co.fecha BETWEEN '$fecha_inicial' AND '$fecha_final' 
                  GROUP BY pc.id_contenedor";
        $result = $conn->query($query);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $labels = [];
        $data = [];

        foreach($productos as $producto){
            $labels[] = $producto['id_contenedor'];
            $data[] = $producto['media_productos'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Media de productos por contenedor',
            'labels' => $labels,
            'data' => $data
        ];
    }

    //Número de contenedores por estado de embarque
    static private function contenedoresPorEstadoEmbarque($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'contenedores';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT estado_de_embarque, COUNT(*) as total FROM $tabla WHERE fecha_embarque BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY estado_de_embarque";
        $result = $conn->query($query);
        $contenedores = [];
        while ($row = $result->fetch_assoc()) {
            $contenedores[] = $row;
        }

        $labels = [];
        $data = [];

        foreach($contenedores as $contenedor){
            $labels[] = $contenedor['estado_de_embarque'];
            $data[] = $contenedor['total'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Número de contenedores por estado de embarque',
            'labels' => $labels,
            'data' => $data
        ];
    }

    //Número de productos por estado de embarque de contenedor
    static private function productosPorEstadoEmbarqueContenedor($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'productos_de_contenedor';
        $tabla_contenedores = 'contenedores';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT estado_de_embarque, COUNT(pc.id) as total FROM $tabla pc JOIN $tabla_contenedores co ON pc.id_contenedor = co.id WHERE co.fecha_embarque BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY estado_de_embarque";
        $result = $conn->query($query);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $labels = [];
        $data = [];

        foreach($productos as $producto){
            $labels[] = $producto['estado_de_embarque'];
            $data[] = $producto['total'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Número de productos por estado de embarque de contenedor',
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    //Número de Contenedores por naviera
    static private function contenedoresPorNaviera($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'contenedores';
        $tabla_navieras = 'navieras';
        $fecha_inicial = $request['fecha_inicial'];
        $fecha_final = $request['fecha_final'];

        $query = "SELECT n.nombre, COUNT(co.id_naviera) as total FROM $tabla co JOIN $tabla_navieras n ON co.id_naviera = n.id WHERE co.fecha_embarque BETWEEN '$fecha_inicial' AND '$fecha_final' GROUP BY n.nombre";
        $result = $conn->query($query);
        $navieras = [];
        while ($row = $result->fetch_assoc()) {
            $navieras[] = $row;
        }

        $labels = [];
        $data = [];

        foreach($navieras as $naviera){
            $labels[] = $naviera['nombre'];
            $data[] = $naviera['total'];
        }

        return [
            'tipo' => 'bar',
            'titulo' => 'Número de Contenedores por naviera',
            'labels' => $labels,
            'data' => $data
        ];
    }

    /////////////////////////////////////////// UTILES ///////////////////////////////////////////
    static private function mes($mes){
        $meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        return $meses[$mes - 1];
    }

    static private function traducirMes($mes) {
        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];
    
        foreach ($meses as $ingles => $espanol) {
            $mes = str_replace($ingles, $espanol, $mes);
        }
    
        return $mes;
    }
}