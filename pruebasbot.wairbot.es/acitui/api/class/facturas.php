<?php

/* 
USUARIOS:
id
rol
email
password
n_socio
token
estado (PREINSCRITO, INSCRITO, BAJA)
nombre_comercial
razon_social
cif
titular
dni
denominacion_actividad
iae
antiguedad
n_trabajadores_autonomos
n_trabajadores_cuenta_ajena
direccion
numero_direccion
poblacion
codigo_postal
telefono
fax
movil
nombre_banco
localidad_banco
titular_banco
iban
periodo_pago (3, 6, 12)
forma_pago (EFECTIVO, TRANSFERENCIA, CARGO EN CUENTA)
fecha_ultima_factura
solicitud_baja
motivo_baja
timestamp

FACTURAS:
id
id_socio
n_socio
nombre_comercial
razon_social
cif
forma_pago
iban
importe
concepto
n_factura
fecha_factura
conformada (0, 1)
abonada (0, 1)
pdf
timestamp
*/

require 'vendor/autoload.php'; // Incluir Composer autoload si estás usando Composer
use Dompdf\Dompdf;
use Dompdf\Options;
use Shuchkin\SimpleXLSXGen;
 
class Facturas{
    //Crear remesa (Comprobar en cada cliente si le corresponde una factura) (Crear el pdf)
    static public function crearRemesa($request){
        $conn = $_SESSION['db_connection'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];
        $d = $request['dia'];
        $m = $request['mes'];
        $y = $request['year'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear una remesa'
            ];
        }

        //obtener todos los socios
        $sql = "SELECT * FROM usuarios WHERE estado = 'INSCRITO'";
        $socios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

        //obtener la fecha de hoy
        $fechaHoy = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));

        $facturar = [];

        foreach ($socios as $socio) {
            $periodoPago = intval($socio['periodo_pago']);

            // Verificar si el socio tiene alguna factura sin conformar
            $sql = "SELECT COUNT(*) as count FROM $tabla WHERE id_socio = {$socio['id']} AND conformada = 0";
            $result = $conn->query($sql)->fetch_assoc();
            if ($result['count'] > 0) {
                continue; // Saltar este socio si tiene facturas sin conformar
            }

            $comprobarFechaFactura = self::comprobarFechaFactura($socio, $periodoPago, $d, $m, $y);

            if ($comprobarFechaFactura) {
                $facturar[] = $socio;
            }
        }

        //crear facturas
        $facturas = [];
        foreach ($facturar as $socio) {
            //obtener concepto e importe por el periodo de pago
            $periodoPago = intval($socio['periodo_pago']);

            $arrayPeriodos = [
                3 => [
                    'concepto' => 'trimestral',
                    'importe' => 36
                ],
                6 => [
                    'concepto' => 'semestral',
                    'importe' => 72
                ],
                12 => [
                    'concepto' => 'anual',
                    'importe' => 120
                ]
            ];

            $concepto = $arrayPeriodos[$periodoPago]['concepto'];

            //si concepto es trimestral indicar en que trimestre y que año Ejemplo: Cuota asociado 2º Trimestre 2023, si es semestral indicar semestre y año Ejemplo:   Cuota asociado 2º Semestre 2023, si es anual indicar año Ejemplo: Cuota asociado 2023
            $yActual = $y;
            $mActual = $m;

            if ($concepto == 'trimestral') {
                if ($mActual >= 1 && $mActual <= 3) {
                    $concepto .= ' ' . '1er Trimestre ' . $yActual;
                } elseif ($mActual >= 4 && $mActual <= 6) {
                    $concepto .= ' ' . '2º Trimestre ' . $yActual;
                } elseif ($mActual >= 7 && $mActual <= 9) {
                    $concepto .= ' ' . '3er Trimestre ' . $yActual;
                } elseif ($mActual >= 10 && $mActual <= 12) {
                    $concepto .= ' ' . '4º Trimestre ' . $yActual;
                }
            } elseif ($concepto == 'semestral') {
                if ($mActual >= 1 && $mActual <= 6) {
                    $concepto .= ' ' . '1er Semestre ' . $yActual;
                } elseif ($mActual >= 7 && $mActual <= 12) {
                    $concepto .= ' ' . '2º Semestre ' . $yActual;
                }
            } elseif ($concepto == 'anual') {
                $concepto .= ' ' . $yActual;
            }

            $importe = $arrayPeriodos[$periodoPago]['importe'];

            $data = [
                'id_socio' => $socio['id'],
                'n_socio' => $socio['n_socio'],
                'nombre_comercial' => $socio['nombre_comercial'],
                'razon_social' => $socio['razon_social'],
                'cif' => $socio['cif'],
                'forma_pago' => $socio['forma_pago'],
                'iban' => $socio['iban'],
                'importe' => $importe,
                'concepto' => "Cuota Socio " . $concepto,
                'fecha_factura' => $fechaHoy,
                'conformada' => 0,
                'abonada' => 0,
                'pdf' => ""
            ];

            $facturas[] = $data;
        }

        //crear facturas en la base de datos
        if (!empty($facturas)) {
            $sql = "INSERT INTO $tabla (id_socio, n_socio, nombre_comercial, razon_social, cif, forma_pago, iban, importe, concepto, fecha_factura, conformada, abonada, pdf) VALUES ";

            $values = [];
            foreach ($facturas as $factura) {
                $values[] = "('" . implode("', '", array_map([$conn, 'real_escape_string'], $factura)) . "')";
            }

            $sql .= implode(', ', $values);

            $conn->query($sql);
        } else {
            return [
                'status' => 'error',
                'message' => 'No se han creado facturas porque no hay socios que cumplan los requisitos'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Remesa creada correctamente',
            'data' => $facturas
        ];
    }

    static private function crearPdf($factura, $socio, $urlPdf = null) {
        $n_factura = $factura['n_factura'];
        $fechaFactura = $factura['fecha_factura'];
        $nombreComercial = $factura['nombre_comercial'];
        $razonSocial = $factura['razon_social'];
        $direccion = $socio['direccion'] . ' ' . $socio['numero_direccion'];
        $poblacion = $socio['poblacion'] . ' ' . $socio['codigo_postal'];
        $cif = $factura['cif'];
        $concepto = $factura['concepto'];
        $importe = $factura['importe'];
        $formaPago = $factura['forma_pago'];

        $formaPagoInfo = "";
        if ($formaPago === 'CARGO EN CUENTA') {
            $formaPagoInfo = $socio['iban'];
        }

        if ($formaPago === 'TRANSFERENCIA') {
            $formaPagoInfo = 'ES8000812276450001107514';
        }

        if ($formaPago === 'EFECTIVO') {
            $formaPagoInfo = $socio['email'];
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isCssFloatEnabled', true); // Habilitar CSS3

        $dompdf = new Dompdf($options);

        $imagen = "https://pruebasbot.wairbot.es/acitui/logo.jpg";
        $imagenBase64 = base64_encode(file_get_contents($imagen));

        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="es">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Factura</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 11pt;
                        padding: 0;
                        margin: 0;
                    }
                
                    .container {
                        width: 180mm;
                        padding: 4mm;
                        box-sizing: border-box;
                    }
                
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                
                    .logoContainer img {
                        width: 150px;
                        height: auto;
                    }
                
                    .infoFacturaContainer {
                        text-align: right;
                        margin: 20px 0;
                    }
                
                    .infoFactura {
                        display: inline-block;
                        border: 2px solid #000;
                        border-radius: 10px;
                        padding: 10px;
                        text-align: left;
                        width: 200px;
                    }
                
                    .infoFacturaImplicadosContainer {
                        margin: 20px 0;
                        width: 180mm;
                        font-size: 0;
                    }
                
                    .infoFacturaImplicado {
                        display: inline-block;
                        border: 2px solid #000;
                        border-radius: 10px;
                        padding: 10px;
                        text-align: left;
                        vertical-align: top;
                        width: 81mm;
                        margin: 5px 0;
                        font-size: 16px;
                    }
                
                    .separadora {
                        width: 5mm;
                        height: 1px;
                        background-color: #ffffff;
                        display: inline-block;
                    }
                
                    .desglose {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }
                
                    .desglose th,
                    .desglose td {
                        border: 2px solid #000;
                        padding: 5px;
                    }
                
                    .desglose th {
                        background-color: rgb(218, 218, 218);
                    }
                
                    .desglose tbody tr td {
                        vertical-align: middle;
                        padding: 40px 10px;
                    }
                
                    .totales {
                        margin-top: 20px;
                        border: 2px solid #000;
                        padding: 15px;
                        text-align: left;
                    }
                
                    .total {
                        margin-bottom: 10px;
                    }
                
                    .pagos {
                        margin-top: 20px;
                    }
                
                    .footer {
                        margin-top: 75px;
                    }
                
                    .separador {
                        width: 100%;
                        height: 1px;
                        background-color: #000;
                        margin-bottom: 5px;
                    }
                
                    .proteccionDeDatos {
                        font-size: 8pt;
                        text-align: justify;
                    }
                </style>
            </head>
                
            <body>
                <div class="container">
                    <div class="header">
                        <div class="logoContainer">
                            <img src="data:image/jpeg;base64,$imagenBase64" alt="Logo">
                        </div>
                    </div>
                
                    <div class="infoFacturaContainer">
                        <div class="infoFactura">
                            <div><strong>Factura nº:</strong>&nbsp; $n_factura</div>
                            <div><strong>Fecha:</strong>&nbsp; $fechaFactura</div>
                        </div>
                    </div>
                
                    <div class="infoFacturaImplicadosContainer">
                        <div class="infoFacturaImplicado">
                            <div><strong>ASOCIACIÓN DE COMERCIANTES E INDUSTRIAIS DE TUI</strong></div>
                            <div>C/Colón 2, 1º</div>
                            <div>36.700 TUI</div>
                            <div>G-36.272.003</div>
                        </div>
                        <div class="separadora"></div>
                        <div class="infoFacturaImplicado">
                            <div><strong>$nombreComercial</strong></div>
                            <div>$razonSocial</div>
                            <div>$direccion</div>
                            <div>$poblacion</div>
                            <div>$cif</div>
                        </div>
                    </div>
                
                    <table class="desglose">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>$concepto</td>
                                <td>1</td>
                                <td>$importe.00 €</td>
                                <td>$importe.00 €</td>
                            </tr>
                        </tbody>
                    </table>
                
                    <div class="totales">
                        <div class="total"><strong>BASE: $importe.00 €</strong></div>
                        <div class="total"><strong>TIPO I.V.A.: Exento</strong></div>
                        <div class="total"><strong>TOTAL: $importe.00 €</strong></div>
                    </div>
                
                    <div class="pagos">
                        <div><strong>Forma de pago: $formaPago</strong></div>
                        <div>$formaPagoInfo</div>
                    </div>
                
                    <div class="footer">
                        <div class="separador"></div>
                        <div class="proteccionDeDatos">
                            Conforme co previsto na normativa vixente de Protección de Datos de Carácter Persoal, os datos recadados
                            na presente factura serán incluídos nos nosos ficheiros, titularidade de ACITUI, CIF.: G-36270003, coa
                            finalidade da facturación dos servicios, a xestión contable, fiscal e administrativa e das relacións
                            mercantís cos nosos socios, clientes e provedores. Os seus datos serán conservados durante o prazo
                            estritamente necesario para cumprir cos preceptos mencionados con anterioridade ou durante os anos
                            necesarios para cumprir coas obrigacións legais. Os datos non se cederán a terceiros salvo nos casos en
                            que exista unha obrigación legal. Poderá exercer os dereitos de acceso, rectificación, portabilidade e
                            supresión dos seus datos e os de limitación e oposición ó seu tratamento dirixíndose por escrito a
                            ACITUI no enderezo rúa Colón nº 2, Edificio Área Panorámica, 36700, Tui (Pontevedra) ou enviando unha
                            mensaxe de correo electrónico a acitui@acitui.es, achegando en ambos os casos copia de seu DNI.
                        </div>
                    </div>
                </div>
            </body>
                
            </html>
        HTML;

        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        
        if ($urlPdf == null) {
            //guardar pdf en la carpeta pdfs
            $valorUnico = uniqid();
            $valorRandom = rand(1000, 9999);
            $valorTiempo = time();
        
            $pdfDir = dirname(__DIR__) . "/public";
            $pdfPath = "$pdfDir/factura_$valorUnico$valorRandom$valorTiempo.pdf";
        } else {
            $pdfPath = $urlPdf;
        }
        
        // Verificar si el directorio existe, si no, crearlo
        if (!file_exists($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }
        
        file_put_contents($pdfPath, $output);
        
        return $pdfPath;
    }

    static private function comprobarFechaFactura($socio, $meses, $d, $m, $y) {
        if ($socio['fecha_ultima_factura'] == null || $socio['fecha_ultima_factura'] == '') {
            return true;
        } else {
            $fechaUltimaFactura = new DateTime($socio['fecha_ultima_factura']);
            $fechaHoy = new DateTime("$y-$m-$d");
    
            $diff = $fechaUltimaFactura->diff($fechaHoy);
    
            // Convertir la diferencia en meses totales
            $mesesTotales = $diff->y * 12 + $diff->m;
    
            if ($mesesTotales >= $meses) {
                return true;
            } else {
                return false;
            }
        }
    }

    static public function listarRemesas($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];
    
        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver las remesas'
            ];
        }
        
        //paginación
        $rowsPerPage = $_SESSION['rows_per_page'];
        $inicio = ($nPagina - 1) * $rowsPerPage;
        
        // Inicializar la variable de fila
        $conn->query("SET @rownum := 0;");
        
        // Obtener el id, fecha_factura, conformada, abonada agrupados por fecha_factura
        $sql = "
            SELECT 
                @rownum := @rownum + 1 AS id,
                fecha_factura, 
                COUNT(*) AS numero_facturas, 
                SUM(conformada) AS conformadas, 
                SUM(abonada) AS abonadas 
            FROM $tabla 
            GROUP BY fecha_factura 
            ORDER BY fecha_factura DESC 
            LIMIT $inicio, $rowsPerPage
        ";
        $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        
        // Obtener el total de remesas
        $sqlCount = "
            SELECT COUNT(DISTINCT fecha_factura) AS totalRemesas 
            FROM $tabla
        ";
        $totalRemesas = $conn->query($sqlCount)->fetch_assoc()['totalRemesas'];
        $paginasTotales = ceil($totalRemesas / $rowsPerPage);
        
        return [
            'status' => 'success',
            'message' => 'Remesas obtenidas correctamente',
            'data' => [
                'paginaActual' => $nPagina,
                'paginasTotales' => $paginasTotales,
                'data' => $result
            ]
        ];
    }

    static public function obtenerFacturasDeRemesa($request){
        $conn = $_SESSION['db_connection'];
        $fechaFactura = $request['fecha_factura'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];
    
        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver las facturas de una remesa'
            ];
        }
    
        //obtener las facturas de la remesa
        $sql = "
            SELECT * 
            FROM $tabla 
            WHERE fecha_factura = '$fechaFactura'
        ";
        $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
        return [
            'status' => 'success',
            'message' => 'Facturas de la remesa obtenidas correctamente',
            'data' => $result
        ];
    }

    //enviar facturas de remesa por mail
    static public function procesarRemesas($request){
        $conn = $_SESSION['db_connection'];
        $fechaFactura = $request['fecha_factura'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];
        $tipoProceso = $request['tipo_proceso']; //enviar_mail, xml, excel, conformar, eliminar
    
        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para enviar las facturas de una remesa por mail'
            ];
        }
    
        //obtener las facturas de la remesa
        $sql = "
            SELECT * 
            FROM $tabla 
            WHERE fecha_factura = '$fechaFactura'
        ";
        $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    
        $facturas = [];
        foreach ($result as $factura) {
            $socio = Usuarios::obtenerPorId(['id' => $factura['id_socio']]);
            $socio = $socio['data'];
    
            $factura['socio'] = $socio;
            $facturas[] = $factura;
        }

        $archivo = "";

        if($tipoProceso === 'enviar_mail'){
            $archivo = self::enviarMail($facturas);
        }

        if($tipoProceso === 'xml'){
            $archivo = self::crearXml($facturas);
        }

        if($tipoProceso === 'excel'){
            $archivo = self::crearExcel($facturas);
        }

        if($tipoProceso === 'eliminar'){
            $dataEliminar = [
                'fecha_factura' => $fechaFactura
            ];

            $resultEliminar = self::eliminarRemesa($dataEliminar);
            if($resultEliminar['status'] === 'error'){
                return $resultEliminar;
            }
        }

        if($tipoProceso === 'conformar'){
            //si hay facturas no conformadas con fecha anterior a las que se van a conformar en la Tabla facturas echar
            $sql = "
                SELECT * 
                FROM $tabla 
                WHERE fecha_factura < '$fechaFactura' AND conformada = 0
            ";
            $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

            if(count($result) > 0){
                return [
                    'status' => 'error',
                    'message' => 'Hay facturas no conformadas con fecha anterior a las que se van a conformar'
                ];
            }

            foreach ($facturas as $factura) {
                $data = $factura;
                $data['conformada'] = 1;
                
                self::editarFactura($data);            
            }
        }

        return [
            'status' => 'success',
            'message' => 'Facturas de la remesa procesadas correctamente',
            'data' => $archivo
        ];
    }

    static private function enviarMail($data) {
        $enviados = [];
        foreach ($data as $factura) {
            //si email no existe o está vacío echar
            if (!isset($factura['socio']['email']) || $factura['socio']['email'] == '') {
                continue;
            }

            $socio = $factura['socio'];
            $email = $socio['email'];
            $pdf = "https://pruebasbot.wairbot.es/acitui/api/public/" . $factura['pdf'];

            $urlAcceso = "https://pruebasbot.wairbot.es/acitui/?email=" . $email . "&password=" . $socio['password'];
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($urlAcceso);
    
            $para = $email;
            //eliminar espacios en blanco
            $para = str_replace(' ', '', $para);

            $asunto = "Factura de la remesa";

            //body alineado a la izquierda con texto diciendo que se adjunta la factua y un div centrado para el qr con el acceso
            $mensaje = "<html><body style='text-align: left;'>";
            $mensaje .= "<h1>Factura de la remesa</h1>";
            $mensaje .= "<p>Se adjunta la factura correspondiente a la cuota de Asociado</p>";
            $mensaje .= "<p>Para acceder a la aplicación escanea el siguiente QR</p>";
            $mensaje .= "<div style='text-align: center;'><img src='" . $qrCodeUrl . "' alt='Acceso a la aplicación'/></div>";
            $mensaje .= "</body></html>";
            
            $urlArchivoAdjunto = $pdf;

            $servidorSMTP = "mail.acitui.es"; // Cambia por tu servidor SMTP
            $puerto = 25; // Usualmente 587 o 465
            $usuario = "no-responder@acitui.es";
            $contrasena = "N-Rspndr*2024";
            $de = "no-responder@acitui.es";

            self::enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, $asunto, $mensaje, $urlArchivoAdjunto);

            $enviados[] = [
                'email' => $email,
                'pdf' => $pdf
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Emails enviados correctamente',
            'data' => $enviados
        ];
    }

    static public function enviarFacturaIndividual($request){
        $para = $request['email'];
        $urlArchivoAdjunto = $request['archivo'];

        $servidorSMTP = "mail.acitui.es"; // Cambia por tu servidor SMTP
        $puerto = 25; // Usualmente 587 o 465
        $usuario = "no-responder@acitui.es";
        $contrasena = "N-Rspndr*2024";
        $de = "no-responder@acitui.es";

        //body alineado a la izquierda con texto saludando, diciendo que se adjunta la factura agradeciendo y despedida
        $mensaje = "<html><body style='text-align: left;'>";
        $mensaje .= "<h1>Cuota Socio Trimestral</h1>";
        $mensaje .= "<p>Estimado Socio,</p>";
        $mensaje .= "<p>Se adjunta la factura correspondiente a la cuota de Asociado</p>";
        $mensaje .= "<p>Gracias por su atención</p>";
        $mensaje .= "<p>Un cordial saludo</p>";
        $mensaje .= "</body></html>";

        $envio = self::enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, "Cuota Socio Trimestral", $mensaje, $urlArchivoAdjunto);

        if($envio){
            return [
                'status' => 'success',
                'message' => 'Email enviado correctamente'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Error al enviar el email'
            ];
        }
    }

    static private function enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, $asunto, $mensaje, $urlArchivoAdjunto){
        $archivoAdjuntoVacio = true;
        $archivoTemporal = '';

        // Verificar si la URL del archivo adjunto no está vacía
        if (!empty($urlArchivoAdjunto)) {
            // Verificar si la URL es accesible
            $headers = @get_headers($urlArchivoAdjunto);
            if (!$headers || strpos($headers[0], '200') === false) {
                //echo "Error: La URL no es accesible o el archivo no existe: $urlArchivoAdjunto\n";
                //error en json:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'La URL no es accesible o el archivo no existe: ' . $urlArchivoAdjunto
                ]);
                return false;
            }

            // Descargar el archivo desde la URL remota usando curl
            $archivoTemporal = tempnam(sys_get_temp_dir(), 'adjunto_');
            $ch = curl_init($urlArchivoAdjunto);
            $fp = fopen($archivoTemporal, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $success = curl_exec($ch);
            if (!$success) {
                //echo "Error descargando el archivo adjunto: " . curl_error($ch) . "\n";
                //error en json:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error descargando el archivo adjunto: ' . curl_error($ch)
                ]);
                curl_close($ch);
                fclose($fp);
                unlink($archivoTemporal);
                return false;
            }
            curl_close($ch);
            fclose($fp);

            // Verificar si el archivo adjunto está vacío
            if (file_exists($archivoTemporal)) {
                $archivoAdjunto = file_get_contents($archivoTemporal);
                $archivoAdjuntoCodificado = base64_encode($archivoAdjunto);
                $nombreArchivo = basename($urlArchivoAdjunto);
                $archivoAdjuntoVacio = empty($archivoAdjunto);
            } else {
                //echo "El archivo adjunto no existe: $archivoTemporal\n";
                //error en json:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'El archivo adjunto no existe: ' . $archivoTemporal
                ]);
                $archivoAdjuntoVacio = true;
            }
        }

        // Conectar al servidor SMTP
        $conexionSMTP = fsockopen($servidorSMTP, $puerto, $errno, $errstr, 10);
        if (!$conexionSMTP) {
            //echo "Error conectando al servidor SMTP: $errstr ($errno)\n";
            //error en json:
            echo json_encode([
                'status' => 'error',
                'message' => 'Error conectando al servidor SMTP: ' . $errstr . ' (' . $errno . ')'
            ]);
            if (!empty($archivoTemporal)) {
                unlink($archivoTemporal);
            }
            return false;
        }

        // Leer la respuesta inicial del servidor
        fgets($conexionSMTP, 515);

        // Enviar comandos HELO (para identificar al cliente)
        fputs($conexionSMTP, "EHLO localhost\r\n");
        fgets($conexionSMTP, 515);

        // Iniciar la autenticación
        fputs($conexionSMTP, "AUTH LOGIN\r\n");
        fgets($conexionSMTP, 515);

        // Enviar usuario y contraseña codificados en base64
        fputs($conexionSMTP, base64_encode($usuario) . "\r\n");
        fgets($conexionSMTP, 515);
        fputs($conexionSMTP, base64_encode($contrasena) . "\r\n");
        fgets($conexionSMTP, 515);

        // Especificar la dirección del remitente
        fputs($conexionSMTP, "MAIL FROM: <$de>\r\n");
        fgets($conexionSMTP, 515);

        // Especificar la dirección del destinatario
        fputs($conexionSMTP, "RCPT TO: <$para>\r\n");
        fgets($conexionSMTP, 515);

        // Enviar comando DATA para indicar el inicio del contenido del correo
        fputs($conexionSMTP, "DATA\r\n");
        fgets($conexionSMTP, 515);

        // Crear un límite único para separar las partes del correo
        $limite = md5(time());

        // Escribir los encabezados y el cuerpo del correo
        $cabeceras = "From: $de\r\n";
        $cabeceras .= "To: $para\r\n";
        $cabeceras .= "Subject: $asunto\r\n";
        $cabeceras .= "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-Type: multipart/mixed; boundary=\"$limite\"\r\n\r\n";

        // Cuerpo del mensaje en formato HTML
        $cuerpo = "--$limite\r\n";
        $cuerpo .= "Content-Type: text/html; charset=UTF-8\r\n";
        $cuerpo .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $cuerpo .= $mensaje . "\r\n\r\n";

        // Adjuntar el archivo PDF si no está vacío
        if (!$archivoAdjuntoVacio) {
            $cuerpo .= "--$limite\r\n";
            $cuerpo .= "Content-Type: application/pdf; name=\"$nombreArchivo\"\r\n";
            $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
            $cuerpo .= "Content-Disposition: attachment; filename=\"$nombreArchivo\"\r\n\r\n";
            $cuerpo .= $archivoAdjuntoCodificado . "\r\n\r\n";
        }

        $cuerpo .= "--$limite--\r\n";

        // Enviar el mensaje completo
        //fputs($conexionSMTP, $cabeceras . $cuerpo . "\r\n.\r\n");
        //fgets($conexionSMTP, 515);

        // Mostrar un mensaje de éxito indicando que el proceso se completó hasta antes de enviar el correo
        //echo $para;

        // Terminar la conexión
        fputs($conexionSMTP, "QUIT\r\n");
        fgets($conexionSMTP, 515);

        fclose($conexionSMTP);

        // Eliminar el archivo temporal si existe
        if (!empty($archivoTemporal)) {
            unlink($archivoTemporal);
        }

        //echo "Correo enviado con éxito\n";
        return true;
    }

    static private function crearXml($data) {
        //eliminar de data los que no están conformados y los que su metodo de pago no es CARO EN CUENTA
        $data = array_filter($data, function($factura){
            return $factura['forma_pago'] == 'CARGO EN CUENTA';
        });

        $xmlFacturas = "";
        foreach ($data as $factura) {
            $EndToEndId = uniqid() . rand(1000, 9999) . time();
            $MndtId = uniqid() . rand(1000, 9999) . time();
            $DtOfSgntr = date('Y-m-d');
            $Nm = $factura['socio']['razon_social'];
            $AdrLineDireccion = $factura['socio']['direccion'] . ' ' . $factura['socio']['numero_direccion'];
            $AdrLinePoblacion = $factura['socio']['poblacion'] . ' ' . $factura['socio']['codigo_postal'];
            $IBAN = $factura['socio']['iban'];
            $IBAN = str_replace(' ', '', $IBAN);
            $Ustrd = $factura['concepto'];
            $InstdAmt = $factura['importe'] . '.00';

            $xmlFacturas .= <<<XML
            <DrctDbtTxInf>
                <PmtId>
                    <EndToEndId> $EndToEndId</EndToEndId>
                </PmtId>
                <InstdAmt Ccy="EUR">$InstdAmt</InstdAmt>
                <DrctDbtTx>
                    <MndtRltdInf>
                        <MndtId>$MndtId</MndtId>
                        <DtOfSgntr>$DtOfSgntr</DtOfSgntr>
                    </MndtRltdInf>
                </DrctDbtTx>
                <DbtrAgt>
                    <FinInstnId />
                </DbtrAgt>
                <Dbtr>
                    <Nm>$Nm</Nm>
                    <PstlAdr>
                        <Ctry>ES</Ctry>
                        <AdrLine>$AdrLineDireccion</AdrLine>
                        <AdrLine>$AdrLinePoblacion</AdrLine>
                    </PstlAdr>
                </Dbtr>
                <DbtrAcct>
                    <Id>
                        <IBAN>$IBAN</IBAN>
                    </Id>
                </DbtrAcct>
                <RmtInf>
                    <Ustrd>$Ustrd</Ustrd>
                </RmtInf>
            </DrctDbtTxInf>
            XML;
        }

        $importeTotal = 0;
        foreach ($data as $factura) {
            $importe = intval($factura['importe']);
            $importeTotal += $importe;
        }

        //identificadores únicos	
        $MsgId = uniqid() . rand(1000, 9999) . time();
        $CreDtTm = date('Y-m-d') . 'T' . date('H:i:s');
        $NbOfTxs = count($data);
        $CtrlSum = $importeTotal . '.00';
        $Nm = 'ACITUI';
        $Id = 'ES36000G36272003';
        $PmtInfId = uniqid() . rand(1000, 9999) . time();
        $ReqdColltnDt = date('Y-m-d');
        $Cdtr = 'ASOCIACION DE COMERCIANTES DE TUI';
        $Ctry = 'ES';
        $AdrLineDireccion = '. 36700 . ';
        $AdrLinePoblacion = ' ';
        $IBAN = 'ES8000812276450001107514';
        $BIC = 'BSABESBBXXX';
        $CdtrSchmeId = 'ES36001G36272003';


        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02">
            <CstmrDrctDbtInitn>
                <GrpHdr>
                    <MsgId>$MsgId</MsgId>
                    <CreDtTm>$CreDtTm</CreDtTm>
                    <NbOfTxs>$NbOfTxs</NbOfTxs>
                    <CtrlSum>$CtrlSum</CtrlSum>
                    <InitgPty>
                        <Nm>$Nm</Nm>
                        <Id>
                            <PrvtId>
                                <Othr>
                                    <Id>$Id</Id>
                                </Othr>
                            </PrvtId>
                        </Id>
                    </InitgPty>
                </GrpHdr>
                <PmtInf>
                    <PmtInfId>$PmtInfId</PmtInfId>
                    <PmtMtd>DD</PmtMtd>
                    <BtchBookg>false</BtchBookg>
                    <PmtTpInf>
                        <SvcLvl>
                            <Cd>SEPA</Cd>
                        </SvcLvl>
                        <LclInstrm>
                            <Cd>CORE</Cd>
                        </LclInstrm>
                        <SeqTp>RCUR</SeqTp>
                    </PmtTpInf>
                    <ReqdColltnDt>$ReqdColltnDt</ReqdColltnDt>
                    <Cdtr>
                        <Nm>$Cdtr</Nm>
                        <PstlAdr>
                            <Ctry>$Ctry</Ctry>
                            <AdrLine>$AdrLineDireccion</AdrLine>
                            <AdrLine>$AdrLinePoblacion</AdrLine>
                        </PstlAdr>
                    </Cdtr>
                    <CdtrAcct>
                        <Id>
                            <IBAN>$IBAN</IBAN>
                        </Id>
                    </CdtrAcct>
                    <CdtrAgt>
                        <FinInstnId>
                            <BIC>$BIC</BIC>
                        </FinInstnId>
                    </CdtrAgt>
                    <CdtrSchmeId>
                        <Id>
                            <PrvtId>
                                <Othr>
                                    <Id>$CdtrSchmeId</Id>
                                    <SchmeNm>
                                        <Prtry>SEPA</Prtry>
                                    </SchmeNm>
                                </Othr>
                            </PrvtId>
                        </Id>
                    </CdtrSchmeId>
                    $xmlFacturas
                </PmtInf>
            </CstmrDrctDbtInitn>
        </Document>
        XML;

        //eliminar archivos antiguos
        $files = glob('../public/facturas_*.xml');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        //nombre del archivo
        $nombreArchivo = "remesa_facturas.xml";
        $path = dirname(__DIR__) . "/public/$nombreArchivo";

        //guardar el archivo en la carpeta public
        file_put_contents($path, $xml);

        return $nombreArchivo;        
    }

    static private function crearExcel($data) {
        // Obtener los datos para el Excel, la primera fila son los nombres de las columnas y el resto los datos
        $dataExcel = [
            [
                'ID', 
                'ID SOCIO', 
                'N SOCIO', 
                'NOMBRE COMERCIAL', 
                'RAZON SOCIAL', 
                'CIF', 
                'FORMA PAGO', 
                'IBAN', 
                'IMPORTE', 
                'CONCEPTO', 
                'N FACTURA', 
                'FECHA FACTURA', 
                'CONFORMADA', 
                'ABONADA', 
                'PDF'
            ]
        ];

        foreach ($data as $factura) {
            // Si conformada o abonada es 1, cambiar a Sí, si no a No
            $factura['conformada'] = $factura['conformada'] == 1 ? 'Sí' : 'No';
            $factura['abonada'] = $factura['abonada'] == 1 ? 'Sí' : 'No';

            $dataExcel[] = [
                $factura['id'],
                $factura['id_socio'],
                $factura['n_socio'],
                $factura['nombre_comercial'],
                $factura['razon_social'],
                $factura['cif'],
                $factura['forma_pago'],
                $factura['iban'],
                $factura['importe'],
                $factura['concepto'],
                $factura['n_factura'],
                $factura['fecha_factura'],
                $factura['conformada'],
                $factura['abonada'],
                $factura['pdf']
            ];
        }

        // Eliminar archivos antiguos
        $nombreArchivo = "remesa_facturas.xlsx";
        $path = dirname(__DIR__) . "/public/$nombreArchivo";
        $files = glob($path);
        foreach ($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    throw new Exception("No se pudo eliminar el archivo antiguo: $file");
                }
            }
        }

        // Convertir todos los datos a UTF-8
        array_walk_recursive($dataExcel, function(&$item) {
            $item = mb_convert_encoding($item, 'UTF-8', 'auto');
        });

        // Crear el archivo XLSX
        try {
            $xlsx = SimpleXLSXGen::fromArray($dataExcel);
        } catch (Exception $e) {
            throw new Exception("Error al crear el archivo XLSX: " . $e->getMessage());
        }

        // Guardar el archivo en la carpeta public
        try {
            $xlsx->saveAs($path);
        } catch (Exception $e) {
            throw new Exception("Error al guardar el archivo XLSX: " . $e->getMessage());
        }

        return $nombreArchivo;
    }


    #################################### FACTURAS ####################################

    static public function listarFacturas($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver las facturas'
            ];
        }

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, [], $datosRelacionados, $search);

        return [
            'status' => 'success',
            'message' => 'Facturas obtenidas correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function obtenerFacturaPorId($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver una factura'
            ];
        }

        $filtro = [];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Factura obtenida correctamente',
            'data' => $result,
        ];
    }

    static public function editarFactura($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $data = $request['data'] ?? $request;
        $tabla = 'facturas';
        $userRol = $_SESSION['user_rol'];
    
        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para editar una factura'
            ];
        }
    
        //obtener el id del socio por su n_socio
        $n_socio = $data['n_socio'];
        $sql = "SELECT id FROM usuarios WHERE n_socio = $n_socio";
        $id_socio = $conn->query($sql)->fetch_assoc()['id'];
    
        $data['id_socio'] = $id_socio;
    
        //si la factura está conformada no se puede editar salvo el campo abonada
        $sql = "SELECT conformada FROM $tabla WHERE id = $id";
        $conformada = $conn->query($sql)->fetch_assoc()['conformada'];
    
        if($conformada == 1){
            if($data['abonada'] !== null && $data['abonada'] !== '' && $data['abonada'] !== 0 && $data['abonada'] !== "0"){
                //obtener la fecha de la factura por su id
                $sql = "SELECT fecha_factura FROM $tabla WHERE id = $id";
                $fecha_factura = $conn->query($sql)->fetch_assoc()['fecha_factura'];
    
                $abonada = $data['abonada'];
                $data = [
                    'id_socio' => $id_socio,
                    'n_socio' => $n_socio,
                    'fecha_factura' => $fecha_factura,
                    'abonada' => $abonada
                ];
            }else{
                return [
                    'status' => 'error',
                    'message' => 'No puedes editar una factura conformada'
                ];
            }
        }
    
        //comprobar que la fecha de la factura es no es anterior a la fecha de la última factura emitida y conformada
        $sql = "SELECT fecha_factura FROM $tabla WHERE conformada = 1 ORDER BY fecha_factura DESC LIMIT 1";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $fechaUltimaFactura = $result->fetch_assoc()['fecha_factura'];
            $fechaUltimaFactura = new DateTime($fechaUltimaFactura);
        } else {
            $fechaUltimaFactura = null;
        }
    
        $fechaFactura = $data['fecha_factura'];
        $fechaFactura = new DateTime($fechaFactura);
    
        //si fechaUltimaFactura es null, no hay facturas emitidas
        if($fechaUltimaFactura == null){
            $fechaUltimaFactura = new DateTime('1900-01-01');
        }
    
        if($fechaFactura < $fechaUltimaFactura){
            return [
                'status' => 'error',
                'message' => 'La fecha de la factura no puede ser anterior a la fecha de la última factura emitida'
            ];
        }
    
        //insertar ['pdf'] en data si no existe
        if (!array_key_exists('pdf', $data)) {
            $data['pdf'] = '';
        }
    
        //comprobar que la factura esté conformada antes de crear un numero de factura
        if (isset($data['conformada']) && $data['conformada'] !== 0) {
            $data['conformada'] = 1;
    
            // Verificar si ya existe un n_factura y comienza con 'FAC'
            if (isset($data['n_factura']) && strpos($data['n_factura'], 'FAC') === 0) {
                $n_factura = $data['n_factura'];
            } else {
                //obtener la ultima factura emitida por su timestamp y que esté conformada
                $sql = "SELECT n_factura FROM $tabla ORDER BY n_factura DESC LIMIT 1";
                $result = $conn->query($sql);
                $ultimo_n_factura = $result->fetch_assoc();
    
                if ($ultimo_n_factura && $ultimo_n_factura['n_factura'] !== null && $ultimo_n_factura['n_factura'] !== '') {
                    $ultimo_n_factura = $ultimo_n_factura['n_factura'];
                    $ultimo_n_factura = intval(substr($ultimo_n_factura, 6));
                    $ultimo_n_factura = ltrim($ultimo_n_factura, '0');
                } else {
                    $ultimo_n_factura = 0;
                }
    
                $y = $fechaFactura->format('Y');
                $year = substr($y, 2);
                $prefix_n_factura = 'FAC' . $year . '-';
    
                $n_factura_sin_prefijo = $ultimo_n_factura + 1;
                $n_factura_sin_prefijo = str_pad($n_factura_sin_prefijo, 4, '0', STR_PAD_LEFT);
                $n_factura = $prefix_n_factura . $n_factura_sin_prefijo;
            }
    
            $data['n_factura'] = $n_factura;
    
            //actualizar el documento pdf
            $pdf = $data['pdf'];
    
            //obtener socio id_socio de la factura
            $sql = "SELECT id_socio FROM $tabla WHERE id = $id";
            $id_socio = $conn->query($sql)->fetch_assoc()['id_socio'];
    
            //obtener socio
            $socio = Usuarios::obtenerPorId(['id' => $id_socio]);
    
            //crear pdf
            $pdf = self::crearPdf($data, $socio['data'], $pdf);
    
            //obtener solo el nombre del pdf sin la ruta
            $pdf = basename($pdf);
    
            //actualizar la factura con el pdf
            $data['pdf'] = $pdf;
    
            //convertir la fecha de la factura a string en formato Y-m-d
            $fechaFactura = $fechaFactura->format('Y-m-d');
    
            $sql = "UPDATE usuarios SET fecha_ultima_factura = '$fechaFactura' WHERE id = $id_socio";
            $conn->query($sql);
        }else{
            $data['n_factura'] = "";
            $data['pdf'] = "";
        }
    
        //eliminar el campo socio de data
        unset($data['socio']);
    
        $updated = Tools::update($conn, $tabla, $id, $data);
    
        if($updated){
            return [
                'status' => 'success',
                'message' => 'Factura actualizada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar la factura',
                'error' => $updated
            ];
        }
    }

    //crear una factura
    static public function crearFactura($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $tabla = 'facturas';
        $userRol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear una factura'
            ];
        }

        //obtener el id del socio por su n_socio
        $n_socio = $data['n_socio'];
        $sql = "SELECT id FROM usuarios WHERE n_socio = $n_socio";
        $id_socio = $conn->query($sql)->fetch_assoc()['id'];

        $data['id_socio'] = $id_socio;

        //comprobar que la fecha de la factura es no es anterior a la fecha de la última factura emitida
        $sql = "SELECT fecha_factura FROM $tabla ORDER BY fecha_factura DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $fechaUltimaFactura = $result->fetch_assoc()['fecha_factura'];
            $fechaUltimaFactura = new DateTime($fechaUltimaFactura);
        } else {
            $fechaUltimaFactura = null;
        }

        $fechaFactura = $data['fecha_factura'];
        $fechaFactura = new DateTime($fechaFactura);

        //si fechaUltimaFactura es null, no hay facturas emitidas
        if($fechaUltimaFactura == null){
            $fechaUltimaFactura = new DateTime('1900-01-01');
        }

        if($fechaFactura < $fechaUltimaFactura){
            return [
                'status' => 'error',
                'message' => 'La fecha de la factura no puede ser anterior a la fecha de la última factura emitida'
            ];
        }


        //comprobar que la factura esté conformada antes de crear un numero de factura
        if (isset($data['conformada']) && $data['conformada'] !== 0) {
            //obtener la ultima factura emitida por su timestamp y que esté conformada
            $sql = "SELECT n_factura FROM $tabla ORDER BY n_factura DESC LIMIT 1";
            $result = $conn->query($sql);
            $ultimo_n_factura = $result->fetch_assoc();

            if ($ultimo_n_factura) {
                $ultimo_n_factura = $ultimo_n_factura['n_factura'];
                $ultimo_n_factura = intval(substr($ultimo_n_factura, 7));
            } else {
                $ultimo_n_factura = 0;
            }

            $y = $fechaFactura->format('Y');
            $year = substr($y, 2);
            $prefix_n_factura = 'FAC' . $year . '-';

            $n_factura_sin_prefijo = $ultimo_n_factura + 1;
            $n_factura = $prefix_n_factura . $n_factura_sin_prefijo;

            $data['n_factura'] = $n_factura;
        }else{
            $data['n_factura'] = "";
        }

        $check = [];

        $created = Tools::create($conn, $tabla, $data, $check);

        if($created){
            return [
                'status' => 'success',
                'message' => 'Factura creada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al crear la factura',
                'error' => $created
            ];
        }
    }

    //eliminar una factura
    static public function eliminarFactura($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = 'facturas';
        $userRol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar una factura'
            ];
        }

        //si la factura está conformada echar
        $sql = "SELECT conformada FROM $tabla WHERE id = $id";
        $conformada = $conn->query($sql)->fetch_assoc()['conformada'];

        if($conformada == 1){
            return [
                'status' => 'error',
                'message' => 'No puedes eliminar una factura conformada'
            ];
        }

        $check = [];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Factura eliminada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la factura',
                'error' => $deleted
            ];
        }
    }

    //eliminar remesa
    static public function eliminarRemesa($request){
        $conn = $_SESSION['db_connection'];
        $fechaFactura = $request['fecha_factura'];
        $tabla = 'facturas';
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar una remesa'
            ];
        }

        //si alguna factura está conformada echar
        $sql = "SELECT conformada FROM $tabla WHERE fecha_factura = '$fechaFactura'";
        $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

        foreach ($result as $factura) {
            if($factura['conformada'] == 1){
                return [
                    'status' => 'error',
                    'message' => 'No puedes eliminar una remesa con facturas conformadas'
                ];
            }
        }

        //obtener las facturas de la remesa
        $sql = "
            SELECT * 
            FROM $tabla 
            WHERE fecha_factura = '$fechaFactura'
        ";
        $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

        foreach ($result as $factura) {
            $id = $factura['id'];
            $check = [];

            $deleted = Tools::delete($conn, $tabla, $id, $check);
            
            //eliminar la fecha de la última factura al socio
            $id_socio = $factura['id_socio'];

            $sql = "UPDATE usuarios SET fecha_ultima_factura = NULL WHERE id = $id_socio";
            $conn->query($sql);
        }

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Remesa eliminada correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar la remesa',
                'error' => $deleted
            ];
        }
    }
}

/*
Clase: Facturas
Métodos:
listarRemesas
    parametros:
        - nPagina

obtenerFacturasDeRemesa
    parametros:
        - fecha_factura
    
procesarRemesas
    parametros:
        - fecha_factura
        - tipo_proceso
        
listarFacturas
    parametros:
        - nPagina
        - search

obtenerFacturaPorId
    parametros:
        - id

editarFactura
    parametros:
        - id
        - data

confirmarCobroFactura
    parametros:
        - id

crearFactura
    parametros:
        - data

eliminarFactura
    parametros:
        - id
        - id_socio

eliminarRemesa
    parametros:
        - fecha_factura
*/


