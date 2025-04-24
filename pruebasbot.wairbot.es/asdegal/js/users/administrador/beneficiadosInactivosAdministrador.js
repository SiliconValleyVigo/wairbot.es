/*
id
nombre
referencia
identificacion
direccion
telefono_fijo
telefono_movil
email
nombre_contacto_1
contacto_1
nombre_contacto_2
contacto_2
notas
id_coordinador

*/
const camposBeneficiadosInactivosAdministrador = [
    {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
    },{
        tipo: "text",
        id: "referencia",
        label: "Referencia",
    },{
        tipo: "text",
        id: "identificacion",
        label: "Identificación",
    },{
        tipo: "text",
        id: "direccion",
        label: "Dirección",
    },{
        tipo: "text",
        id: "telefono_fijo",
        label: "Teléfono Fijo",
    },{
        tipo: "text",
        id: "telefono_movil",
        label: "Teléfono Móvil",
    },{
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "text",
        id: "nombre_contacto_1",
        label: "Nombre Contacto 1",
    },{
        tipo: "text",
        id: "contacto_1",
        label: "Contacto 1",
    },{
        tipo: "text",
        id: "nombre_contacto_2",
        label: "Nombre Contacto 2",
    },{
        tipo: "text",
        id: "contacto_2",
        label: "Contacto 2",
    },{
        tipo: "date",
        id: "fecha_nacimiento",
        label: "Fecha de Nacimiento",
    },{
        tipo: "text",
        id: "procedencia",
        label: "Procedencia",
    },{
        tipo: "file",
        id: "documento_informe_asistencia",
        label: "Documento Informe Asistencia",
    },{
        tipo: "text",
        id: "informe_asistencia",
        label: "Informe Asistencia",
    },{
        tipo: "textarea",
        id: "notas",
        label: "Notas",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {"ACTIVO":"Activo", "INACTIVO":"Inactivo", "BAJA":"Baja"},
    },{
        tipo: "dataListApi",
        id: "id_coordinador",
        label: "Coordinador",
        options: "Coordinadores::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "date",
        id: "fecha_alta",
        label: "Fecha Alta",
    },{
        tipo: "date",
        id: "fecha_baja",
        label: "Fecha Baja",
    },{
        tipo: "text",
        id: "causa_baja",
        label: "Causa Baja",
    }
];

const claseBeneficiadosInactivosAdministrador = "Beneficiarios";
const funcionGuardarBeneficiadosInactivosAdministrador = "guardarBeneficiadosInactivosAdministrador";
const rolBeneficiadosInactivosAdministrador = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getBeneficiadosInactivosAdministrador(nPagina = 1) {
    let location = "BeneficiadosInactivosAdministrador";
    let clase = claseBeneficiadosInactivosAdministrador
    let funcionLeer = "listarNoActivos"; //php
    let funcionAbrir = "openBeneficiadosInactivosAdministrador"; //js
    let funcionCrear = "crearBeneficiadosInactivosAdministrador"; //js
    let rol = rolBeneficiadosInactivosAdministrador;
    let titulo = "Beneficiarios Inactivos";
    let nombreTablaConfiguracion = "beneficiarios_baja";
    let camposInput = camposBeneficiadosInactivosAdministrador;
    let funcionModificacionDeTabla = "";
    let hayPaginacion = true;
    let filtro = false;
    let camposCard = {
        info1: "nombre",
        info2: "telefono_fijo", 
        info3: "telefono_movil",
        info4: "direccion",
        info5: "id_coordinador_nombre",
        id: "id",
    };
    await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, nombreTablaConfiguracion, camposInput, funcionModificacionDeTabla, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarBeneficiadosInactivosAdministrador(id) {
    let titulo = "Eliminar BeneficiadosInactivosAdministrador";
    let mensaje = "¿Estas Seguro de eliminar este Beneficiario? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarBeneficiadosInactivosAdministrador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarBeneficiadosInactivosAdministrador(id) {   
    let clase = claseBeneficiadosInactivosAdministrador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getBeneficiadosInactivosAdministrador();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Beneficiario Eliminado";
        let mensaje = "El Beneficiario ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el Beneficiario pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openBeneficiadosInactivosAdministrador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseBeneficiadosInactivosAdministrador;
    let funcion = "ObtenerPorId"
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarBeneficiadosInactivosAdministrador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposBeneficiadosInactivosAdministrador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //grid de acciones
    let gridAcciones = await getAccionesAdministradorDeBeneficiario(idOriginal);

    //Obtener el modal
    let titulo = "Editar Beneficiario";
    let contenido = form + gridAcciones;
    let funcionGuardar = funcionGuardarBeneficiadosInactivosAdministrador;
    let usuario = rolBeneficiadosInactivosAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposBeneficiadosInactivosAdministrador tiene un campo bloquear y es true, bloquear el campo
    camposBeneficiadosInactivosAdministrador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function getAccionesAdministradorDeBeneficiario(idBeneficiario, nPagina = 1) {
    let location = "AccionesAdministrador";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPorBeneficiado"; //php
    let funcionAbrir = "openAccionesAdministrador"; //js
    let funcionCrear = "crearAccionesAdministrador"; //js
    let rol = rolAccionesAdministrador;
    let titulo = "Acciones";
    let hayPaginacion = true;
    let filtro = false;
    let camposCard = {
        info1: "descripcion",
        info2: "id_beneficiario_nombre",
        info3: "fecha",
        info4: "id_voluntario_nombre",
        info5: "estado",
        id: "id",
    };
    let devolverHtml = true;

    let datosFuncionLeer = {
        id_beneficiario: idBeneficiario
    };
    let grid = await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro, devolverHtml, datosFuncionLeer);
    return grid;
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearBeneficiadosInactivosAdministrador() {
    let campos = await crearCamposDeFormulario(camposBeneficiadosInactivosAdministrador);
    let form = createForm(campos, "");

    let titulo = "Crear Beneficiado";
    let contenido = form;
    let funcionGuardar = funcionGuardarBeneficiadosInactivosAdministrador;
    let usuario = rolBeneficiadosInactivosAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarBeneficiadosInactivosAdministrador(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposBeneficiadosInactivosAdministrador, datosExtra);

    let clase = claseBeneficiadosInactivosAdministrador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getBeneficiadosInactivosAdministrador();
    destruirModal();
}
