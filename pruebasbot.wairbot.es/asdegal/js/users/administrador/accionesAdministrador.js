/*
`id_coordinador` INT(12) NULL DEFAULT NULL,
`id_voluntario` INT(12) NULL DEFAULT NULL,
`id_beneficiario` INT(12) NULL DEFAULT NULL,
`descripcion` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`fecha` DATE NULL DEFAULT NULL,
`hora` TIME NULL DEFAULT NULL,
`lugar1` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`lugar2` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`observaciones` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`confirmacion_cita` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`confirmacion_voluntario` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`notas` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`estado` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`tiempo` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
*/
const camposAccionesAdministrador = [
    {
        tipo: "dataListApi",
        id: "id_coordinador",
        label: "Coordinador",
        options: "Coordinadores::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "dataListApi",
        id: "id_voluntario",
        label: "Voluntario",
        options: "Voluntarios::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "dataListApi",
        id: "id_beneficiario",
        label: "Beneficiario",
        options: "Beneficiarios::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "dataListCompleto",
        id: "descripcion",
        label: "Descripcion",
        options: "descripcion::acciones",
    },{
        tipo: "date",
        id: "fecha",
        label: "Fecha",
    },{
        tipo: "time",
        id: "hora",
        label: "Hora",
    },{
        tipo: "text",
        id: "lugar1",
        label: "Lugar 1",
    },{
        tipo: "text",
        id: "lugar2",
        label: "Lugar 2",
    },{
        tipo: "textarea",
        id: "observaciones",
        label: "Observaciones",
    },{
        tipo: "select",
        id: "confirmacion_cita",
        label: "Confirmacion Cita",
        options: ["SI", "NO"],
        defaultValue: "NO",
    },{
        tipo: "select",
        id: "confirmacion_voluntario",
        label: "Confirmacion Voluntario",
        options: ["SI", "NO"],
        defaultValue: "NO",
    },{
        tipo: "textarea",
        id: "notas",
        label: "Notas",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {"PENDIENTE":"Pendiente", "FINALIZADA":"Finalizada", "PERIODICA":"Periodica"},
    },{
        tipo: "text",
        id: "periodo",
        label: "Periodo",
    },{
        tipo: "text",
        id: "tiempo",
        label: "Tiempo empleado (horas)"
    }
];

const claseAccionesAdministrador = "Acciones";
const funcionGuardarAccionesAdministrador = "guardarAccionesAdministrador";
const rolAccionesAdministrador = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAccionesAdministrador(nPagina = 1) {
    let location = "AccionesAdministrador";
    let clase = "Acciones"; //php
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openAccionesAdministrador"; //js
    let funcionCrear = "crearAccionesAdministrador"; //js
    let rol = rolAccionesAdministrador;
    let titulo = "Acciones";
    let nombreTablaConfiguracion = "acciones";
    let camposInput = camposAccionesAdministrador;
    let funcionModificacionDeTabla = "";
    let hayPaginacion = true;
    let filtro = {
        clase: "Selects",
        funcion: "getForNombreSelect",
        data: {
            nombre: "descripcion",
        }
    }
    let camposCard = {
        info1: "id_beneficiario_nombre",
        info2: "id_voluntario_nombre",
        info3: "fecha",
        info4: "descripcion",
        info5: "estado",
        id: "id",
    };
    await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, nombreTablaConfiguracion, camposInput, funcionModificacionDeTabla, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarAccionesAdministrador(id) {
    let titulo = "Eliminar AccionesAdministrador";
    let mensaje = "¿Estas Seguro de eliminar esta Accion? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarAccionesAdministrador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarAccionesAdministrador(id) {
    let clase = claseAccionesAdministrador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getAccionesAdministrador();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Accion Eliminada";
        let mensaje = "La Accion ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar la Accion pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openAccionesAdministrador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseAccionesAdministrador;
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarAccionesAdministrador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposAccionesAdministrador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    let fichaBeneficiario = await fichaBeneficiarioEnAccionesAdministrador(data.id_beneficiario);

    //comprobar si el voluntario no está vacío
    let fichaVoluntario = "";
    if(data.id_voluntario !== "" && data.id_voluntario !== null && data.id_voluntario !== undefined && data.id_voluntario !== "0" && data.id_voluntario !== 0){
        fichaVoluntario = await fichaVoluntarioEnAccionesAdministrador(data.id_voluntario);
    }

    let fichaCoordinador = "";
    if(data.id_coordinador !== "" && data.id_coordinador !== null && data.id_coordinador !== undefined && data.id_coordinador !== "0" && data.id_coordinador !== 0){
        fichaCoordinador = await fichaCoordinadorEnAccionesAdministrador(data.id_coordinador);
    }

    let fichasContainer = `
        <div class="fichasContainer">
            ${fichaBeneficiario}
            ${fichaVoluntario}
            ${fichaCoordinador}
        </div>
    `;

    //Obtener el modal
    let titulo = "Editar AccionesAdministrador";
    let contenido = form + fichasContainer;
    let funcionGuardar = funcionGuardarAccionesAdministrador;
    let usuario = rolAccionesAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposAccionesAdministrador tiene un campo bloquear y es true, bloquear el campo
    camposAccionesAdministrador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function fichaBeneficiarioEnAccionesAdministrador(id){
    let clase = "Beneficiarios";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    if(data.status === "error"){
        return "";
    }

    data = data.data;

    //calcular edad a partir de la fecha de nacimiento
    let edad = "";
    let fechaNacimiento = data.fecha_nacimiento;

    if(fechaNacimiento !== "" && fechaNacimiento !== null && fechaNacimiento !== undefined){
        edad = calcularEdadFichaBeneficiarioAcciones(fechaNacimiento);
    }

    let fichaBeneficiario = `
        <style>
            .ficha {
                margin-bottom: 10px;
            }
        </style>
        <div class="ficha">
            <div class="fichaTitulo">Beneficiario</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${data.nombre}</div>
                <div class="fichaCampo"><strong>Edad:&nbsp;</strong> ${edad}</div>
                <div class="fichaCampo"><strong>Referencia:&nbsp;</strong> ${data.referencia}</div>
                <div class="fichaCampo"><strong>Identificación:&nbsp;</strong> ${data.identificacion}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${data.direccion}</div>
                <div class="fichaCampo"><strong>Teléfono Fijo:&nbsp;</strong> <a href="tel:${data.telefono_fijo}">${data.telefono_fijo}</a></div>
                <div class="fichaCampo"><strong>Teléfono Móvil:&nbsp;</strong> <a href="tel:${data.telefono_movil}">${data.telefono_movil}</a></div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> <a href="mailto:${data.email}">${data.email}</a></div>
                <div class="fichaCampo"><strong>Nombre Contacto 1:&nbsp;</strong> ${data.nombre_contacto_1}</div>
                <div class="fichaCampo"><strong>Contacto 1:&nbsp;</strong> <a href="tel:${data.contacto_1}">${data.contacto_1}</a></div>
                <div class="fichaCampo"><strong>Nombre Contacto 2:&nbsp;</strong> ${data.nombre_contacto_2}</div>
                <div class="fichaCampo"><strong>Contacto 2:&nbsp;</strong> <a href="tel:${data.contacto_2}">${data.contacto_2}</a></div>
                <div class="fichaCampo"><strong>Notas:&nbsp;</strong> ${data.notas}</div>
            </div>
        </div>  
    `;

    return fichaBeneficiario;
}

function calcularEdadFichaBeneficiarioAcciones(fechaNacimiento){
    let fechaActual = new Date();
    let fechaNacimientoDate = new Date(fechaNacimiento);
    let edad = fechaActual.getFullYear() - fechaNacimientoDate.getFullYear();
    let mes = fechaActual.getMonth() - fechaNacimientoDate.getMonth();

    if(mes < 0 || (mes === 0 && fechaActual.getDate() < fechaNacimientoDate.getDate())){
        edad--;
    }

    return edad;
}

async function fichaVoluntarioEnAccionesAdministrador(id){
    let clase = "Voluntarios";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if(data.status === "error"){
        return "";
    }

    data = data.data;

    let fichaVoluntario = `
        <style>
            .ficha {
                margin-bottom: 10px;
            }
        </style>
        <div class="ficha">
            <div class="fichaTitulo">Voluntario</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${data.nombre}</div>
                <div class="fichaCampo"><strong>Referencia:&nbsp;</strong> ${data.referencia}</div>
                <div class="fichaCampo"><strong>Identificación:&nbsp;</strong> ${data.identificacion}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${data.direccion}</div>
                <div class="fichaCampo"><strong>Teléfono Fijo:&nbsp;</strong> <a href="tel:${data.telefono_fijo}">${data.telefono_fijo}</a></div>
                <div class="fichaCampo"><strong>Teléfono Móvil:&nbsp;</strong> <a href="tel:${data.telefono_movil}">${data.telefono_movil}</a></div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> <a href="mailto:${data.email}">${data.email}</a></div>
                <div class="fichaCampo"><strong>Estado:&nbsp;</strong> ${data.estado}</div>
                <div class="fichaCampo"><strong>Localización:&nbsp;</strong> ${data.localizacion}</div>
                <div class="fichaCampo"><strong>Preferencias:&nbsp;</strong> ${data.preferencias}</div>
                <div class="fichaCampo"><strong>Notas:&nbsp;</strong> ${data.notas}</div>
            </div>
        </div>
    `;

    return fichaVoluntario;
}

//ficha coordinador (mismos campos que voluntario)
async function fichaCoordinadorEnAccionesAdministrador(id){
    let clase = "Coordinadores";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if(data.status === "error"){
        return "";
    }

    data = data.data;

    let fichaCoordinador = `
        <style>
            .ficha {
                margin-bottom: 10px;
            }
        </style>
        <div class="ficha">
            <div class="fichaTitulo">Voluntario</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${data.nombre}</div>
                <div class="fichaCampo"><strong>Referencia:&nbsp;</strong> ${data.referencia}</div>
                <div class="fichaCampo"><strong>Identificación:&nbsp;</strong> ${data.identificacion}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${data.direccion}</div>
                <div class="fichaCampo"><strong>Teléfono Fijo:&nbsp;</strong> <a href="tel:${data.telefono_fijo}">${data.telefono_fijo}</a></div>
                <div class="fichaCampo"><strong>Teléfono Móvil:&nbsp;</strong> <a href="tel:${data.telefono_movil}">${data.telefono_movil}</a></div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> <a href="mailto:${data.email}">${data.email}</a></div>
                <div class="fichaCampo"><strong>Estado:&nbsp;</strong> ${data.estado}</div>
                <div class="fichaCampo"><strong>Localización:&nbsp;</strong> ${data.localizacion}</div>
                <div class="fichaCampo"><strong>Preferencias:&nbsp;</strong> ${data.preferencias}</div>
                <div class="fichaCampo"><strong>Notas:&nbsp;</strong> ${data.notas}</div>
            </div>
        </div>
    `;

    return fichaCoordinador;
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearAccionesAdministrador() {
    let campos = await crearCamposDeFormulario(camposAccionesAdministrador);
    let form = createForm(campos, "");

    let titulo = "Crear Accion";
    let contenido = form;
    let funcionGuardar = funcionGuardarAccionesAdministrador;
    let usuario = rolAccionesAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarAccionesAdministrador(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposAccionesAdministrador, datosExtra);

    let clase = claseAccionesAdministrador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getAccionesAdministrador();
    destruirModal();
}
