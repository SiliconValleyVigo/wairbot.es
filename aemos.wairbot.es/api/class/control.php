<?php

/* 
alumnos: id, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet, vehículo, mensajes_enviados, referencia, timestamp

cursos: id, nombre (unico), horas, financiacion(NORMAL, SEPE), previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, fecha_inicio timestamp

clases: id, id_curso, nombre, fecha, hora_inicio, hora_fin, lugar, observaciones, referencia, timestamp

acciones_formativas: id, id_alumno, id_curso, id_clase, asistio(0, 1), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

*/
class Control{
    public static function listar($request){
        $conn = $_SESSION['db_connection'];

        // Obtener todos los cursos
        $cursos_sql = "SELECT id, nombre, numero_clases FROM cursos WHERE fecha_inicio <= NOW()";
        $cursos_result = $conn->query($cursos_sql);

        $cursos = [];
        if ($cursos_result->num_rows > 0) {
            while($curso = $cursos_result->fetch_assoc()) {
                $cursos[$curso['id']] = [
                    'curso_id' => $curso['id'],
                    'curso_nombre' => $curso['nombre'],
                    'numero_clases' => $curso['numero_clases']
                ];
            }
        }

        // Obtener los alumnos y sus acciones formativas
        $sql = "
            SELECT 
                a.id AS alumno_id, 
                a.nombre AS alumno_nombre, 
                a.apellidos AS alumno_apellidos, 
                c.id AS curso_id, 
                c.nombre AS curso_nombre, 
                COUNT(af.id) AS acciones_formativas,
                COUNT(CASE WHEN af.asistio = 1 THEN 1 END) AS asistencias
            FROM 
                alumnos a
            CROSS JOIN 
                cursos c
            LEFT JOIN 
                acciones_formativas af ON a.id = af.id_alumno AND c.id = af.id_curso
            WHERE 
                c.fecha_inicio <= NOW()
            GROUP BY 
                a.id, c.id
        ";

        $result = $conn->query($sql);

        $alumnos_cursos = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $alumno_id = $row['alumno_id'];
                $curso_id = $row['curso_id'];
                $curso_info = [
                    'curso_id' => $curso_id,
                    'curso_nombre' => $row['curso_nombre'],
                    'acciones_formativas' => $row['acciones_formativas'] > 0 ? $row['asistencias'] . '/' . $cursos[$curso_id]['numero_clases'] : '-'
                ];

                $alumno_nombre_completo = $row['alumno_nombre'] . ' ' . $row['alumno_apellidos'];
                $alumno_url = '<div onclick="openAlumnos(' . $alumno_id . ')" style="margin: 5px; cursor: pointer;">' . $alumno_nombre_completo . '</div>';

                if (!isset($alumnos_cursos[$alumno_id])) {
                    $alumnos_cursos[$alumno_id] = [
                        'alumno_id' => $alumno_id,
                        'alumno_nombre' => $alumno_url,
                        'cursos' => []
                    ];
                }

                $alumnos_cursos[$alumno_id]['cursos'][$curso_id] = $curso_info;
            }
        }

        // Asegurarse de que cada alumno tenga todos los cursos
        foreach ($alumnos_cursos as &$alumno) {
            foreach ($cursos as $curso_id => $curso) {
                if (!isset($alumno['cursos'][$curso_id])) {
                    $alumno['cursos'][$curso_id] = [
                        'curso_id' => $curso_id,
                        'curso_nombre' => $curso['curso_nombre'],
                        'acciones_formativas' => '-'
                    ];
                }
            }
            // Ordenar los cursos por ID
            ksort($alumno['cursos']);
            // Convertir a array indexado
            $alumno['cursos'] = array_values($alumno['cursos']);
        }

        $alumnosConAsistenciasACursos = array_values($alumnos_cursos);

        // Recorrer cada alumno y cada curso y en cada acción formativa si contiene "/" comprobar si el primer número es igual al segundo y si no es igual poner en rojo
        foreach ($alumnosConAsistenciasACursos as &$alumno) {
            foreach ($alumno['cursos'] as &$curso) {
                if (strpos($curso['acciones_formativas'], '/') !== false) {
                    $asistencias = explode('/', $curso['acciones_formativas']);
                    if ($asistencias[0] != $asistencias[1]) {
                        $curso['acciones_formativas'] = '<span style="color: red;">' . $curso['acciones_formativas'] . '</span>';
                    }
                }
                $curso['acciones_formativas'] = '<div onclick="openCursos(' . $curso['curso_id'] . ')" style="margin: 5px; cursor: pointer;">' . $curso['acciones_formativas'] . '</div>';
            }
        }

        // Formatear la salida final
        $salida = [];
        foreach ($alumnosConAsistenciasACursos as $alumno) {
            $alumno_data = [
                'alumno_id' => $alumno['alumno_id'],
                'alumno_nombre' => $alumno['alumno_nombre']
            ];
            foreach ($alumno['cursos'] as $curso) {
                $curso_key = strtolower(str_replace(' ', '_', $curso['curso_nombre']));
                $alumno_data[$curso_key] = $curso['acciones_formativas'];
            }
            $salida[] = $alumno_data;
        }

        //eliminar alumno_id de la salida
        foreach ($salida as &$alumno) {
            $alumno['id'] = $alumno['alumno_id'];
            unset($alumno['alumno_id']);
        }

        //obtener el primer elemento de la salida
        $primerElemento = $salida[0];

        //obtener las claves del primer elemento
        $claves = array_keys($primerElemento);

        //crear un array con clave y dar el valor de 35 a todas las claves menos a alumno_nombre que le damos el valor de 250
        $columnas = [];
        //alumno_nombre =>250 , claveX => 35 , claveY => 35, claveZ => 35
        foreach ($claves as $clave) {
            $columnas[$clave] = $clave == 'alumno_nombre' ? 250 : 35;
        }

        $visualizacion = [];
        $count = 1;
        foreach ($claves as $clave) {
            if($clave == 'id') continue;
            //si es alumno_nombre horizontal y manejadores true, si no vertical y manejadores false
            $visualizacion[] = [
                'id' => $clave,
                'orden' => $count,
                'visible' => true,
                'horizontalOrVertical' => $clave == 'alumno_nombre' ? 'horizontal' : 'vertical',
                'mostrarManejadores' => $clave == 'alumno_nombre' ? true : false
            ];
        }

        //en la tabla configuracion_tabla donde nombre_tabla sea "control" actualizar los json en las columnas "columnas" y "visualizacion"
        $sql = "UPDATE configuracion_tablas SET columnas = '" . json_encode($columnas) . "', visualizacion = '" . json_encode($visualizacion) . "' WHERE nombre_tabla = 'control'";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Alumnos y sus acciones formativas listados con éxito',
            'data' => [
                'paginaActual' => 1,
                'paginasTotales' => 1,
                'rows_per_page' => 999,
                'grid_or_table' => 'table',
                'data' => $salida
            ]
        ];

        return $salida;
    }
}
