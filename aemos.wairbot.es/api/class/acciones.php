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

*/

class Acciones{
    
    ###################################################################################################    
    ///////////////////////////////////////////// BASICAS /////////////////////////////////////////////
    ###################################################################################################

    public static $tabla = 'acciones_formativas';

    public static function crear($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $data = $request['data'];

        $check = [];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Acción creada con éxito',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la acción',
                'error' => $created
            ];
        }
    }

    public static function editar($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $id = $request['id'];
        $data = $request['data'];

        //eliminar los campos que contienen ALU_ o CUR_ o CLA_
        $data = array_filter($data, function($key) {
            return !preg_match('/(ALU_|CUR_|CLA_)/', $key);
        }, ARRAY_FILTER_USE_KEY);

        $updated = Tools::update($conn, $tabla, $id, $data);

        //obtener el id_curso y las clases del curso con fecha anterior a la fecha actual
        $sql = "SELECT c.id_curso, c.id_clase, cl.id AS clase_id 
        FROM $tabla c 
        JOIN clases cl ON c.id_curso = cl.id_curso 
        WHERE c.id = ? AND cl.fecha < CURDATE()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $clases = [];
        while ($row = $result->fetch_assoc()) {
            $idCurso = $row['id_curso'];
            $idClase = $row['id_clase'];
            $clases[] = $row['clase_id'];
        }

        //obtener de la tabla alumnos la columna historial, de la tabla cursos el nombre del curso y de la tabla clases la fecha y el nombre de la clase
        $sql = "SELECT a.historial, a.id AS id_alumno, cu.nombre AS curso, cl.fecha, cl.nombre AS clase, c.asistio
        FROM $tabla c
        JOIN alumnos a ON c.id_alumno = a.id
        JOIN cursos cu ON c.id_curso = cu.id
        JOIN clases cl ON c.id_clase = cl.id
        WHERE c.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();  
            
        $historialAlumno = $data['historial'];
        $cursoAlumno = $data['curso'];
        $fechaClase = $data['fecha'];
        $nombreClase = $data['clase'];
        $idAlumno = $data['id_alumno'];
            
        //añadir si asistió o no a la clase
        $asistio = $data['asistio'] == 1 ? 'asistió' : 'no asistió';
            
        //añadir al historial del alumno la acción formativa
        $historialAlumno .= "El alumno $asistio a la clase $nombreClase del curso $cursoAlumno con fecha $fechaClase\n";

        //actualizar el historial del alumno
        $sql = "UPDATE alumnos SET historial = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $historialAlumno, $idAlumno);
        $stmt->execute();

        //obtener las acciones formativas con el alumno ausente en las clases anteriores
        $sql = "SELECT id FROM $tabla WHERE id_clase IN (" . implode(',', $clases) . ") AND asistio = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $accionesNoAsistidas = $result->fetch_all(MYSQLI_ASSOC);

        //contar $clases
        $numeroClases = count($clases);

        //contar $alumnos
        $sql = "SELECT COUNT(DISTINCT id_alumno) AS numeroAlumnos FROM $tabla WHERE id_curso = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $result = $stmt->get_result();
        $numeroAlumnos = $result->fetch_assoc()['numeroAlumnos'];

        //contar $accionesNoAsistidas
        $numeroAccionesNoAsistidas = count($accionesNoAsistidas);

        $asistencias = ($numeroClases * $numeroAlumnos) - $numeroAccionesNoAsistidas;

        //actualizar el curso
        $sql = "UPDATE cursos SET asistencias = ?, ausencias = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $asistencias, $numeroAccionesNoAsistidas, $idCurso);
        $stmt->execute();

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Acción actualizada con éxito',
            ];

        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la acción',
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
                'message' => 'Acción eliminada con éxito',
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la acción',
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
        $datosRelacionados = [
            'id_alumno' => 'alumnos',
            'id_curso' => 'cursos',
            'id_clase' => 'clases'
        ];

        $orderBy = "";

        $tablaConfiguracion = 'acciones';
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
            'message' => 'Acciones listadas con éxito',
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
        $datosRelacionados = [
            'id_alumno' => 'alumnos',
            'id_curso' => 'cursos',
            'id_clase' => 'clases'
        ];

        $orderBy = "";

        $tablaConfiguracion = 'acciones';
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
        $datosRelacionados = [
            'id_alumno' => 'alumnos',
            'id_curso' => 'cursos',
            'id_clase' => 'clases'
        ];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        //si la acción no existe
        if(!$result){
            return [
                'status' => 'error',
                'message' => 'La acción no existe',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Acción obtenida con éxito',
            'data' => $result,
        ];
    }

    ###################################################################################################
    ///////////////////////////////////////////// PROPIAS /////////////////////////////////////////////
    ###################################################################################################

    //crear acciones formativas cruzando datos de alumnos y las clases de un curso
    public static function crearAccionesFormativas($request){
        $conn = $_SESSION['db_connection'];
        $idCurso = $request['id_curso'];
    
        //obtener alumnos inscritos en el curso
        $sql = "SELECT id_alumno FROM inscripciones WHERE id_curso = ? AND estado = 'ACEPTADO'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $alumnos = $stmt->get_result();
        $alumnos = $alumnos->fetch_all(MYSQLI_ASSOC);

        //si no hay alumnos inscritos en el curso no se pueden crear acciones formativas
        if(empty($alumnos)){
            return [
                'status' => 'error',
                'message' => 'No hay alumnos inscritos en el curso'
            ];
        }
    
        //obtener clases del curso
        $sql = "SELECT * FROM clases WHERE id_curso = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $clases = $stmt->get_result();
        $clases = $clases->fetch_all(MYSQLI_ASSOC);

        //si no hay clases en el curso no se pueden crear acciones formativas
        if(empty($clases)){
            return [
                'status' => 'error',
                'message' => 'No hay clases en el curso'
            ];
        }
    
        //crear acciones formativas para cada alumno y cada clase
        $values = [];
        foreach($alumnos as $alumno){
            foreach($clases as $clase){
                $values[] = "({$alumno['id_alumno']}, $idCurso, {$clase['id']}, 'NO', '', 'NO', 'NO', 'NO')";
            }
        }
    
        if (!empty($values)) {
            $sql = "INSERT INTO acciones_formativas (id_alumno, id_curso, id_clase, asistio, notas, curso_igualdad_previo, entrega_de_material, alumno_trabajando) VALUES " . implode(',', $values);
            $conn->query($sql);
        }

        $connWairbot = DB::initDbWairbot();
        //tabla users de wairbot: id, admin_phone, name, user_phone, reference, role, status, timestamp

        $admin_phone = 'AemosCursos';
        
        //el role es el nombre del curso
        $sql = "SELECT nombre FROM cursos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $result = $stmt->get_result();
        $nombreCurso = $result->fetch_assoc()['nombre'];

        $role = $nombreCurso;

        //obtener los alumnos inscritos en el curso
        $sql = "SELECT id_alumno FROM inscripciones WHERE id_curso = ? AND estado = 'ACEPTADO'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $result = $stmt->get_result();
        $alumnos = $result->fetch_all(MYSQLI_ASSOC);

        //obtener los datos de los alumnos
        $alumnosData = [];
        foreach($alumnos as $alumno){
            $sql = "SELECT nombre, apellidos, telefono FROM alumnos WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $alumno['id_alumno']);
            $stmt->execute();
            $result = $stmt->get_result();
            $alumnoData = $result->fetch_assoc();
            $alumnosData[] = $alumnoData;
        }

        //insertar los datos en la tabla users de wairbot
        $values = [];
        foreach($alumnosData as $alumno){
            $values[] = "('$admin_phone', '{$alumno['nombre']} {$alumno['apellidos']}', '{$alumno['telefono']}', '', '$role', 'ACTIVE', NOW())";
        }

        if (!empty($values)) {
            $sql = "INSERT INTO users (admin_phone, name, user_phone, reference, role, status, timestamp) VALUES " . implode(',', $values);
            $connWairbot->query($sql);
        }
    
        return [
            'status' => 'success',
            'message' => 'Acciones formativas creadas con éxito'
        ];
    }

    public static function obtenerAccionesFormativasPorCurso($request){
        $conn = $_SESSION['db_connection'];
        $idCurso = $request['id_curso'];
    
        // Unificar las consultas SQL
        $sql = "SELECT 
                    acciones_formativas.*, 
                    alumnos.id AS id_alumno, 
                    alumnos.nombre AS nombre_alumno, 
                    alumnos.apellidos, 
                    clases.id AS id_clase, 
                    clases.nombre AS nombre_clase, 
                    clases.fecha,
                    clases.hora_inicio,
                    clases.hora_fin,
                    clases.lugar,
                    clases.observaciones AS observaciones_clase
                FROM 
                    acciones_formativas 
                INNER JOIN 
                    alumnos ON acciones_formativas.id_alumno = alumnos.id 
                INNER JOIN 
                    clases ON acciones_formativas.id_clase = clases.id 
                WHERE 
                    acciones_formativas.id_curso = ?";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idCurso);
        $stmt->execute();
        $result = $stmt->get_result();
        $acciones = $result->fetch_all(MYSQLI_ASSOC);
    
        // Agrupar acciones formativas por cada clase
        $accionesPorClase = [];
        foreach($acciones as $accion){
            if (!isset($accionesPorClase[$accion['id_clase']])) {
                $accionesPorClase[$accion['id_clase']] = [
                    'id_clase' => $accion['id_clase'],
                    'nombre' => $accion['nombre_clase'],
                    'fecha' => $accion['fecha'],
                    'hora_inicio' => $accion['hora_inicio'],
                    'hora_fin' => $accion['hora_fin'],
                    'lugar' => $accion['lugar'],
                    'observaciones' => $accion['observaciones_clase'],
                    'acciones' => []
                ];
            }
            $accionesPorClase[$accion['id_clase']]['acciones'][] = [
                'id' => $accion['id'],
                'id_alumno' => $accion['id_alumno'],
                'nombre' => $accion['nombre_alumno'],
                'apellidos' => $accion['apellidos'],
                'asistio' => $accion['asistio'],
                'notas' => $accion['notas'],
                'curso_igualdad_previo' => $accion['curso_igualdad_previo'],
                'entrega_de_material' => $accion['entrega_de_material'],
                'alumno_trabajando' => $accion['alumno_trabajando']
            ];
        }
    
        return [
            'status' => 'success',
            'message' => 'Acciones formativas obtenidas con éxito',
            'data' => $accionesPorClase
        ];
    }
}