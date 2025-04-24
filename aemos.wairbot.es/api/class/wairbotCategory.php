<?php

require 'vendor/autoload.php';

/* 
alumnos: id, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehículo, mensajes_enviados, referencia, timestamp

alumnos_baja: id, id_original, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehículo, mensajes_enviados, referencia, timestamp

cursos: id, nombre (unico), horas, financiacion(NORMAL, SEPE), previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, fecha_inicio timestamp

cursos_baja: id, id_original, nombre, horas, financiacion, previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, fecha_inicio, timestamp

clases: id, id_curso, nombre, fecha, hora_inicio, hora_fin, lugar, observaciones, referencia, timestamp

inscripciones: id, id_alumno, id_curso, estado(PROPUESTO, SOLICITADO, ACEPTADO, CURSANDO, RECHAZADO, FINALIZADO), timestamp

cita_previa: id, id_alumno, disponibilidad, timestamp

mensajes_programados: id, id_curso, numero_mensajes, intervalo_dias, fecha_primer_mensaje, hora_envio, mensaje, observaciones_internas, referencia, timestamp

acciones_formativas: id, id_alumno, id_curso, id_clase, asistio(1,0), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

selects: id, nombre, tabla, menu, timestamp

data: id, admin_phone, name, category, public, user_phone, role, status, url_file, text, file_reference, date_reference, date_send_daily, date_send_monthly, date_send_date, timestamp

categories: id, admin_phone, name, status(true), child_categories, timestamp

roles: id, admin_phone, name, status(true), timestamp

*/

class WairbotCategory{
    
    public static $tabla = 'categories';

    public static function crear($request){
        $connWairbot = DB::initDbWairbot();

        $tabla = self::$tabla;
        $data = $request['data'];

        $data['admin_phone'] = 'AemosCursos';
        $data['status'] = 'true';

        //si child_categories no viene en el request, se pone {}
        if(!array_key_exists('child_categories', $data)){
            $data['child_categories'] = '{}';
        }

        //si child_categories es un array, se convierte a json
        if(is_array($data['child_categories'])){
            $data['child_categories'] = json_encode($data['child_categories']);
        }

        // si child_categories es una cadena con solo dos comillas dobles, se pone vacío
        if($data['child_categories'] === '""'){
            $data['child_categories'] = '';
        }

        $check = [];

        $created = Tools::create($connWairbot, $tabla, $data, $check);

        //crear un registro en data solo con el nombre de la categoría
        $sql = "INSERT INTO data (admin_phone, name, category, public, user_phone, role, status, url_file, text, file_reference, date_reference, date_send_daily, date_send_monthly, date_send_date) VALUES ('AemosCursos', ?, ?, 'true', '', '', 'true', '', '', '', '', '', '', '')";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('ss', $data['name'], $data['name']);
        $stmt->execute();

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Categoría creada con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la categoría',
                'error' => $created
            ];
        }

        $connWairbot->close();
    }

    public static function editar($request){
        $connWairbot = DB::initDbWairbot();

        $tabla = self::$tabla;
        $id = $request['id'];
        $data = $request['data'];

        $data['admin_phone'] = 'AemosCursos';
        $data['status'] = 'true';

        //si child_categories no viene en el request, se pone {}
        if(!array_key_exists('child_categories', $data)){
            $data['child_categories'] = '{}';
        }

        //si child_categories es un array, se convierte a json
        if(is_array($data['child_categories'])){
            $data['child_categories'] = json_encode($data['child_categories']);
        }

        $updated = Tools::update($connWairbot, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Categoría actualizada con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la categoría',
                'error' => $updated
            ];
        }

        $connWairbot->close();
    }

    public static function eliminar($request){
        $connWairbot = DB::initDbWairbot();

        $tabla = self::$tabla;
        $id = $request['id'];

        $check = [];

        $deleted = Tools::delete($connWairbot, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Categoría eliminada con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la categoría',
                'error' => $deleted
            ];
        }

        $connWairbot->close();
    }

    public static function listar($request){
        $connWairbot = DB::initDbWairbot();
        $conn = $_SESSION['db_connection'];

        $tabla = self::$tabla;
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        $datosRelacionados = [];

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $orderBy = "";

        $tablaConfiguracion = 'wairbotcategory';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = $configuracion['row_per_page'];

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [];

        $result = Tools::read($connWairbot, $nPagina, $tabla, $filtro, $datosRelacionados, $search, $orderBy, $camposRelacionados);

        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Categorías listadas con éxito',
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
        $connWairbot = DB::initDbWairbot();
        $conn = $_SESSION['db_connection'];

        $tabla = self::$tabla;
        $search = null;
        $nPagina = 1;

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $orderBy = "";

        $tablaConfiguracion = 'wairbotcategory';
        $configuracion = ConfiguracionTablas::obtenerConfiguracionTabla($conn, $tablaConfiguracion);
        $_SESSION['rows_per_page'] = 100000;

        if($configuracion['grid_or_table'] == 'table'){
            $orderBy = $configuracion['order_by'];
            $filtroConfiguracion = json_decode($configuracion['filtros'], true);
            $filtro = array_merge($filtroConfiguracion, $filtro);
        }

        $camposRelacionados = [];

        $result = Tools::read($connWairbot, $nPagina, $tabla, $filtro, $search, $orderBy, $camposRelacionados);
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

        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Excel generado correctamente',
            'data' => [
                'path' => $path
            ]
        ];
    }

    public static function plantillaExcel(){
        $connWairbot = DB::initDbWairbot();

        $tabla = self::$tabla;

        $sql = "SHOW COLUMNS FROM $tabla";
        $stmt = $connWairbot->prepare($sql);
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

        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Plantilla Excel generada correctamente',
            'data' => [
                'path' => $path
            ]
        ];
    }

    public static function insertarDatosDesdeExcel($request){
        $connWairbot = DB::initDbWairbot();

        Utilidades::insertarDatosDesdeExcel($request, $connWairbot);

        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Datos insertados correctamente'
        ];
    }

    public static function obtenerPorId($request){
        $connWairbot = DB::initDbWairbot();

        $tabla = self::$tabla;
        $id = $request['id'];
        $admin_phone = 'AemosCursos';

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $datosRelacionados = [];

        $result = Tools::getById($connWairbot, $tabla, $id, $filtro, $datosRelacionados);

        $connWairbot->close();

        //si la categoría no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'La categoría no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Categoría obtenida con éxito',
            'data' => $result,
        ];
    }

    //WairbotCategory::listarNombreMasId
    public static function listarNombreMasId(){
        //tabla categories: id, admin_phone, name, status(true), child_categories, timestamp
        $connWairbot = DB::initDbWairbot();
    
        $tabla = self::$tabla;
    
        $sql = "SELECT id, name FROM $tabla 
                WHERE admin_phone = 'AemosCursos' 
                AND (child_categories IS NULL OR child_categories = '' OR child_categories = '{}' OR child_categories = '[]' OR child_categories = '\"\"')";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $lista = $stmt->get_result();
    
        $lista = $lista->fetch_all(MYSQLI_ASSOC);
    
        //poner clases en formato id => nombre
        $lista = array_column($lista, 'name', 'id');
    
        $connWairbot->close();
    
        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $lista
        ];
    }

    //WairbotCategory::listarNombreMasIdRoles
    public static function listarNombreMasIdRoles(){
        $connWairbot = DB::initDbWairbot();

        $tabla = 'roles';

        $sql = "SELECT id, name FROM $tabla WHERE admin_phone = 'AemosCursos'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $lista = $stmt->get_result();

        $lista = $lista->fetch_all(MYSQLI_ASSOC);

        //poner clases en formato id => nombre
        $lista = array_column($lista, 'name', 'id');

        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $lista
        ];
    }
        
}