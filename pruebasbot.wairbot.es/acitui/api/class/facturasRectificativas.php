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
 
class FacturasRectificativas{
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

        $envio = Tools::enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, "Cuota Socio Trimestral", $mensaje, $urlArchivoAdjunto);

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

    #################################### FACTURAS ####################################

    static public function listarFacturas($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = 'facturas_rectificativas';
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
        $tabla = 'facturas_rectificativas';
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
        $tabla = 'facturas_rectificativas';
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
            if (isset($data['n_factura']) && strpos($data['n_factura'], 'REC') === 0) {
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
                $prefix_n_factura = 'REC' . $year . '-';
    
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
        $tabla = 'facturas_rectificativas';
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
            $prefix_n_factura = 'REC' . $year . '-';

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
        $tabla = 'facturas_rectificativas';
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
}