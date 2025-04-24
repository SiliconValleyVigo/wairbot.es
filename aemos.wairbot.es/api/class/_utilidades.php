<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class Utilidades {
    public static $relacionales = ['ALU' => 'alumnos', 'CUR' => 'cursos']; //[PREFIX => TABLE]

    public static function obtenerElemenosDeUnaColumnaSinRepetir($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $columna = $request['columna'];
    
        // Extraer el prefijo del nombre de la columna
        $pos = strpos($columna, '_');
        if ($pos !== false) {
            $prefix = substr($columna, 0, $pos);
            $columnaSinPrefijo = substr($columna, $pos + 1);
    
            // Verificar si el prefijo está en $relacionales
            if (array_key_exists($prefix, self::$relacionales)) {
                $tabla = self::$relacionales[$prefix];
                $columna = $columnaSinPrefijo;
            }
        }
    
        $sql = "SELECT DISTINCT $columna FROM $tabla";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()){
            $data[] = $row[$columna];
        }
        return $data;
    }

    /*
    Actualizar un registro de una tabla
    tabla: tabla,
        columna: columna,
        id: idRegistro,
        value: value
    */
    public static function actualizarRegistro($request){
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla']; //self::obtenerTabla($request['tabla']);
        $columna = $request['columna'];
        $id = $request['id'];
        $value = $request['value'];

        $sql = "UPDATE $tabla SET $columna = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();

        //si la tabla es "inscripciones" se actualiza el historial del alumno
        if($tabla == 'inscripciones'){
            //obtener el id_alumno y el id_curso
            $sql = "SELECT id_alumno, id_curso FROM inscripciones WHERE id = $id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $id_alumno = $row['id_alumno'];
            $id_curso = $row['id_curso'];
            $estado = $value;

            //insertar en el historial
            Inscripciones::actualizarHistorialAlumno($conn, $id_alumno, $id_curso, $estado);
        }

        return [
            'status' => 'success',
            'message' => 'Registro actualizado correctamente'
        ];
    }

    private static function obtenerTabla($tablaPropiedades){
        //$tablas = ['acciones', 'acciones_periodicas', 'beneficiarios', 'beneficiarios_baja', 'coordinadores', 'voluntarios', 'voluntarios_baja'];
        if($tablaPropiedades == 'acciones' || $tablaPropiedades == 'acciones_periodicas'){
            return 'acciones';
        }

        if($tablaPropiedades == 'beneficiarios' || $tablaPropiedades == 'beneficiarios_baja'){
            return 'beneficiarios';
        }

        if($tablaPropiedades == 'coordinadores' || $tablaPropiedades == 'voluntarios' || $tablaPropiedades == 'voluntarios_baja'){
            return 'usuarios';
        }
    }

    public static function insertarDatosDesdeExcel($request) {
        $conn = $_SESSION['db_connection'];
        $tabla = $request['tabla'];
        $excelBase64 = $request['excel'];
        $reiniciarTabla = $request['reiniciar'];
    
        // Decodificar el archivo Excel
        $excelData = base64_decode($excelBase64);
        if ($excelData === false) {
            throw new \Exception('Error al decodificar el archivo base64.');
        }
    
        // Guardar el archivo en la carpeta public
        $publicDir = 'public';
        $fileName = 'uploaded_excel_' . time() . '.xlsx';
        $filePath = $publicDir . '/' . $fileName;
        file_put_contents($filePath, $excelData);
    
        // Verificar el tipo MIME del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
    
        $validMimeTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel' // .xls
        ];
    
        if (!in_array($mimeType, $validMimeTypes)) {
            unlink($filePath);
            throw new \Exception('El archivo no es un archivo Excel válido. Tipo MIME detectado: ' . $mimeType);
        }
    
        // Leer el contenido del archivo Excel
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
    
        // Verificar si se debe reiniciar la tabla
        if ($reiniciarTabla) {
            $conn->query("DELETE FROM $tabla");
        }
    
        // Obtener los nombres de las columnas del Excel
        $columnNames = array_shift($rows);
    
        // Obtener los nombres de las columnas de la tabla
        $result = $conn->query("SHOW COLUMNS FROM $tabla");
        $tableColumns = [];
        while ($row = $result->fetch_assoc()) {
            $tableColumns[] = $row['Field'];
        }
    
        // Filtrar las columnas que existen en la tabla
        $validColumns = array_intersect($columnNames, $tableColumns);
    
        // Función para convertir fechas al formato YYYY-MM-DD
        function convertirFecha($valor) {
            //si contiene un / cambiarlo por -
            if(strpos($valor, '/') !== false){
                $valor = str_replace('/', '-', $valor);
            }

            $date = date_create_from_format('d-m-Y', $valor);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
            
            return $valor;
        }
    
        // Iterar sobre las filas del Excel
        foreach ($rows as $row) {
            $data = array_combine($columnNames, $row);
            $filteredData = array_intersect_key($data, array_flip($validColumns));
    
            // Convertir fechas al formato YYYY-MM-DD
            foreach ($filteredData as $key => $value) {
                $filteredData[$key] = convertirFecha($value);
            }
    
            if (isset($filteredData['id']) && !empty($filteredData['id'])) {
                // Actualizar el registro
                $id = $filteredData['id'];
                unset($filteredData['id']);
                $setClause = implode(", ", array_map(function($col) { return "$col = ?"; }, array_keys($filteredData)));
                $sql = "UPDATE $tabla SET $setClause WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $params = array_values($filteredData);
                $params[] = $id;
                $stmt->bind_param(str_repeat("s", count($filteredData)) . "i", ...$params);
            } else {
                // Insertar un nuevo registro
                $columns = implode(", ", array_keys($filteredData));
                $placeholders = implode(", ", array_fill(0, count($filteredData), "?"));
                $sql = "INSERT INTO $tabla ($columns) VALUES ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(str_repeat("s", count($filteredData)), ...array_values($filteredData));
            }
            $stmt->execute();
        }
    
        // Eliminar el archivo temporal
        unlink($filePath);
    
        return [
            'status' => 'success',
            'message' => 'Datos insertados/actualizados correctamente'
        ];
    }

    public static function enviarMensajeWhatsapp($request){
        $userPhone = $request['userPhone'];
        $message = $request['message'];
        $file = "";
        $codigoAdministradorBot = "AemosCursos";
        $passwordAdministradorBot = "15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225";

        Tools::sendMessageWhatsapp($userPhone, $message, $file, $codigoAdministradorBot, $passwordAdministradorBot);

        return [
            'status' => 'success',
            'message' => 'Mensaje enviado correctamente'
        ];
    }

    public static function iniciarFormularioEnWairbot($request){
        $curso = $request['curso'];
        $userPhone = $request['userPhone'];

        $adminPhone = "AemosCursos";
        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3MTc1MDY5NjAsImRhdGEiOlt7IkFEX2lkXyI6IjEzMyIsIkFEX2VtYWlsXyI6IkJvdFBpbG90b1BydWViYSIsIkFEX3Bhc3N3b3JkXyI6ImYwYTM5NjUyYjZlYTM2NjhlMWRiMjIxYzRiMTAzNGE2NjdmODNhYmRmMGRhYWQwZmNmNDFmMzE5YjlkYTdiZjgiLCJBRF90aXBvX3NlcnZpY2lvXyI6IjEiLCJBRF92YWxpZGF0ZWRfIjoiMSIsIkFEX25vbWJyZV9kZWxfc2VydmljaW9fX3RleHQiOiJHZXN0aW9uIGRlIGRhdG9zIiwiQURfbnVtZXJvX2RlX3RlbGVmb25vX2RlbF9ib3RfX3RlbCI6IkJvdFBpbG90b1BydWViYSIsIkFEX251bWVyb19kZV90ZWxlZm9ub19jb25fcGVybWlzb19kZV9hZG1pbmlzdHJhZG9yX190ZXh0IjpudWxsLCJBRF9wcmVmaWpvX2ludGVybmFjaW9uYWxfX251bWJlciI6IjM0IiwiQURfaWRpb21hX190ZXh0IjoiZXMiLCJBRF91cmxfZW52aW9fZGVfZXN0YWRvc19fdGV4dCI6bnVsbCwidGltZXN0YW1wIjoiMjAyNC0wNi0wNCAxNToxNTo1NSIsIkFEX2ludGVyZmFjZV9kYXRhXyI6IntcIm1lc2FzXCI6W3tcImlkXCI6XCIxXCIsXCJub21icmVcIjpcIk1lc2EgMVwiLFwiZXN0YWRvXCI6XCJsaWJyZVwiLFwiY29tYW5kYVwiOlwiXCJ9XSxcInByb2R1Y3Rvc1wiOlt7XCJpZFwiOlwiMVwiLFwibm9tYnJlXCI6XCJQcm9kdWN0byAxXCIsXCJjYXRlZ29yaWFcIjpcIjFcIixcInByZWNpb1wiOlwiMS4wMFwifV0sXCJjYXRlZ29yaWFzXCI6W3tcImlkXCI6XCIxXCIsXCJub21icmVcIjpcIkNhdGVnb3JpYSAxXCJ9XSxcImNvbWFuZGFzXCI6W10sXCJtZW5zYWplc1wiOltdfSIsIkFEX3Blcm1pc29zXyI6IntcIm5NYXhpbW9FbGVtZW5vc1wiOjEwMDAsXCJmb3JtdXNhcmlvc0VuTWVudVJhaXpcIjp0cnVlLFwiZm9ybXVsYXJpb3NDb25maVwiOnRydWUsXCJjb25maWd1cmFjaW9uXCI6dHJ1ZSxcInVzdWFyaW9zXCI6dHJ1ZSxcInJvbGVzXCI6dHJ1ZSxcImRhdG9zXCI6ZmFsc2UsXCJmb3JtdWxhcmlvc1wiOjEwMCxcInRyYWR1Y2Npb25lc1wiOnRydWUsXCJjb21hbmRhc1wiOmZhbHNlLFwiY2hhdFwiOmZhbHNlLFwicGx1Z2luc1wiOntcImNvbXVuaWRhZGVzUGlsb3RvQ3JlYXJUZWNuaWNvXCI6dHJ1ZSxcImNvbXVuaWRhZGVzUGlsb3RvQ3JlYXJJbmNpZGVuY2lhXCI6dHJ1ZX0sXCJkYXRvc0F1eGlsaWFyZXNcIjp7XCJ0ZWxlZm9ub0FkbWluaXN0cmFkb3JcIjpcIjM0NjAwODU3ODgyXCJ9LFwiY2l0YVByZXZpYVwiOmZhbHNlfSIsIkFEX2NvbnRhZG9yX2NvbnZlcnNhY2lvbmVzXyI6bnVsbCwiQURfcGx1Z2luZ3NfY29uZmlnXyI6Ilt7XCJub21icmVfcGx1Z2luXCI6XCJDb211bmlkYWRlc1BpbG90b0NyZWFyVGVjbmljb1wiLFwidGFibGFcIjpcInRlY25pY29zX0JvdFBpbG90b1BydWViYVwiLFwidGlwb1wiOlwiY29sdW1uYVwiLFwibm9tYnJlX251ZXZhX2NvbHVtbmFcIjpcIlwiLFwiZnVuY2lvbmVzXCI6W1wiaGFuZGxlRGF0YVwiXSxcInRlbXBsYXRlXCI6e1widGFibGFcIjpcIlwifSxcImNvbHVtbmFzXCI6W10sXCJ2YWxvcmVzXCI6W10sXCJjbGFzZVwiOlwiQ29tdW5pZGFkZXNQaWxvdG9DcmVhclRlY25pY29cIixcInRpcG9fcmVzdWx0YWRvXCI6XCJ0ZXh0XCJ9LHtcIm5vbWJyZV9wbHVnaW5cIjpcIkNvbXVuaWRhZGVzUGlsb3RvQ3JlYXJJbmNpZGVuY2lhXCIsXCJ0YWJsYVwiOlwiaW5jaWRlbmNpYXNfQm90UGlsb3RvUHJ1ZWJhXCIsXCJ0aXBvXCI6XCJjb2x1bW5hXCIsXCJub21icmVfbnVldmFfY29sdW1uYVwiOlwiXCIsXCJmdW5jaW9uZXNcIjpbXCJoYW5kbGVEYXRhXCJdLFwidGVtcGxhdGVcIjp7XCJ0YWJsYVwiOlwiXCJ9LFwiY29sdW1uYXNcIjpbXSxcInZhbG9yZXNcIjpbXSxcImNsYXNlXCI6XCJDb211bmlkYWRlc1BpbG90b0NyZWFySW5jaWRlbmNpYVwiLFwidGlwb19yZXN1bHRhZG9cIjpcInRleHRcIn1dIiwiQURfbW9zdHJhcl9jb2x1bW5hc191c3VhcmlvX3VzZXJpbnNlcnRkYXRhX190ZXh0IjpudWxsLCJBRF9jb25maWd1cmFjaW9uX3RpZW5kYV9fdGV4dCI6IiJ9XX0.oYp5g2xxDDJVPGXm-c6OhvvydAfSyPFuMV33FBvVQmM";

        //$url = "https://gestion-datos.wairbot.com/bot-datos/";
        $url = "http://localhost/wairbot-datos/bot-datos/";

        $data = [
            'token' => $token,
            'key' => $token,
            "text"=> "user-exp",
            "expClass" => "Puentes",
            "expFunction" => "iniciarFormularioQuery",
            "admin_phone" => $adminPhone,
            "adminPhone" => $adminPhone,
            "codigoBot" => $adminPhone,
            "userPhone" => $userPhone,
            "name_opcion" => "cursos_disponibles",
            "respuestas" => [
                $curso
            ]
        ];

        //consulta curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return [
            'status' => 'success',
            'message' => 'Formulario iniciado correctamente'
        ];
    }
}
