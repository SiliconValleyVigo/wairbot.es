const camposSocios = [
    {
        tipo: "text",
        id: "n_socio",
        label: "Nº Socio",
    },{
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "text",
        id: "password",
        label: "Contraseña",
    },{
        tipo: "text",
        id: "token",
        label: "Token",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {
            "PREINSCRITO": "Preinscrito",
            "INSCRITO": "Inscrito",
            "BAJA": "Baja",
        },
        defaultValue: "INSCRITO",
    },{
        tipo: "text",
        id: "nombre_comercial",
        label: "Nombre Comercial",
    },{
        tipo: "text",
        id: "razon_social",
        label: "Razón Social",
    },{
        tipo: "text",
        id: "cif",
        label: "CIF",
    },{
        tipo: "text",
        id: "titular",
        label: "Titular",
    },{
        tipo: "text",
        id: "dni",
        label: "DNI",
    },{
        tipo: "text",
        id: "denominacion_actividad",
        label: "Denominación Actividad",
    },{
        tipo: "text",
        id: "iae",
        label: "IAE",
    },{
        tipo: "text",
        id: "antiguedad",
        label: "Antigüedad",
    },{
        tipo: "text",
        id: "n_trabajadores_autonomos",
        label: "Nº Trabajadores Autónomos",
    },{
        tipo: "text",
        id: "n_trabajadores_cuenta_ajena",
        label: "Nº Trabajadores Cuenta Ajena",
    },{
        tipo: "text",
        id: "direccion",
        label: "Dirección",
    },{
        tipo: "text",
        id: "numero_direccion",
        label: "Número Dirección",
    },{
        tipo: "text",
        id: "poblacion",
        label: "Población",
    },{
        tipo: "text",
        id: "codigo_postal",
        label: "Código Postal",
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
    },{
        tipo: "text",
        id: "fax",
        label: "Fax",
    },{
        tipo: "text",
        id: "movil",
        label: "Móvil",
    },{
        tipo: "text",
        id: "nombre_banco",
        label: "Nombre Banco",
    },{
        tipo: "text",
        id: "localidad_banco",
        label: "Localidad Banco",
    },{
        tipo: "text",
        id: "titular_banco",
        label: "Titular Banco",
    },{
        tipo: "text",
        id: "iban",
        label: "IBAN",
    },{
        tipo: "select",
        id: "periodo_pago",
        label: "Periodo de Pago",
        options: {
            "3": "3 meses",
            "6": "6 meses",
            "12": "12 meses",
        },
        defaultValue: "texto",
    },{
        tipo: "select",
        id: "forma_pago",
        label: "Forma de Pago",
        options: {
            "EFECTIVO": "Efectivo",
            "TRANSFERENCIA": "Transferencia",
            "CARGO EN CUENTA": "Cargo en Cuenta",
        },
        defaultValue: "texto",
    },{
        tipo: "date",
        id: "fecha_ultima_factura",
        label: "Fecha Última Factura",
    },{
        tipo: "file",
        id: "documento",
        label: "Documento de Inscripción",
    },{
        tipo: "select",
        id: "solicitud_baja",
        label: "Solicitud de Baja",
        options: {
            "0": "No solicitada",
            "1": "Solicitada",
        },
    },{
        tipo: "text",
        id: "motivo_baja",
        label: "Motivo de Baja",
    }
];

const funcionGuardarSocios = "guardarSocio";
const rolSocios = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getSocios(nPagina = 1) {
    console.log("getSocios");
    let location = "socios";
    let clase = "Usuarios";//php
    let funcionLeer = "listarSocios";//php
    let funcionAbrir = "openSocio";//js
    let funcionCrear = "crearSocio";//js

    //comprobar el rol del usuario
    let usuario = localStorage.getItem('usuario');
    usuario = JSON.parse(usuario);

    let rol = usuario.rol;

    let titulo = "Socios";
    let hayPaginacion = true;
    let camposCard = {
        info1: "nombre_comercial",
        info2: "n_socio",
        info3: "razon_social",
        info4: "email",
        info5: "telefono",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openSocio(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'Usuarios';
    let funcion = 'obtenerPorId';
    let datos = {
        rol: 'SOCIO',
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data;
    console.log(data);

    //si el campo documento no está vacío, añadir la url al nombre del archivo https://pruebasbot.wairbot.es/acitui/api/public/DOCUMENTO
    if(data.documento !== ""){
        data.documento = "https://pruebasbot.wairbot.es/acitui/api/public/" + data.documento;
    }

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarSocio",
        id: idOriginal
    }

    let campos = crearCamposDeFormulario(camposSocios, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //crear el botón de generar y descargar QR
    //La ulr es https://pruebasbot.wairbot.es/acitui/?email=EMAIL&password=PASSWORD
    let urlQr = `https://pruebasbot.wairbot.es/acitui/?email=${data.email}&password=${data.password}`;
    //formatear la url para que no de error con simbolos como @ y / con la función encodeURI
    urlQr = encodeURI(urlQr);
    let botonQR = `
        <style>
            .qrBoton{
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 10px;
                margin-bottom: 10px;
            }
            .qrBoton .boton{
                background-color: #0a4fff;
                border: none;
                color: white;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
            }
        </style>
        <div class="qrBoton">
            <button class="boton" onclick="generarYDescargarQR('${urlQr}')">Tarjeta de Socio</button>
        </div>
    `;

    //comprobar si existe en data el campo facturas
    let tablaFacturas = "";
    if(data.facturas){
        let facturas = data.facturas;
        console.log(facturas);
        let facturasTabla = "";
        facturas.forEach(f => {
            facturasTabla += `
                <tr>
                    <td>${f.concepto}</td>
                    <td>
                        <a href="https://pruebasbot.wairbot.es/acitui/api/public/${f.pdf}" target="_blank">Descargar PDF</a>
                    </td>
                    <td>
                        <button onclick="enviarFacturaEmailDesdeSocio('https://pruebasbot.wairbot.es/acitui/api/public/${f.pdf}', '${data.email}')">Enviar por Email</button>
                    </td>
                </tr>
            `;
        });

        //dibujar la tabla solo si hay facturas
        if(facturasTabla !== ""){
            tablaFacturas = `
                <style>
                    .tablaFacturas{
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .tablaFacturas th, .tablaFacturas td{
                        border: 1px solid black;
                        padding: 8px;
                        text-align: left;
                    }
                    .tablaFacturas th{
                        background-color: #0a4fff;
                        color: white;
                    }
                </style>
                <table class="tablaFacturas">
                    <tr>
                        <th>Concepto</th>
                        <th>Ver</th>
                        <th>Enviar</th>
                    </tr>
                    ${facturasTabla}
                </table>
            `;
        }
    }

    //crear un botón pequeño que muestre los campos de solicitud de baja y motivo de baja
    let botonBaja = `
        <style>
            .botonBaja{
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 10px;
                margin-bottom: 10px;
            }
            .botonBaja .boton{
                background-color: grey;
                border: none;
                color: white;
                padding: 5px 10px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 12px;
                margin: 4px 2px;
                cursor: pointer;
            }
        </style>
        <div class="botonBaja">
            <button class="boton" onclick="mostrarCamposBaja()">Solicitud de Baja</button>
        </div>
    `;

    //botón que al hacer click ejecuta la funcion enviarQrEmail
    let botonQrEmail = `
        <style>
            .botonQrEmail{
                width: 100%;
                display: flex;
                justify-content: center;
                margin-top: 10px;
                margin-bottom: 10px;
            }
            .botonQrEmail .boton{
                background-color: #0a4fff;
                border: none;
                color: white;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
            }
        </style>
        <div class="botonQrEmail">
            <button class="boton" onclick="enviarQrEmail('${data.email}', '${data.password}')">Enviar QR por Email</button>
        </div>
    `;

    //Obtener el modal
    let titulo = "Editar Socio";
    let contenido = form + botonQR + botonQrEmail + tablaFacturas + botonBaja;
    let funcionGuardar = funcionGuardarSocios;

    let usuarioData = localStorage.getItem('usuario');
    usuarioData = JSON.parse(usuarioData);

    let usuario = usuarioData.rol;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si password está vacío, poner un password aleatorio con letras minuscúlas y números
    let password = document.getElementById("password");
    if(password.value === ""){
        password.value = Math.random().toString(36).slice(-8);
    }

    //si token está vacío, poner un token aleatorio con letras mayusculas, minúsculas, numeros y timestamp
    let token = document.getElementById("token");
    if(token.value === ""){
        token.value = Math.random().toString(36).slice(-8).toUpperCase() + Math.random().toString(36).slice(-8).toLowerCase() + Math.floor(Date.now() / 1000);
    }

    //si n_socio está vacío obtener el número de socio más alto de la base de datos y sumarle 1 con la función de la api obtenerNumeroSocioMasAlto
    let nSocio = document.getElementById("n_socio");
    if(nSocio.value === ""){
        let clase = 'Usuarios';
        let funcion = 'obtenerNumeroSocioMasAlto';
        let datos = {
            rol: 'SOCIO'
        };
        let data = await queryAsync(clase, funcion, datos);
        nSocio.value = parseInt(data.data.n_socio) + 1;
    }

    //si usuario es SOCIO, eliminar los padres d los campos password, token, estado, n_socio, periodo_pago, forma_pago y fecha_ultima_factura
    if(usuario === 'SOCIO'){
        let password = document.getElementById("password");
        let padre = password.parentNode;
        padre.style.display = "none";

        let token = document.getElementById("token");
        padre = token.parentNode;
        padre.style.display = "none";

        let estado = document.getElementById("estado");
        padre = estado.parentNode;
        padre.style.display = "none";

        let nSocio = document.getElementById("n_socio");
        padre = nSocio.parentNode;
        padre.style.display = "none";

        let periodoPago = document.getElementById("periodo_pago");
        padre = periodoPago.parentNode;
        padre.style.display = "none";

        let formaPago = document.getElementById("forma_pago");
        padre = formaPago.parentNode;
        padre.style.display = "none";

        let fechaUltimaFactura = document.getElementById("fecha_ultima_factura");
        padre = fechaUltimaFactura.parentNode;
        padre.style.display = "none";

        //de la clase modalBoton eliminar el botón de Eliminar por su innerHTML
        let modalBoton = document.querySelectorAll(".modalBoton");
        modalBoton.forEach(b => {
            if(b.innerHTML === "Eliminar"){
                b.style.display = "none";
            }
        });
    }

    //ocultar los campos de solicitud de baja y motivo de baja
    let solicitudBaja = document.getElementById("solicitud_baja");
    let padre = solicitudBaja.parentNode;
    padre.style.display = "none";

    let motivoBaja = document.getElementById("motivo_baja");
    padre = motivoBaja.parentNode;
    padre.style.display = "none";
}

async function enviarFacturaEmailDesdeSocio(pdf, email){
    let clase = 'Facturas';
    let funcion = 'enviarFacturaIndividual';
    let datos = {
        archivo: pdf,
        email: email
    };
    
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    //crear modal aviso de éxito
    let titulo = "Factura Enviada";
    let mensaje = "La factura ha sido enviada correctamente";
    let funcionAceptar = "destruirModalAviso";

    createModalAviso(titulo, mensaje, funcionAceptar);
}

async function enviarQrEmail(email, password){
    let clase = 'Usuarios';
    let funcion = 'enviarCorreoAltaSocio';
    let datos = {
        email: email,
        //email: 'susomacias@hotmail.com',
        password: password
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    //crear modal aviso de éxito
    let titulo = "QR Enviado";
    let mensaje = "El QR ha sido enviado correctamente";
    let funcionAceptar = "destruirModalAviso";

    createModalAviso(titulo, mensaje, funcionAceptar);
}

function mostrarCamposBaja(){
    let solicitudBaja = document.getElementById("solicitud_baja");
    let padre = solicitudBaja.parentNode;
    padre.style.display = "flex";

    let motivoBaja = document.getElementById("motivo_baja");
    padre = motivoBaja.parentNode;
    padre.style.display = "flex";
    motivoBaja.value = "";
    motivoBaja.focus();
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarSocio(id) {
    let titulo = "Eliminar Socio";
    let mensaje = "¿Estás seguro de que quieres eliminar este socio?";
    let funcionAceptar = "eliminarSocio";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarSocio(id) {
    let clase = 'Usuarios';
    let funcion = 'eliminar';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getSocios();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Socio Eliminado";
        let mensaje = "El socio ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el socio pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearSocio() {   
    let campos = crearCamposDeFormulario(camposSocios);
    let form = createForm(campos, "");

    let titulo = "Crear Socio";
    let contenido = form;
    let funcionGuardar = funcionGuardarSocios;
    let usuario = rolSocios;

    createModal(titulo, contenido, funcionGuardar, usuario);

    //si password está vacío, poner un password aleatorio con letras minuscúlas y números
    let password = document.getElementById("password");
    if(password.value === ""){
        password.value = Math.random().toString(36).slice(-8);
    }

    //si token está vacío, poner un token aleatorio con letras mayusculas, minúsculas, numeros y timestamp
    let token = document.getElementById("token");
    if(token.value === ""){
        token.value = Math.random().toString(36).slice(-8).toUpperCase() + Math.random().toString(36).slice(-8).toLowerCase() + Math.floor(Date.now() / 1000);
    }

    //si n_socio está vacío obtener el número de socio más alto de la base de datos y sumarle 1 con la función de la api obtenerNumeroSocioMasAlto
    let nSocio = document.getElementById("n_socio");
    if(nSocio.value === ""){
        let clase = 'Usuarios';
        let funcion = 'obtenerNumeroSocioMasAlto';
        let datos = {
            rol: 'SOCIO'
        };
        let data = await queryAsync(clase, funcion, datos);
        console.log(data);
        nSocio.value = parseInt(data.data.n_socio) + 1;
    }

    //eliminar el padre del campo fecha_ultima_factura
    let fechaUltimaFactura = document.getElementById("fecha_ultima_factura");
    let padre = fechaUltimaFactura.parentNode;
    padre.remove();
}

async function guardarSocio(id = 0){
    // si no existe el campo fecha_ultima_factura, añadirlo con valor "";
    let fechaUltimaFactura = document.getElementById("fecha_ultima_factura");
    if(fechaUltimaFactura === null){
        let div = document.createElement("div");
        div.innerHTML = `<label for="fecha_ultima_factura">Fecha Última Factura</label>
        <input type="date" id="fecha_ultima_factura" name="fecha_ultima_factura" value="">`;
        //el formulario está en la clase modalFormulario
        let formulario = document.querySelector(".modalFormulario");
        formulario.appendChild(div);
    }

    let datosExtra = {
        rol: 'SOCIO',
    };
    
    let datos = await getDatosFormulario(camposSocios, datosExtra);

    let clase = 'Usuarios';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'editar';
    }else{
        funcion = 'preinscripcion';
    }

    console.log(datos);

    await queryAsync(clase, funcion, datos);

    let usuario = localStorage.getItem('usuario');
    usuario = JSON.parse(usuario);

    if(usuario.rol === 'ADMINISTRADOR'){
        getSocios();
        destruirModal();
    }
}

async function generarYDescargarQR(url) {
    //obtener los datos del socio por el valor de los inputs n_socio, nombre_comercial, razon_social y el logo de la empresa que está en la url https://pruebasbot.wairbot.es/acitui/logo.jpg

    let nSocio = document.getElementById("n_socio").value;
    let nombreComercial = document.getElementById("nombre_comercial").value;
    let razonSocial = document.getElementById("razon_social").value;
    let logo = "https://pruebasbot.wairbot.es/acitui/logo.jpg";


    // Genera el QR
    const canvas = document.createElement('canvas');
    await QRCode.toCanvas(canvas, url, {
        width: 500,
        margin: 2.5,
    });

    // Convierte el canvas a una imagen PNG
    const imagenUrl = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");

    //crear una tarjeta de 85mm x 55mm con el QR, los datos del socio y el logo de la empresa y abrir una ventana nueva para imprimir la tarjeta
    let tarjeta = `
    <div style="width: 55mm; height: 85mm; border: 1px solid black; display: flex; flex-direction: column; justify-content: space-between; padding: 5mm; font-family: Arial; font-size: 9pt;">
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <img src="${logo}" style="width: 25mm; height: 25mm">
            <img src="${imagenUrl}" style="width: 40mm; height: 40mm; margin: 4mm;">
        </div>
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <div style="margin: 0;">Nº Socio: ${nSocio}</div>
            <div style="margin: 0; text-align: center;">${nombreComercial}</div>
        </div>
    </div>`;
    let ventana = window.open("", "_blank");
    ventana.document.write(tarjeta);

    //esperar 1 segundo para que se cargue la imagen
    await new Promise(r => setTimeout(r, 1000));

    ventana.print();
}

/*
https://pruebasbot.wairbot.es/acitui/
usuario: admin
password: admin

*Login*
* Hay un enlace para solicitar ser socio que lleva a un formulario con los campo necesarios
* Si se entra a través del QR de la tarjeta de socio entra directamente a la página del socio

*Pagina de socio*
* Editar los datos
* Ver las facturas
* Solicitar baja

*Solicitudes*
* Generar solicitudes desde administrador
* Se ven todas las solicitudes pendientes de aprobar

*Socios*
* Editar los datos
* Ver las facturas

*Bajas*
* Ver las solicitudes de baja (para hacerlas efectivas hay que seleccionar Estado: Baja y sería conveniente modificar la contraseña)

*Remesas*
* Crear una remesa en base a los tiempos de facturación de los socios, solo se generan las facturas de los socios que les toca pagar en ese periodo
* Ver las facturas Generadas en cada remesa
* Conformar todas las facturas en bloque
* Obtener un XML para el banco
* Descargar un Excel con las facturas
* Enviar facturas por email a los socios
* Cancelar la remesa (Solo si las facturas no están conformadas)

*Facturas*
* Ver las facturas
* Editar las facturas si no están conformadas
* Marcar como Abonada (aunque esté conformada)
* Descargar pdf de la factura
* Crear una factura manualmente con datos no asociados a un socio (puede elegir el socio y los datos se rellenan)
*/