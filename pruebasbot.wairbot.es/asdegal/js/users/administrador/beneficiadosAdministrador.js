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
const camposBeneficiadosAdministrador = [
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
        id: "edad",
        label: "Edad",
    },{
        tipo: "text",
        id: "procedencia",
        label: "Procedencia",
    },{
        tipo: "file",
        id: "documento_informe_asistencia",
        label: "Doc. Informe Asistencia",
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

const claseBeneficiadosAdministrador = "Beneficiarios";
const funcionGuardarBeneficiadosAdministrador = "guardarBeneficiadosAdministrador";
const rolBeneficiadosAdministrador = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getBeneficiadosAdministrador(nPagina = 1) {
    let location = "BeneficiadosAdministrador";
    let clase = claseBeneficiadosAdministrador
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openBeneficiadosAdministrador"; //js
    let funcionCrear = "crearBeneficiadosAdministrador"; //js
    let rol = rolBeneficiadosAdministrador;
    let titulo = "Beneficiarios";
    let nombreTablaConfiguracion = "beneficiarios";
    let camposInput = camposBeneficiadosAdministrador;
    let funcionModificacionDeTabla = "getEdadBeneficiadosAdministrador";
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

function getEdadBeneficiadosAdministrador() {
    //obtener todos los inputs con el id que empieza por beneficiarios::fecha_nacimiento
    let inputs = document.querySelectorAll("input[id^='beneficiarios::fecha_nacimiento']");
    inputs.forEach((input) => {
        let id = input.id;
        let idArray = id.split("::");
        let idRegistro = idArray[2];

        let fechaNacimiento = input.value;

        //fecha actual
        let fechaActual = new Date();
        let diaActual = fechaActual.getDate();
        let mesActual = fechaActual.getMonth() + 1;
        let anioActual = fechaActual.getFullYear();

        //fecha de nacimiento
        let fechaNac = new Date(fechaNacimiento);
        let diaNac = fechaNac.getDate();
        let mesNac = fechaNac.getMonth() + 1;
        let anioNac = fechaNac.getFullYear();

        //calcular edad
        let edadCalculada = anioActual - anioNac;
        if (mesActual < mesNac || (mesActual === mesNac && diaActual < diaNac)) {
            edadCalculada--;
        }

        //si edadCalculada es NaN, ponerlo a ??
        if (isNaN(edadCalculada)) {
            edadCalculada = "??";
        }

        //actualizar el input de la edad
        let inputEdad = document.getElementById(`beneficiarios::edad::${idRegistro}`);
        inputEdad.value = edadCalculada;
    });
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarBeneficiadosAdministrador(id) {
    let titulo = "Eliminar BeneficiadosAdministrador";
    let mensaje = "¿Estas Seguro de eliminar este Beneficiario? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarBeneficiadosAdministrador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarBeneficiadosAdministrador(id) {   
    let clase = claseBeneficiadosAdministrador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getBeneficiadosAdministrador();
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
async function openBeneficiadosAdministrador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseBeneficiadosAdministrador;
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
        funcion: "modalEliminarBeneficiadosAdministrador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposBeneficiadosAdministrador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //grid de acciones
    let gridAcciones = await getAccionesAdministradorDeBeneficiario(idOriginal);

    //Obtener el modal
    let titulo = "Editar Beneficiario";
    let contenido = form + gridAcciones;
    let funcionGuardar = funcionGuardarBeneficiadosAdministrador;
    let usuario = rolBeneficiadosAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposBeneficiadosAdministrador tiene un campo bloquear y es true, bloquear el campo
    camposBeneficiadosAdministrador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });

    //comprobar si la fecha de nacimiento está rellena

    //calcula la edad a partir de la fecha de nacimiento
    let fechaNacimiento = document.getElementById("fecha_nacimiento");

    if (fechaNacimiento.value !== "") {
        calcularEdad();
    }
}

function calcularEdad() {
    let fechaNacimiento = document.getElementById("fecha_nacimiento");
    let edad = document.getElementById("edad");

    //fecha actual
    let fechaActual = new Date();
    let diaActual = fechaActual.getDate();
    let mesActual = fechaActual.getMonth() + 1;
    let anioActual = fechaActual.getFullYear();

    //fecha de nacimiento
    let fechaNac = new Date(fechaNacimiento.value);
    let diaNac = fechaNac.getDate();
    let mesNac = fechaNac.getMonth() + 1;
    let anioNac = fechaNac.getFullYear();

    //calcular edad
    let edadCalculada = anioActual - anioNac;
    if (mesActual < mesNac || (mesActual === mesNac && diaActual < diaNac)) {
        edadCalculada--;
    }

    edad.value = edadCalculada;
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
async function crearBeneficiadosAdministrador() {
    let campos = await crearCamposDeFormulario(camposBeneficiadosAdministrador);
    let form = createForm(campos, "");

    let titulo = "Crear Beneficiado";
    let contenido = form;
    let funcionGuardar = funcionGuardarBeneficiadosAdministrador;
    let usuario = rolBeneficiadosAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarBeneficiadosAdministrador(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposBeneficiadosAdministrador, datosExtra);

    //eliminar campos que no quiero editar (edad)
    delete datos.data.edad;

    let clase = claseBeneficiadosAdministrador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getBeneficiadosAdministrador();
    destruirModal();
}
