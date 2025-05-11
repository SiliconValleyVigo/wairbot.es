//comprobar si hay token en localstorage
let token = localStorage.getItem('token');

function encodeHTML(str) {
    return str.replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#39;");
}

if (token === null || token === undefined || token === 'undefined' || token === '' || token === 'null') {
    mostrarSection('login');
} else {
    query('getAllData', 'getAll', {}, 'guardarDatosLogin');
}

function mostrarSection(section) {
    let sections = document.querySelectorAll('section');
    sections.forEach(s => {
        s.style.display = 'none';
    });

    document.getElementById(section).style.display = 'flex';

    let thisUrl = window.location.href;
    let sanitizedUrl = encodeHTML(thisUrl);

    if (section === 'login') {
        let htmlLogin = `
            <form id="formLogin" style="display: flex;">
                <img src="${sanitizedUrl}/imgs/logo.svg" alt="Logo" class="logo">
                <h2>LOGIN</h2>
                <div class="inputGroup">
                    <label>Usuario o email</label>
                    <input type="text" id="usuarioLogin" required>
                </div>
                <div class="inputGroup">
                    <label>Contraseña</label>
                    <input type="password" id="passwordLogin" required>
                </div>
                <button onclick="login(event)">Entrar</button>
                <div class="cambioFormularioLoginRegistro" onclick="entrarEnRegistro(event)">
                    Aun no eres Socio? Registrate aqui
                </div>
                <small id="avisoLogin"></small>
            </form>
            <form id="formRegistro" style="display: none; max-width: 600px; width: 80%;">
                <img src="${thisUrl}/imgs/logo.svg" alt="Logo" class="logo">
                <h2>Solicitar Inscripción</h2>
                <div class="inputGroup">
                    <label>Nombre Representante de la Solicitud</label>
                    <input type="text" id="representanteNombre" required>
                </div>
                <div class="inputGroup">
                    <label>Cargo Representante de la Solicitud</label>
                    <input type="text" id="representanteCargo" required>
                </div>
                <div class="inputGroup">
                    <label>Email</label>
                    <input type="text" id="emailRegistro" required>
                </div>
                <div class="inputGroup">
                    <label>Nombre Comercial</label>
                    <input type="text" id="nombreComercialRegistro">
                </div>
                <div class="inputGroup">
                    <label>Razon Social</label>
                    <input type="text" id="razonSocialRegistro">
                </div>
                <div class="inputGroup">
                    <label>CIF</label>
                    <input type="text" id="cifRegistro">
                </div>
                <div class="inputGroup">
                    <label>Titular</label>
                    <input type="text" id="titularRegistro">
                </div>
                <div class="inputGroup">
                    <label>DNI</label>
                    <input type="text" id="dniRegistro">
                </div>
                <div class="inputGroup">
                    <label>Denominacion Actividad</label>
                    <input type="text" id="denominacionActividadRegistro">
                </div>
                <div class="inputGroup">
                    <label>IAE</label>
                    <input type="text" id="iaeRegistro">
                </div>
                <div class="inputGroup">
                    <label>Antiguedad</label>
                    <input type="text" id="antiguedadRegistro">
                </div>
                <div class="inputGroup">
                    <label>Nº Trabajadores Autonomos</label>
                    <input type="text" id="nTrabajadoresAutonomosRegistro">
                </div>
                <div class="inputGroup">
                    <label>Nº Trabajadores Cuenta Ajena</label>
                    <input type="text" id="nTrabajadoresCuentaAjenaRegistro">
                </div>
                <div class="inputGroup">
                    <label>Direccion</label>
                    <input type="text" id="direccionRegistro">
                </div>
                <div class="inputGroup">
                    <label>Nº Direccion</label>
                    <input type="text" id="numeroDireccionRegistro">
                </div>
                <div class="inputGroup">
                    <label>Poblacion</label>
                    <input type="text" id="poblacionRegistro">
                </div>
                <div class="inputGroup">
                    <label>Codigo Postal</label>
                    <input type="text" id="codigoPostalRegistro">
                </div>
                <div class="inputGroup">
                    <label>Telefono</label>
                    <input type="text" id="telefonoRegistro">
                </div>
                <div class="inputGroup">
                    <label>Fax</label>
                    <input type="text" id="faxRegistro">
                </div>
                <div class="inputGroup">
                    <label>Movil</label>
                    <input type="text" id="movilRegistro">
                </div>
                <div class="inputGroup">
                    <label>Nombre Banco</label>
                    <input type="text" id="nombreBancoRegistro">
                </div>
                <div class="inputGroup">
                    <label>Localidad Banco</label>
                    <input type="text" id="localidadBancoRegistro">
                </div>
                <div class="inputGroup">
                    <label>Titular Banco</label>
                    <input type="text" id="titularBancoRegistro">
                </div>
                <div class="inputGroup">
                    <label>IBAN</label>
                    <input type="text" id="ibanRegistro">
                </div>
                <div>
                    <h2>Protección de datos</h2>
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
                    <div class="inputGroup">
                        <select name="aceptacion" id="RepresentanteAceptacionComunicaciones" value="Sí Acepto">
                            <option value="Sí Acepto">Sí Acepto</option>
                            <option value="No Acepto">No Acepto</option>
                        </select>
                    </div>
                    <p>
                       Vostede consinte expresamente que o seu nome, amelidos, número de teléfono e/ou e-mail, sexan facilitados a outros socios para facilitar as interaccións entre os socios.
                    </p>
                    <div class="inputGroup">
                        <select name="aceptacion" id="RepresentanteAceptacionComunicacionesEntreSocios" value="Sí Acepto">
                            <option value="Sí Acepto">Sí Acepto</option>
                            <option value="No Acepto">No Acepto</option>
                        </select>
                    </div>

                    <p>
                        Vostede consinte expresamente que os seus datos de carácter persoal sexan empregados como publicidade, da súa actividade, en soportes ou dixitais, en redes sociais propias de ACITUI e en medios de comunicacións. Así mesmo consinte expresamente a cesión dos seus datos a imprenta “Gráfica Juvia”, “Señora Emilia”, “Pixeralia” e os deseñadores gráficos “Pixeralia” e a “Arlequín Publicidade” e “Señora Emilia” encargados de deseñar os soportes publicitarios e da impresión das campañas publicitarias.
                    </p>
                    <div class="inputGroup">
                        <select name="aceptacion" id="RepresentanteAceptacionPublicidad" value="Sí Acepto">
                            <option value="Sí Acepto">Sí Acepto</option>
                            <option value="No Acepto">No Acepto</option>
                        </select>
                    </div>

                </div>
                <button onclick="registro(event)">Solicitar</button>
                <div class="cambioFormularioLoginRegistro" onclick="entrarEnLogin(event)">
                    Ya eres Socio? Entra aqui
                </div>
            </form>
        `;

        document.getElementById('login').innerHTML = htmlLogin;
    }
}

function entrarEnRegistro(e) {
    e.preventDefault();
    let formLogin = document.getElementById('formLogin');
    let formRegistro = document.getElementById('formRegistro');
    let login = document.getElementById('login');

    formLogin.style.display = 'none';
    formRegistro.style.display = 'flex';

    login.style.height = 'auto';
    login.style.paddingTop = '25px';
}

function entrarEnLogin(e) {
    e.preventDefault();
    let formLogin = document.getElementById('formLogin');
    let formRegistro = document.getElementById('formRegistro');
    let login = document.getElementById('login');

    formLogin.style.display = 'flex';
    formRegistro.style.display = 'none';

    login.style.height = 'calc(100dvh - 133px)';
    login.style.marginTop = '0';
}

async function registro(e) {
    e.preventDefault();
    let email = document.getElementById('emailRegistro').value;
    let nombreComercial = document.getElementById('nombreComercialRegistro').value;
    let razonSocial = document.getElementById('razonSocialRegistro').value;
    let cif = document.getElementById('cifRegistro').value;
    let titular = document.getElementById('titularRegistro').value;
    let dni = document.getElementById('dniRegistro').value;
    let denominacionActividad = document.getElementById('denominacionActividadRegistro').value;
    let iae = document.getElementById('iaeRegistro').value;
    let antiguedad = document.getElementById('antiguedadRegistro').value;
    let nTrabajadoresAutonomos = document.getElementById('nTrabajadoresAutonomosRegistro').value;
    let nTrabajadoresCuentaAjena = document.getElementById('nTrabajadoresCuentaAjenaRegistro').value;
    let direccion = document.getElementById('direccionRegistro').value;
    let numeroDireccion = document.getElementById('numeroDireccionRegistro').value;
    let poblacion = document.getElementById('poblacionRegistro').value;
    let codigoPostal = document.getElementById('codigoPostalRegistro').value;
    let telefono = document.getElementById('telefonoRegistro').value;
    let fax = document.getElementById('faxRegistro').value;
    let movil = document.getElementById('movilRegistro').value;
    let nombreBanco = document.getElementById('nombreBancoRegistro').value;
    let localidadBanco = document.getElementById('localidadBancoRegistro').value;
    let titularBanco = document.getElementById('titularBancoRegistro').value;
    let iban = document.getElementById('ibanRegistro').value;

    //Representante
    let representanteNombre = document.getElementById('representanteNombre').value;
    let representanteCargo = document.getElementById('representanteCargo').value;
    let RepresentanteAceptacionComunicaciones = document.getElementById('RepresentanteAceptacionComunicaciones').value;
    let RepresentanteAceptacionComunicacionesEntreSocios = document.getElementById('RepresentanteAceptacionComunicacionesEntreSocios').value;
    let RepresentanteAceptacionPublicidad = document.getElementById('RepresentanteAceptacionPublicidad').value;

    let data = {
        rol: 'SOCIO',
        email: email,
        password: '',
        n_socio: '',
        token: '',
        estado: 'PREINSCRITO',
        nombre_comercial: nombreComercial,
        razon_social: razonSocial,
        cif: cif,
        titular: titular,
        dni: dni,
        denominacion_actividad: denominacionActividad,
        iae: iae,
        antiguedad: antiguedad,
        n_trabajadores_autonomos: nTrabajadoresAutonomos,
        n_trabajadores_cuenta_ajena: nTrabajadoresCuentaAjena,
        direccion: direccion,
        numero_direccion: numeroDireccion,
        poblacion: poblacion,
        codigo_postal: codigoPostal,
        telefono: telefono,
        fax: fax,
        movil: movil,
        nombre_banco: nombreBanco,
        localidad_banco: localidadBanco,
        titular_banco: titularBanco,
        iban: iban,
        periodo_pago: 3,
        forma_pago: 'CARGO EN CUENTA',
        fecha_ultima_factura: '',
        solicitud_baja: '',
        motivo_baja: '',
        representanteNombre: representanteNombre,
        representanteCargo: representanteCargo,
        RepresentanteAceptacionComunicaciones: RepresentanteAceptacionComunicaciones,
        RepresentanteAceptacionComunicacionesEntreSocios: RepresentanteAceptacionComunicacionesEntreSocios,
        RepresentanteAceptacionPublicidad: RepresentanteAceptacionPublicidad
    };

    let clase = 'Usuarios';
    let funcion = 'preinscripcion';
    let datos = { data: data };

    let preinscripcion = await queryAsync(clase, funcion, datos);

    let status = preinscripcion.status;

    if (status === 'success') {
        let titulo = 'Solicitud de Inscripción Enviada';
        let mensaje = 'Su solicitud de inscripción ha sido enviada correctamente. Nos pondremos en contacto con usted lo antes posible. Gracias.';
        createModalAviso(titulo, mensaje)
    }

    //volvemos a mostrar el formulario de login
    entrarEnLogin(e);
}