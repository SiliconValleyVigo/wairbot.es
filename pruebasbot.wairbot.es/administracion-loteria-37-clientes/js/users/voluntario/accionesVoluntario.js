/*
id_coordinador
id_voluntario
id_beneficiario
fecha
descripcion
observaciones
confirmacion_cita
estado (PENDIENTE, FINALIZADA)
tiempo
*/
const camposAccionesVoluntario = [
    {
        tipo: "dataListApi",
        id: "id_beneficiario",
        label: "Beneficiario",
        options: "Beneficiarios::listarNombreMasIdPorVoluntario",
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

const claseAccionesVoluntario = "Acciones";
const funcionGuardarAccionesVoluntario = "guardarAccionesVoluntario";
const rolAccionesVoluntario = "VOLUNTARIO";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAccionesVoluntario(nPagina = 1) {
    let location = "AccionesVoluntario";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPorVoluntario"; //php
    let funcionAbrir = "openAccionesVoluntario"; //js
    let funcionCrear = "crearAccionesVoluntario"; //js
    let rol = rolAccionesVoluntario;
    let titulo = "Acciones";
    let nombreTablaConfiguracion = "acciones";
    let camposInput = camposBeneficiadosAdministrador;
    let funcionModificacionDeTabla = "";
    let hayPaginacion = true;
    let filtro = false;
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
function modalEliminarAccionesVoluntario(id) {
    let titulo = "Eliminar AccionesVoluntario";
    let mensaje = "¿Estas Seguro de eliminar esta Accion? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarAccionesVoluntario";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarAccionesVoluntario(id) {
    let clase = claseAccionesVoluntario
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getAccionesVoluntario();
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
async function openAccionesVoluntario(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseAccionesVoluntario;
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
        funcion: "modalEliminarAccionesVoluntario",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposAccionesVoluntario, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    let fichaBeneficiario = await fichaBeneficiarioEnAccionesVoluntario(data.id_beneficiario);
    let fichaVoluntario = await fichaVoluntarioEnAccionesVoluntario(data.id_voluntario);
    let fichaCoordinador = await fichaCoordinadorEnAccionesVoluntario(data.id_coordinador);

    let fichasContainer = `
        <div class="fichasContainer">
            ${fichaBeneficiario}
            ${fichaVoluntario}
            ${fichaCoordinador}
        </div>
    `;

    //Obtener el modal
    let titulo = "Editar AccionesVoluntario";
    let contenido = form + fichasContainer;
    let funcionGuardar = funcionGuardarAccionesVoluntario;
    let usuario = rolAccionesVoluntario;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposAccionesVoluntario tiene un campo bloquear y es true, bloquear el campo
    camposAccionesVoluntario.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function fichaBeneficiarioEnAccionesVoluntario(id){
    let clase = "Beneficiarios";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
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

async function fichaVoluntarioEnAccionesVoluntario(id){
    let clase = "Voluntarios";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
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
async function fichaCoordinadorEnAccionesVoluntario(id){
    let clase = "Coordinadores";
    let funcion = "obtenerPorId";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
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
async function crearAccionesVoluntario() {
    let campos = await crearCamposDeFormulario(camposAccionesVoluntario);
    let form = createForm(campos, "");

    let titulo = "Crear Accion";
    let contenido = form;
    let funcionGuardar = funcionGuardarAccionesVoluntario;
    let usuario = rolAccionesVoluntario;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarAccionesVoluntario(id = 0) {
    //obtener el id del usuario de localstorage
    let usuario = localStorage.getItem("usuario");
    usuario = JSON.parse(usuario);

    let datosExtra = {
        id_voluntario: usuario.id
    };

    let datos = getDatosFormulario(camposAccionesVoluntario, datosExtra);

    let clase = claseAccionesVoluntario;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getAccionesVoluntario();
    destruirModal();
}
