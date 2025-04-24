<?php

class PartesAsistencia{
    static public function getPdfBase64($request){
        $html = self::getHtml($request);
        $css = self::getCss();

        $data = [
            'html' => $html,
            'css' => $css,
            'size' => 'A4'
        ];

        $data_string = json_encode($data);

        $ch = curl_init('https://docker-navegador-ms.wairbot.es/generate-pdf');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    static public function getHtml($request) {
        $data = self::getData($request);
    
        $html = <<<HTML
    <body>
    HTML;
    
        $alumnos = $data['alumnos'];
        $chunks = array_chunk($alumnos, 10);
    
        foreach ($chunks as $chunk) {
            $tbody = '';
            foreach ($chunk as $alumno) {
                $tbody .= "<tr>";
                $tbody .= "<td>{$alumno['apellidos']}</td>";
                $tbody .= "<td>{$alumno['nombre']}</td>";
                $tbody .= "<td>{$alumno['dni']}</td>";
                $tbody .= "<td class='firma'></td>";
                $tbody .= "</tr>";
            }
    
            $html .= <<<HTML
        <div class="pagina">
            <!-- CABECERA -->
            <div class="cabacera">
                <div class="logo">
                    <img src="https://aemos.wairbot.es/imgs/emprego.svg" alt="Logo">
                </div>
                <div class="titulo">CONTROL DE ASISTENCIA</div>
                <div class="logo">
                    <img src="https://aemos.wairbot.es/imgs/logo.svg" alt="Logo">
                </div>
            </div>
    
            <!-- INFORMACION -->
            <div class="informacion">
                <div class="denominacionEtiqueta"><b>Denominación da acción Formativa:</b></div>
                <div class="denominacion">{$data['curso']['nombre']}</div>
                <div class="fechas">
                    <b>DATAS: </b> {$data['curso']['fechas']}
                </div>
                <div class="horario">
                    <b>HORARIO: </b> {$data['curso']['horario']}
                </div>
                <div class="lugarEtiqueta">
                    <b>Lugar de realización:</b>
                </div>
                <div class="lugar">
                    <pre>{$data['curso']['lugar']}</pre>
                </div>
                <div class="formador">
                    <b>Formador:</b> {$data['formador']['nombre']} - <b>DNI:</b> {$data['formador']['dni']}
                </div>
            </div>
    
            <!-- TABLA -->
            <table>
                <thead>
                    <tr>
                        <th colspan="3">DATOS DOS ASISTENTES</th>
                        <th class="firma">SINATURAS</th>
                    </tr>
                    <tr>
                        <th>APELIDOS</th>
                        <th>NOME</th>
                        <th>N.I.F.</th>
                        <th class="firma">{$data['clase']['fecha']}</th>
                    </tr>
                </thead>
                <tbody>
                    $tbody
                </tbody>
            </table>
    
            <!-- PIE -->
            <div class="pie">
                <div class="logo">
                    <img src="https://aemos.wairbot.es/imgs/xunta.svg" alt="Logo">
                </div>
                <div class="logoMinisterio">
                    <img src="https://aemos.wairbot.es/imgs/ministerio.svg" alt="Logo">
                </div>
            </div>
        </div>
    HTML;
        }
    
        $html .= <<<HTML
    </body>
    HTML;
    
        return $html;
    }

    static public function getCss(){
        $css = <<<CSS
        <style>
            @page {
                size: A4;
                margin: 0;
            }
    
            body {
                font-family: sans-serif;
                margin: 0;
                padding: 0;
            }
    
            .pagina {
                padding: 10mm;
                width: calc(210mm - 20mm);
                height: calc(297mm - 20mm);
                page-break-after: always;
            }
    
            .cabacera {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 5mm;
                color: #000080;
            }
    
            .cabacera .logo img {
                width: 40mm;
            }
    
            .cabacera .titulo {
                font-size: 18pt;
                font-weight: bold;
            }
    
            .informacion {
                margin-bottom: 5mm;
                padding: 3mm;
                border: 3px solid #afafaf;
                color: #000080;
            }
    
            .informacion .fechas, .informacion .horario, .informacion .lugarEtiqueta, .informacion .formador {
                margin-top: 3mm;
            }
    
            pre {
                margin: 0;
                font-size: 12pt;
                font-family: sans-serif;
            }
    
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 5mm;
            }
    
            th, td {
                border: 2px solid #afafaf;
                padding: 3mm;
                text-align: center;
                font-size: 10pt;
                height: 7mm;
            }
    
            th {
                font-weight: bold;
                color: #000080;
            }
    
            .firma {
                width: 50mm;
            }
    
            .pie {
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: #000080;
            }
    
            .pie .logo img {
                width: 70mm;
                height: 13mm;
            }
    
            .pie .logoMinisterio {
                width: 70mm;
                height: 13mm;
                text-align: right;
            }
    
            .pie .logoMinisterio img {
                height: 13mm;
                width: auto;
            }
        CSS;

        return $css;
    }

    static public function getData($request){
        $id_clase = $request['id'];

        //Obtener la clase de la tabla clases por su id
        $clase = self::getClase($id_clase);

        $id_formador = $clase['id_formador'];
        $id_curso = $clase['id_curso'];

        $formador = self::getFormador($id_formador);
        $curso = self::getCurso($id_curso);
        $alumnos = self::getAlumnos($id_curso);

        return [
            'clase' => $clase,
            'formador' => $formador,
            'curso' => $curso,
            'alumnos' => $alumnos
        ];
    }

    static public function getClase($id_clase){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM clases WHERE id = $id_clase";
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }

    static public function getFormador($id_formador){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM formadores WHERE id = $id_formador";
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }

    static public function getCurso($id_curso){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM cursos WHERE id = $id_curso";
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }

    static public function getAlumnos($id_curso){
        $conn = $_SESSION['db_connection'];
        
        //de la tabla inscripciones obtener un array con los id de los alumnos inscritos en la clase
        $sql = "SELECT id_alumno FROM inscripciones WHERE id_curso = $id_curso";
        $result = $conn->query($sql);
        $alumnos = [];
        while($row = $result->fetch_assoc()){
            $alumnos[] = $row['id_alumno'];
        }

        //de la tabla alumnos obtener apellidos, nombre y dni de los alumnos inscritos en la clase
        $alumnosData = [];
        $id_alumnos = implode(',', $alumnos); // Convierte el array de IDs en una cadena separada por comas
        $sql = "SELECT apellidos, nombre, dni FROM alumnos WHERE id IN ($id_alumnos)";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $alumnosData[] = $row;
        }

        return $alumnosData;
    }
}