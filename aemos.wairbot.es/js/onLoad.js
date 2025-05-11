//comprobar si hay token en localstorage
let token = localStorage.getItem('token');

if(token === null || token === undefined || token === 'undefined' || token === '' || token === 'null'){
    mostrarSection('login');
}else{
    query('getAllData', 'getAll', {}, 'guardarDatosLogin');
}

//eliminar del localstorage location
localStorage.removeItem('location');

function mostrarSection(section) {
    let sections = document.querySelectorAll('section');
    sections.forEach(s => {
        s.style.display = 'none';
    });

    document.getElementById(section).style.display = 'flex';

    let thisUrl = new URL(window.location.href).origin;
    
    if(section === 'login'){
        let htmlLogin = `
            <form id="formLogin" style="display: flex;">
                <img src="${thisUrl}/imgs/logo.svg" alt="Logo" class="logo">
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
                <div class="cambioFormularioLoginRegistro" onclick="entrarEnRegistro(event)" style="display: none;">
                    Aun no eres Socio? Registrate aqui
                </div>
                <small id="avisoLogin"></small>
            </form>
            <form id="formRegistro" style="display: none;">
                <img src="${thisUrl}/imgs/logo.svg" alt="Logo" class="logo">
                <h2>Solicitar Inscripción</h2>
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
                <button onclick="registro(event)">Solicitar</button>
                <div class="cambioFormularioLoginRegistro" onclick="entrarEnLogin(event)">
                    Ya eres Socio? Entra aqui
                </div>
            </form>
        `;

        document.getElementById('login').innerHTML = htmlLogin;
    }
}

function entrarEnRegistro(e){
    e.preventDefault();
    let formLogin = document.getElementById('formLogin');
    let formRegistro = document.getElementById('formRegistro');
    let login = document.getElementById('login');

    formLogin.style.display = 'none';
    formRegistro.style.display = 'flex';

    login.style.height = 'auto';
    login.style.paddingTop = '25px';
}

function entrarEnLogin(e){
    e.preventDefault();
    let formLogin = document.getElementById('formLogin');
    let formRegistro = document.getElementById('formRegistro');
    let login = document.getElementById('login');

    formLogin.style.display = 'flex';
    formRegistro.style.display = 'none';

    login.style.height = 'calc(100dvh - 133px)';
    login.style.marginTop = '0';
}

async function registro(e){
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
        motivo_baja: ''
    };

    let clase = 'Usuarios';
    let funcion = 'preinscripcion';
    let datos = {data: data};

    let preinscripcion = await queryAsync(clase, funcion, datos);

    let status = preinscripcion.status;

    if(status === 'success'){
        let titulo = 'Solicitud de Inscripción Enviada';
        let mensaje = 'Su solicitud de inscripción ha sido enviada correctamente. Nos pondremos en contacto con usted lo antes posible. Gracias.';
        createModalAviso(titulo, mensaje)
    }
    
    //volvemos a mostrar el formulario de login
    entrarEnLogin(e);
}