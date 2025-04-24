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

acciones_formativas: id, id_alumno, id_curso, id_clase, asistio(SI, NO), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

selects: id, nombre, tabla, menu, timestamp

data: id, admin_phone, name, category, public, user_phone, role, status, url_file, text, file_reference, date_reference, date_send_daily, date_send_monthly, date_send_date, timestamp

categories: id, admin_phone, name, status(true), child_categories, timestamp

roles: id, admin_phone, name, status(true), timestamp

*/

class Cursos{

    ###################################################################################################    
    ///////////////////////////////////////////// BASICAS /////////////////////////////////////////////
    ###################################################################################################

    public static $tabla = 'cursos';

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $data = $request['data'];

        $check = ['nombre'];

        $created = Tools::create($conn, $tabla, $data, $check);

        self::actualizarCursosEnFormularioWairbot();

        //crear rol en wairbot
        self::crearRolWairbot($data['nombre']);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Curso creado con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el curso',
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

        self::actualizarCursosEnFormularioWairbot();

        //actualizar rol en wairbot
        self::actualizarRolWairbot($id, $data['nombre']);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Curso actualizado con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el curso',
                'error' => $updated
            ];
        }
    }

    public static function eliminar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];

        //obtener datos del curso
        $sql = "SELECT * FROM $tabla WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $curso = $stmt->get_result();
        $curso = $curso->fetch_assoc();

        //cambiar id a id_original
        $curso['id_original'] = $curso['id'];
        unset($curso['id']);

        //crear curso en la tabla cursos_baja
        $tablaBaja = 'cursos_baja';
        Tools::create($conn, $tablaBaja, $curso, []);

        $check = [
            //'inscripciones' => 'id_curso',
            //'mensajes_programados' => 'id_curso',
            //'alumnos_cursos' => 'id_curso',
            //'acciones_formativas' => 'id_curso',
        ];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        self::actualizarCursosEnFormularioWairbot();

        //eliminar rol en wairbot
        self::eliminarRolWairbot($id);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Curso eliminado con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el curso',
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
            'message' => 'Cursos listados con éxito',
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

        self::actualizarCursosEnFormularioWairbot();

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

        //si el curso no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El curso no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Curso obtenido con éxito',
            'data' => $result,
        ];
    }

    ###################################################################################################    
    ///////////////////////////////////////////// PROPIAS /////////////////////////////////////////////
    ###################################################################################################

    public static function obtenerCursosQueNoIniciaronPorFecha(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
    
        $fechaActual = date('Y-m-d');
    
        $sql = "SELECT * FROM $tabla WHERE fecha_inicio > ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $fechaActual);
        $stmt->execute();
        $cursos = $stmt->get_result();
        $cursos = $cursos->fetch_all(MYSQLI_ASSOC);
    
        return [
            'status' => 'success',
            'message' => 'Cursos obtenidos con éxito',
            'data' => $cursos
        ];
    }

    private static function actualizarCursosEnFormularioWairbot(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;

        //obtene un string de los nombres de todos los cursos separados por coma
        $sql = "SELECT GROUP_CONCAT(nombre) as cursos FROM $tabla";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $cursos = $stmt->get_result();
        $cursos = $cursos->fetch_assoc();
        $cursos = $cursos['cursos'];
        
        //conectar a la base de datos de wairbot        
        $connWairbot = DB::initDbWairbot();
        //obtener de la tabla forms el campo fields donde la columna name table sea cursos_disponibles_AemosCursos
        $sql = "SELECT fields FROM forms WHERE name_table = 'cursos_disponibles_AemosCursos'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();
        $fields = $stmt->get_result();
        $fields = $fields->fetch_assoc();
        $fields = json_decode($fields['fields'], true);

        //cambiar el label del objeto con el nombre de columna curso_1 añadiendo [SELECTOR]: $cursos
        $fields[0]['label'] = "[SELECTOR]: $cursos";

        $fields = json_encode($fields);

        //actualizar el campo fields en la tabla forms
        $sql = "UPDATE forms SET fields = ? WHERE name_table = 'cursos_disponibles_AemosCursos'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('s', $fields);
        $stmt->execute();

        //actualizar los roles en wairbot
        self::actualizarRolesEnWairbot($cursos, $connWairbot);

        //cerrar la conexión wairbot
        $connWairbot->close();

        return [
            'status' => 'success',
            'message' => 'Cursos actualizados en el formulario de Wairbot'
        ];
    }

    private static function actualizarRolesEnWairbot($cursos, $connWairbot){
        $sql = "DELETE FROM roles WHERE admin_phone = 'AemosCursos'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->execute();

        $cursos = explode(',', $cursos);

        $values = [];
        foreach($cursos as $curso){
            $values[] = "('AemosCursos', '$curso', 'true')";
        }
        $sql = "INSERT INTO roles (admin_phone, name, status) VALUES " . implode(',', $values);
        $connWairbot->query($sql);

        return [
            'status' => 'success',
            'message' => 'Roles actualizados en Wairbot'
        ];
    }

    public static function listarNombreMasId(){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;

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

    //roles: id, admin_phone, name, status(true), timestamp
    public static function crearRolWairbot($nombre){
        $connWairbot = DB::initDbWairbot();
    
        $sql = "INSERT INTO roles (admin_phone, name, status)
                SELECT 'AemosCursos', ?, 'true'
                WHERE NOT EXISTS (
                    SELECT 1 FROM roles WHERE admin_phone = 'AemosCursos' AND name = ?
                )";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('ss', $nombre, $nombre);
        $stmt->execute();
    
        $connWairbot->close();
    }

    public static function actualizarRolWairbot($id, $nombre){
        $connWairbot = DB::initDbWairbot();

        $sql = "UPDATE roles SET name = ? WHERE id = ?";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('si', $nombre, $id);
        $stmt->execute();

        $connWairbot->close();
    }

    public static function eliminarRolWairbot($id){
        $connWairbot = DB::initDbWairbot();

        $sql = "DELETE FROM roles WHERE id = ?";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        //eliminar los usuarios con ese rol y el admin_phone AemosCursos en la tabla users
        $sql = "DELETE FROM users WHERE role = ? AND admin_phone = 'AemosCursos'";
        $stmt = $connWairbot->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $connWairbot->close();
    }
}