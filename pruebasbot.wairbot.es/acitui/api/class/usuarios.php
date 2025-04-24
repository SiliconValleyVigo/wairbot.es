<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
periodo_pago (1,3,6)
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
conformada
abonada
pdf
timestamp
*/
 
class Usuarios{
    public static $tabla = 'usuarios';

    static public function obtenerNumeroSocioMasAlto($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los usuarios'
            ];
        }

        //n_socio es un string con un valor numérico
        $sql = "SELECT n_socio FROM $tabla ORDER BY n_socio DESC LIMIT 1";
        $result = $conn->query($sql)->fetch_assoc();

        return [
            'status' => 'success',
            'message' => 'Número de socio obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function preinscripcion($request){
        $tabla = self::$tabla;
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];

        unset($data['representanteNombre']);
        unset($data['representanteCargo']);
        unset($data['RepresentanteAceptacionComunicaciones']);
        unset($data['RepresentanteAceptacionComunicacionesEntreSocios']);
        unset($data['RepresentanteAceptacionPublicidad']);

        $data['rol'] = 'SOCIO';

        //si no existe el campo n_socio se le asigna el valor "" y si no existe el campo estado se le asigna el valor "PREINSCRITO"
        if(!array_key_exists('n_socio', $data)){
            $data['n_socio'] = "";
        }

        if(!array_key_exists('estado', $data) || empty($data['estado'])){
            $data['estado'] = 'PREINSCRITO';
        }

        $check = ['email'];

        //si existe el campo iban se elimina del valor los espacios en blanco
        if(array_key_exists('iban', $data)){
            $data['iban'] = str_replace(' ', '', $data['iban']);
        }

        $created = Tools::create($conn, $tabla, $data, $check);
        $created = ['status' => 'success', 'id' => 1];

        //enviar pdf de solicitud de alta de socio
        self::enviarPdfSolicitudAltaSocio($request['data']);

        if($created){
            if($created['status'] == 'success'){
                $dataId = $created['id'];
                return [
                    'status' => 'success',
                    'message' => 'Preinscripción realizada correctamente',
                    'data' => [
                        'id' => $dataId
                    ]
                ];
            }

            if($created['status'] == 'error'){
                return [
                    'status' => 'error',
                    'message' => 'El email o el número de socio ya están en uso',
                    'error' => $created
                ];
            }
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al realizar la preinscripción',
                'error' => $created
            ];
        }
    }

    static private function enviarPdfSolicitudAltaSocio($data){
        function generarAutorizacionHTML($valor) {
            if (strpos($valor, 'No') !== false) {
                return <<<HTML
                    <div class="Autorizaciones">
                        <span class="autorizoApagado">X</span>&nbsp;&nbsp;Autorizo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="autorizoEncendido">X</span>&nbsp;&nbsp;No Autorizo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                HTML;
            } else {
                return <<<HTML
                    <div class="Autorizaciones">
                        <span class="autorizoEncendido">X</span>&nbsp;&nbsp;Autorizo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="autorizoApagado">X</span>&nbsp;&nbsp;No Autorizo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                HTML;
            }
        }
        
        $data['RepresentanteAceptacionComunicaciones'] = generarAutorizacionHTML($data['RepresentanteAceptacionComunicaciones']);
        $data['RepresentanteAceptacionComunicacionesEntreSocios'] = generarAutorizacionHTML($data['RepresentanteAceptacionComunicacionesEntreSocios']);
        $data['RepresentanteAceptacionPublicidad'] = generarAutorizacionHTML($data['RepresentanteAceptacionPublicidad']);

        $logo = "https://pruebasbot.wairbot.es/acitui/logo.jpg";
    
        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 10,    // Margen izquierdo en milímetros
            'margin_right' => 10,   // Margen derecho en milímetros
            'margin_top' => 5,     // Margen superior en milímetros
            'margin_bottom' => 5,  // Margen inferior en milímetros
            'margin_header' => 0,   // Margen del encabezado en milímetros
            'margin_footer' => 0    // Margen del pie de página en milímetros
        ]);

        //fecha de hoy en formato dd/mm/yyyy
        $fecha = date('d/m/Y');

$html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11pt;
            }

            h1 {
                font-size: 18pt;
                margin: 0;
            }

            h2 {
                font-size: 14pt;
                margin: 0;
            }

            h3 {
                font-size: 9pt;
                margin: 0;
                margin-bottom: 0px;
            }

            .container {
                width: 100%;
                margin: 0 auto;
            }
            .logoContainer {
                text-align: center;
                margin-bottom: 20px;
            }
            .tituloContainer {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid black;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
            .equal-width td {
                width: 33.33%;
            }

            p{
                font-size: 9pt;
                text-align: justify;
                margin-top: 2px;
                margin-bottom: 7px;
            }

            .autorizoEncendido{
                border: 1px solid black;
                color: black;
                padding: 5px;
            }

            .autorizoApagado{
                border: 1px solid black;
                color: white;
                padding: 5px;
            }

            .footer{
                font-size: 8pt;
                text-align: center;
                color: grey;
            }

            
        </style>
    </head>
    <body>

        <div class="container">
            <div class="logoContainer">
                <img src="$logo" alt="Logo" style="width: 100px; height: 100px;">
            </div>
            <div class="tituloContainer">
                <h1>Boletín de Afiliación</h1>
            </div>

            <p>Tui, $fecha</p>
            <p>D./Dona {$data['representanteNombre']}, en calidade de {$data['representanteCargo']} de la empresa {$data['nombre_comercial']}, solicita a súa afiliación á Asociación de Comerciantes, Industriais e Turismo de Tui, cos dereitos e as obrigas que establecen os estatutos da mesma.</p>

            <h2>1. Datos de la Empresa</h2>
            <table>
                <tr>
                    <td colspan="3"><strong>Nome Comercial</strong><br>{$data['nombre_comercial']}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Razón Social</strong><br>{$data['razon_social']}</td>
                    <td><strong>CIF</strong><br>{$data['cif']}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Titular</strong><br>{$data['titular']}</td>
                    <td><strong>DNI</strong><br>{$data['dni']}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Denominación da Actividade</strong><br>{$data['denominacion_actividad']}</td>
                    <td><strong>IAE</strong><br>{$data['iae']}</td>
                </tr>
                <tr class="equal-width">
                    <td><strong>Antigüedade</strong><br>{$data['antiguedad']}</td>
                    <td><strong>Nº Traballadores Autónomos</strong><br>{$data['n_trabajadores_autonomos']}</td>
                    <td><strong>Nº Traballadores conta allea</strong><br>{$data['n_trabajadores_cuenta_ajena']}</td>
                </tr>
            </table>

            <h2>2. Domicilio Social</h2>
            <table>
                <tr>
                    <td colspan="2"><strong>Enderezo</strong><br>{$data['direccion']}</td>
                    <td><strong>Número</strong><br>{$data['numero_direccion']}</td>
                </tr>
                <tr class="equal-width">
                    <td><strong>Poboación</strong><br>{$data['poblacion']}</td>
                    <td><strong>Código Postal</strong><br>{$data['codigo_postal']}</td>
                    <td><strong>Email</strong>{$data['email']}</td>
                </tr>
                <tr class="equal-width">
                    <td><strong>Teléfono</strong><br>{$data['telefono']}</td>
                    <td><strong>Fax</strong><br>{$data['fax']}</td>
                    <td><strong>Móvil</strong><br>{$data['movil']}</td>
                </tr>
            </table>

            <h2>3. Datos Bancarios</h2>
            <table>
                <tr>
                    <td colspan="2"><strong>Nome do Banco</strong><br>{$data['nombre_banco']}</td>
                    <td><strong>Localidad Banco</strong><br>{$data['localidad_banco']}</td>
                </tr>
                <tr class="equal-width">
                    <td><strong>Titular do Banco</strong><br>{$data['titular_banco']}</td>
                    <td><strong>Periodo de Pago</strong><br>{$data['periodo_pago']}</td>
                    <td><strong>Forma de Pago</strong><br>{$data['forma_pago']}</td>
                </tr>
                <tr>
                    <td colspan="3"><strong>IBAN</strong><br>{$data['iban']}</td>
                </tr>
            </table><br><br>

            <div class="tituloContainer">
                <div class="footer">Edificio Área Panorámica, R/Colón nº2 - 1º 36700 Tui. Pontevedra</div>
                <div class="footer">acitui.es - email: acitui@acitui.es</div>
            </div>
        </div>

        <div class="container">
            <div class="logoContainer">
                <img src="$logo" alt="Logo" style="width: 100px; height: 100px;">
            </div>
            <h2>4. Protección de datos</h2>
        
            <p>
                De conformidade co establecido na normativa vixente en Protección de Datos de Carácter Persoal, informámoslle que os seus datos serán incorporados ó sistema de tratamento titularidade da Asociación de Comerciantes e Industrias de Tui (ACITUI), con CIF.: G-36270003 e enderezo social na Rúa Colón nº2, Edificio Área Panorámica, 36700, Tui (Pontevedra), cua finalidade da xestión administrativa dos datos dos socios, así como para representar , defender e promocionar os intereses económicos, sociais, profesionais e cultirais dos socios, así como dos demais fins reflexados nos Estatutos da Asociación. Os seus datos serán conservados durante o prazo estrictamente necesario para cumprir cos preceptos mencionados con anterioridade, así mentres dure a súa condición de socio en ACITUI ou durante os anos necesarios para cumprir coas obrigacións legais.
            </p>

            <p>
                Mentres non nos comunique o contrario, entenderemos que os seus datos non foron modificados, que vostede se compromete a notificarnos calquera variación e que temos o seu consentimento para empregalos para as finalidades mencionadas.
            </p>
            <p>
                Así mesmo, informamoslle que se procederán a tratar os datos de maneira licita, leal, transparente, adecuada, pertinente, limitada, exacta e actualizada. Para isto comprometémonos a adoptar as medidas razoables para que estes suprímanse ou se rectifiquen sen dilación cando sexan inexactos.
            </p>
            <p>
                De acordo cos dereitos que lle confire a normativa vixente en protección de datos, poderá exercer os dereitos de acceso, rectificación, limitación de tratamento, supresión, portabilidade e oposición ó tratamento dos seus datos de carácter persoal, así como ó consentimento prestado para o tratamento dos mesmos, dirixindo a súa petición ó enderezo postal Rúa Colón nº 2, Edificio Area Panorámica, 36700, Tui (Pontevedra) ou ó correo electrónico acitui@acitui.es, en ámbolos casos a través dun escrito asinado para ese efecto e achegando copia do seu DNI.
            </p>
            <p>
                Se considra que o tratamento non se axusta á normativa vixente, poderá presta unha reclamación ante a autoridade de control en agpd.es.
            </p>
            <p>
                Os datos recadados serán cedidos a outros socios, a imprenta e os deseñadores gráficos previa autorización do interesado; a empresa de aloxamento da páxina web (encargada do tratamento), entidades bancarias, a Xunta de Galicia e nos casos nos que exista unha obriga legal. Cederanse os datos imprescindibles para elo e o consentimiento poderá ser revocado en calquera momento mediante comunicación fidedigna no enderezo ou no e-mail indicados anteriormente.
            </p>
            <p>
                Así mesmo, consinte e outorga expresamente a sú autorización para enviarlle comunicacións publicitarias, así como para que os seus datos sexan empregados no envío de comunicacións por e-mail, fax ou calquera outro medio electrónico ou físico, presente ou futuro e dielizalo coma socio. 
            </p>
                <div>
                    {$data['RepresentanteAceptacionComunicaciones']}
                </div>
            <p>
                Vostede consinte expresamente que o seu nome, amelidos, número de teléfono e/ou e-mail, sexan facilitados a outros socios para facilitar as interaccións entre os socios.
            </p>
            <div>
                    {$data['RepresentanteAceptacionComunicacionesEntreSocios']}    
            </div>
            <p>
                Vostede consinte expresamente que os seus datos de carácter persoal sexan empregados como publicidade, da súa actividade, en soportes ou dixitais, en redes sociais propias de ACITUI e en medios de comunicacións. Así mesmo consinte expresamente a cesión dos seus datos a imprenta “Gráfica Juvia”, “Señora Emilia”, “Pixeralia” e os deseñadores gráficos “Pixeralia” e a “Arlequín Publicidade” e “Señora Emilia” encargados de deseñar os soportes publicitarios e da impresión das campañas publicitarias.
            </p>
            <div>
                    {$data['RepresentanteAceptacionPublicidad']}  
            </div>
            <p>
                E para que así conste os efectos oportunos firmo este exemplar:
            </p>
            <p>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Firma do Solicitante:
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Selo da Asociación:
            </p>
            <br><br><br><br><br><br><br>
            <div class="tituloContainer">
                <div class="footer">Edificio Área Panorámica, R/Colón nº2 - 1º 36700 Tui. Pontevedra</div>
                <div class="footer">acitui.es - email: acitui@acitui.es</div>
            </div>
        </div>

        <!-- Página 3 -->
        <div class="container">
            <div class="logoContainer">
                <img src="$logo" alt="Logo" style="width: 100px; height: 100px;">
            </div>
            <div class="tituloContainer">
                <h2>CONSENTIMIENTO GRUPO MENSAJERÍA INSTANTÁNEA</h2>
            </div>
            <p>
                D./Dona: {$data['representanteNombre']}, con DNI: {$data['dni']}, Teléfono Móvil: {$data['movil']}, Enderezo: {$data['direccion']} - {$data['poblacion']} - {$data['codigo_postal']}
            </p>
            <h3>RESPONSABLE</h3>
            <p>ASOC. COMERCIANTES E INDUSTRIALES DE TUI - CIF G36272003 - ACITUI Edif. Area panorámica de tui, 1º. Ocina ACITUI. 36700, TUI (Pontevedra), España
            acitui@acitui.es - Tel. 629879730 - http://www.acitui.es</p>
            <h3>FINALIDADES</h3>
            <p>Inclusión en grupo de mensajería instantánea a través del número de teléfono con el  fin de facilitar la comunicación del personal con esta Empresa/Asociación. La inclusión o expulsíón únicamente podrá realizarse por el administrador del grupo.</p>
            <h3>LEGITIMACION</h3>
            <p>Consentimiento expreso del interesado</p>
            <h3>DATOS TRATADOS</h3>
            <p>Número de teléfono facilitado por la organización, nombre, conversaciones y archivos multimedia compartidos en el grupo</p>
            <h3>CONSERVACIÓN</h3>
            <p>Mientras se pertenezca al grupo se podrá guardar el histórico de conversaciones que sean necesarias para el n descrito. Cuando un miembro sea expulsado del grupo deberá eliminar inmediatamente todas las conversaciones del mismo, excepto si se requiere su conservación por una obligación legal o para la legítima defensa del miembro expulsado, en cuyo caso se deberá bloquear toda la información y limitar su tratamiento en tanto que pudieran derivarse responsabilidades de su relación con los otros miembros del grupo. El administrador del grupo podrá conservar indenidamente los datos y mensajes de todos los miembros que hayan permanecido en el grupo hasta la eliminación denitiva del mismo, excepto que se requiera su conservación por los mismos motivos que cualquier otro miembro del mismo</p>
            <h3>DESTINATARIOS</h3>
            <p>Todos los datos tratados en el grupo serán visibles por todos los miembros que pertenezcan al grupo en el momento en que se comparte la información.</p>
            <h3>TRANSFERENCIAS INTERNACIONALES</h3>
            <p>Los servidores de las aplicaciones de mensajería instantánea suelen ubicarse en paises europeos o países con garantías sucientes de seguridad bajo Privacy Shield.</p>
            <h3>PROCEDENCIA Y USO DE LOS DATOS</h3>
            <p>Todos los datos aportados en el grupo los aportan cada miembro a propia volundad y queda terminantemente prohibido su uso para nalidades distintas o compartir la información a terceros fuera del grupo</p>
            <h3>DEVOLUCIÓN DEL TERMINAL</h3>
            <p>Cuando se requiera la devolución del teléfono está terminantemente prohibido realizar  copias del contenido de las conversaciones del grupo o los grupos a los que ha o haya pertenecido sin la autorización expresa del administrador del grupo o del responsable del tratamiento.</p>
            <h3>DERECHOS</h3>
            <p>Tiene a acceder a sus datos, recticarlos, suprimirlos, limitar u oponerse a su tratamiento, a su portabilidad, a retirar su consentimiento y a reclamar ante la autoridad de control https://www.aepd.es</p>
            <h3>INFORMACION ADICIONAL</h3>
            <p>ASOC. COMERCIANTES E INDUSTRIALES DE TUI ACITUI Edif. Area panorámica de tui, 1º. Ocina ACITUI 36700 TUI PONTEVEDRA TELEFONO: 986 60 75 56</p>
            <h3>ACEPTACIÓN</h3>
            <p>La rma del presente documento indica que usted entiende, acepta y autoriza todas las cláusulas de nuestra política de privacidad
            para la recogida y el tratamiento de sus datos personales.</p>

            <div>
                Firmado:
            </div>
            <br><br><br>
            <div>En Tui, a $fecha por {$data['representanteNombre']} con DNI {$data['dni']}</div>
            <br>
            <div class="tituloContainer">
                <div class="footer">Edificio Área Panorámica, R/Colón nº2 - 1º 36700 Tui. Pontevedra</div>
                <div class="footer">acitui.es - email: acitui@acitui.es</div>
            </div>
        </div>

    </body>
    </html>
    HTML;
    
        // Generar el contenido PDF en memoria
        $mpdf->WriteHTML($html);
    
        // Guardar el PDF en una variable en lugar de en el archivo directamente
        $pdfContent = $mpdf->Output('', 'S'); // 'S' devuelve el PDF como cadena de texto
    
        // Guardar el archivo codificado en una ruta del servidor 'public/solicitud_alta_socio.pdf';
        //el nombre del archivo es el nombre del campo email sin la @ y el .
        $email = $data['email'];
        $emailSinArroba = str_replace('@', '', $email);
        $emailSinArroba = str_replace('.', '', $emailSinArroba);
        $fileName = "alta_socio_" . $emailSinArroba . ".pdf";
        $file = fopen("public/" . $fileName, "w");
    
        fwrite($file, $pdfContent);
        fclose($file);

        //enviar el pdf por email
        $urlCompleta = "https://pruebasbot.wairbot.es/acitui/api/public/" . $fileName;
        self::enviarEmailSolicitudAltaSocio($urlCompleta, $email);

        //enviar copia del pdf a la asociación
        self::enviarEmailSolicitudAltaSocio($urlCompleta, 'acitui@acitui.es');

        return true;
    }

    static private function enviarEmailSolicitudAltaSocio($pdfPath, $email){
        $servidorSMTP = "mail.acitui.es"; // Cambia por tu servidor SMTP
        $puerto = 25; // Usualmente 587 o 465
        $usuario = "no-responder@acitui.es";
        $contrasena = "N-Rspndr*2024";
        $de = "no-responder@acitui.es";

        $para = $email;
        $asunto = "SOLICITUD DE ALTA DE SOCIO ACITUI";
        $mensaje = "<html><body style='text-align: center;'>";
        $mensaje .= "<h1 style='margin-bottom: 20px;'>SOLICITUD DE ALTA DE SOCIO ACITUI</h1>";
        $mensaje .= "<p style='margin-bottom: 20px;'>Gracias por solicitar el alta de socio en ACITUI. Adjunto a este correo encontrarás la solicitud de alta de socio y el permiso de comunicación por mensajería instantanea que deberás firmar y enviar a la dirección de la asociación.</p>";
        $mensaje .= "</body></html>";

        $urlArchivoAdjunto = $pdfPath;

        Tools::enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, $asunto, $mensaje, $urlArchivoAdjunto);

        return true;
    }


    static public function editar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $userId = $_SESSION['user_id'];
        $data = $request['data'];
        $tabla = self::$tabla;
        $userRol = $_SESSION['user_rol'];

        //comprobar si existe alguna key de data que contenga file_upload_
        foreach($data as $key => $value){
            if(strpos($key, 'file_upload_') !== false){
                //el valor es un archivo en base64 y se debe guardar en la carpeta public con un timestamp, un uniqid, el nombre del archivo que está en data con el resto de la key
                
                $keyNombreArchivo = str_replace('file_upload_', '', $key);
                $nombreArchivo = $data[$keyNombreArchivo];
                //quitar los espacios en blanco del nombre del archivo
                $nombreArchivo = str_replace(' ', '', $nombreArchivo);
                $nombreArchivo = time() . '_' . uniqid() . '_' . $nombreArchivo;

                //guardar el archivo en la carpeta public
                $file = fopen("public/" . $nombreArchivo, "w");
                fwrite($file, base64_decode($value));
                fclose($file);

                //eliminar la key de data
                unset($data[$key]);

                //añadir la key con el nombre del archivo
                $data[$keyNombreArchivo] = $nombreArchivo;
            }
        }

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR' && $userRol !== 'SOCIO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para editar un usuario'
            ];
        }

        if($userRol === 'SOCIO' && $userId != $id){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para editar este usuario'
            ];
        }

        //si existe el campo iban se elimina del valor los espacios en blanco
        if(array_key_exists('iban', $data)){
            $data['iban'] = str_replace(' ', '', $data['iban']);
        }

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Usuario actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el usuario',
                'error' => $updated
            ];
        }
    }

    static public function conformarSocio($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $userRol = $_SESSION['user_rol'];
        $n_socio = $request['n_socio'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para conformar un usuario'
            ];
        }

        //comprobar si el número de socio ya está en uso
        $sql = "SELECT * FROM $tabla WHERE n_socio = '$n_socio'";
        $result = $conn->query($sql)->fetch_assoc();

        if($result){
            return [
                'status' => 'error',
                'message' => 'El número de socio ya está en uso'
            ];
        }

        $data = [
            'n_socio' => $n_socio,
            'estado' => 'INSCRITO'
        ];

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Usuario conformado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al conformar el usuario',
                'error' => $updated
            ];
        }
    }

    static public function bajaSocio($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $userRol = $_SESSION['user_rol'];
        $motivo_baja = $request['motivo_baja'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para dar de baja un usuario'
            ];
        }

        $data = [
            'estado' => 'BAJA',
            'motivo_baja' => $motivo_baja
        ];

        $updated = Tools::update($conn, $tabla, $id, $data);

        if($updated){
            return [
                'status' => 'success',
                'message' => 'Usuario dado de baja correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al dar de baja el usuario',
                'error' => $updated
            ];
        }
    }

    static public function listarPreinscritos($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los preinscritos'
            ];
        }

        $filtro = [
            'estado' => 'PREINSCRITO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

        return [
            'status' => 'success',
            'message' => 'Preinscritos obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function listarSocios($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver los socios'
            ];
        }

        $filtro = [
            'estado' => 'INSCRITO'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search);

        return [
            'status' => 'success',
            'message' => 'Socios obtenidos correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function listarBajas($request){
        $conn = $_SESSION['db_connection'];
        $nPagina = $request['nPagina'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];
        $search = array_key_exists('search', $request) ? $request['search'] : null;

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver las bajas'
            ];
        }

        $filtro = [
            'solicitud_baja' => '1'
        ];

        //sin datos relacionados
        $datosRelacionados = [];

        //ordenar por estado los estados BAJA deben aparecer primero
        $orderBy = 'ORDER BY estado DESC';

        $result = Tools::read($conn, $nPagina, $tabla, $filtro, $datosRelacionados, $search , $orderBy);

        return [
            'status' => 'success',
            'message' => 'Bajas obtenidas correctamente',
            'data' => [
                'paginaActual' => $result['paginaActual'],
                'paginasTotales' => $result['paginasTotales'],
                'data' => $result['data']
            ]
        ];
    }

    static public function obtenerPorId($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $userRol = $_SESSION['user_rol'];
        $userId = $_SESSION['user_id'];

        //si el rol no es administrador echar
        if($userRol !== 'ADMINISTRADOR' && $userRol !== 'SOCIO'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para editar un usuario'
            ];
        }

        if($userRol === 'SOCIO' && $userId != $id){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para editar este usuario'
            ];
        }

        $filtro = [];

        //obtener facturas
        $datosRelacionados = ['id_socio' => 'facturas'];

        $result = Tools::getById($conn, $tabla, $id, $filtro, $datosRelacionados);

        return [
            'status' => 'success',
            'message' => 'Usuario obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function obtenerPorNumeroDeSocio($request){
        $conn = $_SESSION['db_connection'];
        $n_socio = $request['n_socio'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver un usuario'
            ];
        }

        $sql = "SELECT * FROM $tabla WHERE n_socio = '$n_socio'";
        $result = $conn->query($sql)->fetch_assoc();

        return [
            'status' => 'success',
            'message' => 'Usuario obtenido correctamente',
            'data' => $result,
        ];
    }

    static public function obtenerFechaUltimaFacturaEmitida($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para ver un usuario'
            ];
        }

        //obtener la fecha de la última factura emitida
        $sql = "SELECT fecha_ultima_factura FROM $tabla ORDER BY fecha_ultima_factura DESC LIMIT 1";
        $result = $conn->query($sql)->fetch_assoc();

        return [
            'status' => 'success',
            'message' => 'Fecha de la última factura obtenida correctamente',
            'data' => $result,
        ];
    }

    //eliminar un usuario
    static public function eliminar($request){
        $conn = $_SESSION['db_connection'];
        $id = $request['id'];
        $tabla = self::$tabla;
        $rol = $_SESSION['user_rol'];

        //si el rol no es administrador echar
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar un usuario'
            ];
        }

        $check = ['facturas' => 'id_socio'];

        $deleted = Tools::delete($conn, $tabla, $id, $check);

        if($deleted){
            return [
                'status' => 'success',
                'message' => 'Usuario eliminado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al eliminar el usuario',
                'error' => $deleted
            ];
        }
    }

    static public function obtenerNombresUsuarios(){
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT n_socio, nombre_comercial FROM usuarios WHERE estado = 'INSCRITO'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $usuarios = $stmt->get_result();

        $usuarios = $usuarios->fetch_all(MYSQLI_ASSOC);

        //eliminar usuarios que n_socio o nombre_comercial sea null o vacio
        $usuarios = array_filter($usuarios, function($usuario){
            return !empty($usuario['n_socio']) && !empty($usuario['nombre_comercial']);
        });

        //poner usuarios en formato n_socio => nombre_comercial
        $usuarios = array_column($usuarios, 'nombre_comercial', 'n_socio');

        return [
            'status' => 'success',
            'message' => 'Datos obtenidos correctamente',
            'data' => $usuarios
        ];
    }

    static public function enviarCorreoAltaSocio($request){
        $email = $request['email'];
        $password = $request['password'];

        $servidorSMTP = "mail.acitui.es"; // Cambia por tu servidor SMTP
        $puerto = 25; // Usualmente 587 o 465
        $usuario = "no-responder@acitui.es";
        $contrasena = "N-Rspndr*2024";
        $de = "no-responder@acitui.es";

        $url = "https://pruebasbot.wairbot.es/acitui/?email=" . $email . "&password=" . $password;
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($url);

        $para = $email;
        $asunto = "Acceso a la aplicación";
        $mensaje = "<html><body style='text-align: center;'>";
        $mensaje .= "<h1 style='margin-bottom: 20px;'>ACCESO SOCIO ACITUI</h1>";
        $mensaje .= "<p style='margin-bottom: 20px;'>Para acceder a la aplicación escanea el siguiente QR</p>";
        $mensaje .= "<img src='" . $qrCodeUrl . "' style='margin-bottom: 20px;' alt='Acceso a la aplicación'/>";
        $mensaje .= "<p style='margin-bottom: 20px;'>O haz clic aquí:</p>";
        $mensaje .= "<a href='" . $url . "' style='margin-bottom: 20px;'>Acceder a la aplicación</a>";
        $mensaje .= "</body></html>";

        $urlArchivoAdjunto = "";

        Tools::enviarCorreoSMTP($servidorSMTP, $puerto, $usuario, $contrasena, $de, $para, $asunto, $mensaje, $urlArchivoAdjunto);

        return [
            'status' => 'success',
            'message' => 'Correo enviado correctamente'
        ];
    }
}

/*
BACKEND:
CLASE: Usuarios
    - obtenerNumeroSocioMasAlto
        params: none

    - preinscripcion
        params: data
        - rol
        - email
        - password
        - n_socio
        - estado
        - nombre_comercial
        - razon_social
        - cif
        - titular
        - dni
        - denominacion_actividad
        - iae
        - antiguedad
        - n_trabajadores_autonomos
        - n_trabajadores_cuenta_ajena
        - direccion
        - numero_direccion
        - poblacion
        - codigo_postal
        - telefono
        - fax
        - movil
        - nombre_banco
        - localidad_banco
        - titular_banco
        - iban
        - periodo_pago
        - forma_pago
        - fecha_ultima_factura
        - solicitud_baja
        - motivo_baja
        - timestamp

    - editar
        params: id, data...
    - conformarSocio
        params: id, n_socio
    - bajaSocio
        params: id, motivo_baja
    - listarPreinscritos
        params: nPagina
    - listarSocios
        params: nPagina
    - listarBajas
        params: nPagina
    - obtenerPorId
        params: id
*/