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
const camposAccionesPeriodicasAdministrador = [
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

const claseAccionesPeriodicasAdministrador = "Acciones";
const funcionGuardarAccionesPeriodicasAdministrador = "guardarAccionesPeriodicasAdministrador";
const rolAccionesPeriodicasAdministrador = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAccionesPeriodicasAdministrador(nPagina = 1) {
    let location = "AccionesPeriodicasAdministrador";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPeriodicas"; //php
    let funcionAbrir = "openAccionesPeriodicasAdministrador"; //js
    let funcionCrear = "crearAccionesPeriodicasAdministrador"; //js
    let rol = rolAccionesPeriodicasAdministrador;
    let titulo = "Acciones Periodicas";
    let nombreTablaConfiguracion = "acciones_periodicas";
    let camposInput = camposAccionesPeriodicasAdministrador;
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
function modalEliminarAccionesPeriodicasAdministrador(id) {
    let titulo = "Eliminar AccionesPeriodicasAdministrador";
    let mensaje = "¿Estas Seguro de eliminar esta Accion? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarAccionesPeriodicasAdministrador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarAccionesPeriodicasAdministrador(id) {
    let clase = claseAccionesPeriodicasAdministrador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getAccionesPeriodicasAdministrador();
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
async function openAccionesPeriodicasAdministrador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseAccionesPeriodicasAdministrador;
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
        funcion: "modalEliminarAccionesPeriodicasAdministrador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposAccionesPeriodicasAdministrador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    let fichaBeneficiario = await fichaBeneficiarioEnAccionesPeriodicasAdministrador(data.id_beneficiario);
    let fichaVoluntario = await fichaVoluntarioEnAccionesPeriodicasAdministrador(data.id_voluntario);
    let fichaCoordinador = await fichaCoordinadorEnAccionesPeriodicasAdministrador(data.id_coordinador);

    let fichasContainer = `
        <div class="fichasContainer">
            ${fichaBeneficiario}
            ${fichaVoluntario}
            ${fichaCoordinador}
        </div>
    `;

    //Obtener el modal
    let titulo = "Editar AccionesPeriodicasAdministrador";
    let contenido = form + fichasContainer;
    let funcionGuardar = funcionGuardarAccionesPeriodicasAdministrador;
    let usuario = rolAccionesPeriodicasAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposAccionesPeriodicasAdministrador tiene un campo bloquear y es true, bloquear el campo
    camposAccionesPeriodicasAdministrador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function fichaBeneficiarioEnAccionesPeriodicasAdministrador(id){
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

async function fichaVoluntarioEnAccionesPeriodicasAdministrador(id){
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
async function fichaCoordinadorEnAccionesPeriodicasAdministrador(id){
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
async function crearAccionesPeriodicasAdministrador() {
    let campos = await crearCamposDeFormulario(camposAccionesPeriodicasAdministrador);
    let form = createForm(campos, "");

    let titulo = "Crear Accion";
    let contenido = form;
    let funcionGuardar = funcionGuardarAccionesPeriodicasAdministrador;
    let usuario = rolAccionesPeriodicasAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarAccionesPeriodicasAdministrador(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposAccionesPeriodicasAdministrador, datosExtra);

    let clase = claseAccionesPeriodicasAdministrador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getAccionesPeriodicasAdministrador();
    destruirModal();
}
