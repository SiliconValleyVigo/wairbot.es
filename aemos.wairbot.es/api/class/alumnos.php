<?php

require 'vendor/autoload.php';

/* 
alumnos: id, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehículo, mensajes_enviados, referencia, timestamp

alumnos_baja: id, id_original, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehículo, mensajes_enviados, referencia, timestamp

cursos: id, nombre (unico), horas, financiacion(NORMAL, SEPE), previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, timestamp

cursos_baja: id, id_original, nombre, horas, financiacion, previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, timestamp

clases: id, id_curso, nombre, fecha, hora_inicio, hora_fin, lugar, observaciones, referencia, timestamp

inscripciones: id, id_alumno, id_curso, estado(PROPUESTO, SOLICITADO, ACEPTADO, CURSANDO, RECHAZADO, FINALIZADO), timestamp

cita_previa: id, id_alumno, disponibilidad, timestamp

mensajes_programados: id, id_curso, numero_mensajes, intervalo_dias, fecha_primer_mensaje, hora_envio, mensaje, observaciones_internas, referencia, timestamp

acciones_formativas: id, id_alumno, id_curso, id_clase, asistio(SI, NO), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

selects: id, nombre, tabla, menu, timestamp

*/

class Alumnos{
    public static $tabla = 'alumnos';

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $data = $request['data'];

        //si data contiene telefono, eliminar cualquier caracter que no sea número y si comienza por 6 o 7, añadir 34
        if(array_key_exists('telefono', $data)){
            $telefono = $data['telefono'];
            $telefono = preg_replace('/[^0-9]/', '', $telefono);
            if(strlen($telefono) == 9 && (substr($telefono, 0, 1) == '6' || substr($telefono, 0, 1) == '7')){
                $telefono = '34' . $telefono;
            }
            $data['telefono'] = $telefono;
        }

        $check = ['numero', 'email', 'telefono'];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Alumno creado con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear el alumno',
                'error' => $created
            ];
        }
    }

    public static function editar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];
        $data = $request['data'];

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
                'message' => 'Alumno actualizado con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el alumno',
                'error' => $updated
            ];
        }  

    }

    public static function eliminar($request){ //antes de eliminar se pasa a la tabla alumnos_baja
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];

        //obtener datos del alumno
        $sql = "SELECT * FROM $tabla WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $alumno = $stmt->get_result();
        $alumno = $alumno->fetch_assoc();

        //cambiar la key id por id_original
        $alumno['id_original'] = $alumno['id'];
        unset($alumno['id']);

        //crear alumno en la tabla alumnos_baja
        $tablaBaja = 'alumnos_baja';
        Tools::create($conn, $tablaBaja, $alumno, []);

        $check = [
            //'alumnos_cursos' => 'id_alumno',
            //'inscripciones' => 'id_alumno',
            //'cita_previa' => 'id_alumno',
            //'mensajes_programados' => 'id_alumno',
            //'acciones_formativas' => 'id_alumno',
        ];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Alumno eliminado con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el alumno',
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
            'message' => 'Alumnos listados con éxito',
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

        //si el alumno no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'El alumno no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Alumno obtenido con éxito',
            'data' => $result,
        ];
    }

    //Alumnos
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
}
