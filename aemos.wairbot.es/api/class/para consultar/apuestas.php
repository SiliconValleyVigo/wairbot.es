<?php
/* 
CLIENTES: id, nombre, telefono, cartera, bote, timestamp
APUESTAS: id, id_cliente, juego, imagen, fecha, importe, json_date timestamp
PREMIOS: id, id_cliente, id_apuesta, juego, importe, timestamp
*/

require 'vendor/autoload.php';

class Apuestas{
    public static $tabla = 'apuestas';
    public static $permisos = ['ADMINISTRADOR'];

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];
        $data = $request['data'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para crear una apuesta'];
        }

        $check = [];

        //obtener la cartera del cliente
        $id_cliente = $data['id_cliente'];
        $sql = "SELECT cartera, telefono FROM clientes WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
            
        if ($cliente) {
            $cartera = $cliente['cartera'];
            $telefono = $cliente['telefono'];
        } else {
            // Manejar el caso cuando no se encuentra el cliente
            $cartera = null;
            $telefono = null;
        }

        //convertir los valores en float con dos decimales y restar el importe de la apuesta a la cartera
        $data['importe'] = number_format((float)$data['importe'], 2, '.', '');
        $cartera = number_format((float)$cartera, 2, '.', '');
        $cartera = $cartera - $data['importe'];

        //si la cartera es menor a 0, no se puede realizar la apuesta
        if($cartera < 0){
            return [
                'status' => 'error',
                'message' => 'La cartera del cliente no puede ser menor a 0',
            ];
        }else{
            //actualizar la cartera del cliente
            $sql = "UPDATE clientes SET cartera = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('di', $cartera, $id_cliente);
            $stmt->execute();
        }

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $codigoAdministradorBot = 'loterias37vigo_CHAT_';
                $passwordAdministradorBot = '15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225';
                $userPhone = $telefono;
                $juego = $data['juego'];
                $importe = $data['importe'];
                $fechas = json_decode($data['json_date'], true);
                $message = "Hemos realizado tu apuesta \n *Juego*: $juego \n*Importe*: $importe €\n Fechas: ";
                foreach($fechas as $fecha){
                    //la fecha está en formato 2021-09-01T00:00:00.000Z, se debe convertir a 01-09-2021
                    $fecha = date('d-m-Y', strtotime($fecha));
                    $message .= $fecha . '\n';
                }
                $message .= "\n¡Buena suerte!\n\nAccede a tu informacion en el enlace y pulsa Enviar: https://wa.me/34886080849?text=loterias37vigo";
                $file = $data['imagen'];

                //enviar mensaje al chat
                Tools::sendMessageWhatsapp($userPhone, $message, $file, $codigoAdministradorBot, $passwordAdministradorBot);
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
            return ['status' => 'error', 'message' => 'No tienes permisos para editar una apuesta'];
        }

        //obtener la cartelera del cliente
        $id_cliente = $data['id_cliente'];
        $sql = "SELECT cartera FROM clientes WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartera = $result->fetch_assoc()['cartera'];

        //obtener el importe anterior de la apuesta
        $sql = "SELECT importe FROM $tabla WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $importeAnterior = $result->fetch_assoc()['importe'];

        $importeActual = $data['importe'];

        //convertir los valores en float con dos decimales y restar el importe actual al importe anterior, si el resultado es negativo restar al importe de la cartera y si es positivo sumar a la cartera
        $importeAnterior = number_format((float)$importeAnterior, 2, '.', '');
        $cartera = number_format((float)$cartera, 2, '.', '');
        $importeActual = number_format((float)$importeActual, 2, '.', '');
        $diferencia = $importeActual - $importeAnterior;

        if($diferencia < 0){
            $cartera = $cartera - abs($diferencia);
        }else{
            $cartera = $cartera + $diferencia;
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Apuesta actualizada con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la apuesta',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar eliminar una apuesta'];
        }

        $check = [
            'premios' => 'id_apuesta'
        ];

        //obtener el importe de la apuesta
        $sql = "SELECT importe, id_cliente FROM $tabla WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $importe = $data['importe'];
        $id_cliente = $data['id_cliente'];

        //obtener la cartera del cliente
        $sql = "SELECT cartera FROM clientes WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartera = $result->fetch_assoc()['cartera'];

        //convertir los valores en float con dos decimales y restar el importe de la apuesta a la cartera
        $importe = number_format((float)$importe, 2, '.', '');
        $cartera = number_format((float)$cartera, 2, '.', '');
        $cartera = $cartera + $importe;

        //actualizar la cartera del cliente
        $sql = "UPDATE clientes SET cartera = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $cartera, $id_cliente);
        $stmt->execute();

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Apuesta eliminada con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la apuesta',
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar apuestas'];
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
            ['clientes', 'nombre', 'id_cliente']
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
            return ['status' => 'error', 'message' => 'No tienes permisos para listar las apuestas en excel'];
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
            ['clientes', 'nombre', 'id_cliente']
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
        $id_cliente = $request['id_cliente'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para listar las apuestas de un cliente'];
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
            ['clientes', 'nombre', 'id_cliente']
        ];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

        return [
            'status' => 'success',
            'message' => 'Apuestas listadas con éxito',
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
        $id_cliente = $request['id_cliente'];
        $search = null;
        $nPagina = 1;

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para listar las apuestas de un cliente en excel'];
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
            ['clientes', 'nombre', 'id_cliente']
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
            return ['status' => 'error', 'message' => 'No tienes permisos para obtener una apuesta'];
        }

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si el coordinador no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'La apuesta no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Apuesta obtenida con éxito',
            'data' => $result,
        ];
    }

    public static function listarFechaJuegoMasId($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $permisos = self::$permisos;
        $rol = $_SESSION['user_rol'];        

        if(!in_array($rol, $permisos)){
            return ['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción'];
        }

        $sql = "SELECT a.id, a.id_cliente, a.fecha, a.juego, c.nombre FROM $tabla a JOIN clientes c ON a.id_cliente = c.id";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $lista = $stmt->get_result();

        $lista = $lista->fetch_all(MYSQLI_ASSOC);

        //poner en formato id => cliente - juego - fecha
        $listaFinal = [];
        foreach($lista as $item){
            $listaFinal[$item['id']] = $item['nombre'] . ' - ' . $item['juego'] . ' - ' . $item['fecha'];
        }

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $listaFinal
        ];
    }
}
