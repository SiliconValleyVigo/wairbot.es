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

class WairbotData{

    public static function crear($request){
        $connWairbot = DB::initDbWairbot();
        $conn = $_SESSION['db_connection'];

        $tabla = 'data';
        $admin_phone = 'AemosCursos';
        $data = $request['data'];

        //insertar el admin_phone en data
        $data['admin_phone'] = $admin_phone;
        $data['public'] = 'false';
        $data['status'] = 'true';
        $data['file_reference'] = "";
        $data['date_reference'] = "";
        $data['date_send_daily'] = "";
        $data['date_send_monthly'] = "";
        $data['date_send_date'] = "";

        //category contiene un id de la tabla categories hay que obtener el nombre de la categoría
        $category = $data['category'];
        
        $sql = "SELECT name FROM categories WHERE id = $category";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        //compobar si existe la categoría
        if($category){
            $category = $category['name'];
        }else{
            $category = "";
        }

        $data['category'] = $category;

        //role contiene un id de la tabla roles hay que obtener el nombre del rol
        $role = $data['role'];
        if(!empty($role)){
            $sql = "SELECT name FROM roles WHERE id = ?";
            $stmt = $connWairbot->prepare($sql);
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $role = $result->fetch_assoc();
        }
        $data['role'] = $role ? $role['name'] : "";

        //user_phone contiene un id de la tabla alumnos hay que obtener el telefono del alumno
        $user_phone = $data['user_phone'];
        if (!empty($user_phone)) {
            $sql = "SELECT telefono FROM alumnos WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_phone = $result->fetch_assoc();
        }
        $data['user_phone'] = $user_phone ? $user_phone['telefono'] : "";

        $check = [];

        $created = Tools::create($connWairbot, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Wairbot creado con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el Wairbot',
                'error' => $created
            ];
        }

        $connWairbot->close();
    }

    public static function editar($request){
        $connWairbot = DB::initDbWairbot();
        $conn = $_SESSION['db_connection'];

        $tabla = 'data';
        $id = $request['id'];
        $data = $request['data'];
        $admin_phone = 'AemosCursos';

        //insertar el admin_phone en data
        $data['admin_phone'] = $admin_phone;
        $data['public'] = false;
        $data['status'] = 'true';

        //category contiene un id de la tabla categories hay que obtener el nombre de la categoría
        $category = $data['category'];
        
        $sql = "SELECT name FROM categories WHERE id = $category";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        
        //compobar si existe la categoría
        if($category){
            $category = $category['name'];
        }else{
            $category = "";
        }

        $data['category'] = $category;

        //role contiene un id de la tabla roles hay que obtener el nombre del rol
        $role = $data['role'];
        if(!empty($role)){
            $sql = "SELECT name FROM roles WHERE id = ?";
            $stmt = $connWairbot->prepare($sql);
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $role = $result->fetch_assoc();
        }
        $data['role'] = $role ? $role['name'] : "";

        //user_phone contiene un id de la tabla alumnos hay que obtener el telefono del alumno
        $user_phone = $data['user_phone'];
        if (!empty($user_phone)) {
            $sql = "SELECT telefono FROM alumnos WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_phone);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_phone = $result->fetch_assoc();
        }
        $data['user_phone'] = $user_phone ? $user_phone['telefono'] : "";

        $updated = Tools::update($connWairbot, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Wairbot actualizado con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el Wairbot',
                'error' => $updated
            ];
        }

        $connWairbot->close();
    }

    public static function eliminar($request){
        $connWairbot = DB::initDbWairbot();

        $tabla = 'data';
        $id = $request['id'];

        $check = [];

        $deleted = Tools::delete($connWairbot, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Wairbot eliminado con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el Wairbot',
                'error' => $deleted
            ];
        }

        $connWairbot->close();
    }

    public static function listar($request){
        $connWairbot = DB::initDbWairbot();
        $conn = $_SESSION['db_connection'];

        $tabla = 'data';
        $search = array_key_exists('search', $request) ? $request['search'] : null;
        $nPagina = $request['nPagina'];

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $datosRelacionados = [];

        $orderBy = "";

        $tablaConfiguracion = 'wairbotdata';
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

        $tabla = 'data';
        $search = null;
        $nPagina = 1;

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $orderBy = "";

        $tablaConfiguracion = 'wairbotdata';
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

        $tabla = 'data';

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
        $conn = $_SESSION['db_connection'];

        $tabla = 'data';
        $id = $request['id'];
        $admin_phone = 'AemosCursos';

        $filtro = [
            'admin_phone' => 'AemosCursos'
        ];

        $datosRelacionados = [];

        $result = Tools::getById($connWairbot, $tabla, $id, $filtro, $datosRelacionados);

        //category contiene un name hay que obtener el id de la tabla categories donde el name y el admin_phone coincidan
        $category = $result['category'];
        $sql = "SELECT id FROM categories WHERE name = '$category' AND admin_phone = '$admin_phone'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $category_id = $stmt->get_result();
        $category_id = $category_id->fetch_assoc();

        //compobar si existe la categoría
        if($category_id){
            $category_id = $category_id['id'];
        }else{
            $category_id = "";
        }

        $result['category'] = $category_id;

        //role contiene un name hay que obtener el id de la tabla roles donde el name y el admin_phone coincidan
        $role = $result['role'];
        if(!empty($role)){
            $sql = "SELECT id FROM roles WHERE name = ? AND admin_phone = ?";
            $stmt = $connWairbot->prepare($sql);
            $stmt->bind_param("ss", $role, $admin_phone);
            $stmt->execute();
            $role_id = $stmt->get_result();
            $role_id = $role_id->fetch_assoc();
            $result['role'] = $role_id ? $role_id['id'] : "";
        }

        //user_phone contiene un telefono hay que obtener el id de la tabla alumnos donde el telefono coincida
        $user_phone = $result['user_phone'];
        if (!empty($user_phone)) {
            $sql = "SELECT id FROM alumnos WHERE telefono = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_phone);
            $stmt->execute();
            $user_phone_id = $stmt->get_result();
            $user_phone_id = $user_phone_id->fetch_assoc();
            $result['user_phone'] = $user_phone_id ? $user_phone_id['id'] : "";
        }

        //si el Wairbot no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El Wairbot no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Wairbot obtenido con éxito',
            'data' => $result,
        ];
    }
}