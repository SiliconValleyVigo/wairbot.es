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
const camposAccionesCoordinador = [
    {
        tipo: "dataListApi",
        id: "id_voluntario",
        label: "Voluntario",
        options: "Voluntarios::listarNombreMasIdPorCoordinador",
        defaultValue: "",
    },{
        tipo: "dataListApi",
        id: "id_beneficiario",
        label: "Beneficiario",
        options: "Beneficiarios::listarNombreMasIdPorCoordinador",
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
        options: {"PENDIENTE":"Pendiente", "FINALIZADA":"Finalizada"},
    },{
        tipo: "text",
        id: "tiempo",
        label: "Tiempo empleado (horas)"
    }
];

const claseAccionesCoordinador = "Acciones";
const funcionGuardarAccionesCoordinador = "guardarAccionesCoordinador";
const rolAccionesCoordinador = "COORDINADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAccionesCoordinador(nPagina = 1) {
    let location = "AccionesCoordinador";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPorCoordinador"; //php
    let funcionAbrir = "openAccionesCoordinador"; //js
    let funcionCrear = "crearAccionesCoordinador"; //js
    let rol = rolAccionesCoordinador;
    let titulo = "Acciones";
    let nombreTablaConfiguracion = "acciones";
    let camposInput = camposAccionesCoordinador;
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
function modalEliminarAccionesCoordinador(id) {
    let titulo = "Eliminar AccionesCoordinador";
    let mensaje = "¿Estas Seguro de eliminar esta Accion? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarAccionesCoordinador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarAccionesCoordinador(id) {
    let clase = claseAccionesCoordinador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getAccionesCoordinador();
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
async function openAccionesCoordinador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseAccionesCoordinador;
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
        funcion: "modalEliminarAccionesCoordinador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposAccionesCoordinador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    let fichaBeneficiario = await fichaBeneficiarioEnAccionesCoordinador(data.id_beneficiario);
    let fichaVoluntario = await fichaVoluntarioEnAccionesCoordinador(data.id_voluntario);
    let fichaCoordinador = await fichaCoordinadorEnAccionesCoordinador(data.id_coordinador);

    let fichasContainer = `
        <div class="fichasContainer">
            ${fichaBeneficiario}
            ${fichaVoluntario}
            ${fichaCoordinador}
        </div>
    `;

    //Obtener el modal
    let titulo = "Editar AccionesCoordinador";
    let contenido = form + fichasContainer;
    let funcionGuardar = funcionGuardarAccionesCoordinador;
    let usuario = rolAccionesCoordinador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposAccionesCoordinador tiene un campo bloquear y es true, bloquear el campo
    camposAccionesCoordinador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function fichaBeneficiarioEnAccionesCoordinador(id){
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

async function fichaVoluntarioEnAccionesCoordinador(id){
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
async function fichaCoordinadorEnAccionesCoordinador(id){
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
async function crearAccionesCoordinador() {
    let campos = await crearCamposDeFormulario(camposAccionesCoordinador);
    let form = createForm(campos, "");

    let titulo = "Crear Accion";
    let contenido = form;
    let funcionGuardar = funcionGuardarAccionesCoordinador;
    let usuario = rolAccionesCoordinador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarAccionesCoordinador(id = 0) {
    //obtener el id del usuario de localstorage
    let usuario = localStorage.getItem("usuario");
    usuario = JSON.parse(usuario);

    let datosExtra = {
        id_coordinador: usuario.id
    };

    let datos = getDatosFormulario(camposAccionesCoordinador, datosExtra);

    let clase = claseAccionesCoordinador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getAccionesCoordinador();
    destruirModal();
}
