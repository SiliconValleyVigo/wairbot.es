/*
id
rol
imagen
nombre
referencia
identificacion
direccion
telefono_fijo
telefono_movil
email
password
estado
localizacion
preferencias
notas
*/
const camposCoordinadoresAdministrador = [
    {
        tipo: "select",
        id: "rol",
        label: "Rol",
        options: {"COORDINADOR":"Coordinador", "VOLUNTARIO":"Voluntario", "ADMINISTRADOR":"Administrador"},
    },{
        tipo: "file",
        id: "imagen",
        label: "Imagen",
    },{
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
        id: "password",
        label: "Password",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {"ACTIVO":"Activo", "INACTIVO":"Inactivo", "BAJA":"Baja"},
    },{
        tipo: "text",
        id: "delegacion",
        label: "Delegación",
    },{
        tipo: "text",
        id: "localizacion",
        label: "Localización",
    },{
        tipo: "textarea",
        id: "notas",
        label: "Notas",
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

const claseCoordinadoresAdministrador = "Coordinadores";
const funcionGuardarCoordinadoresAdministrador = "guardarCoordinadoresAdministrador";
const rolCoordinadoresAdministrador = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getCoordinadoresAdministrador(nPagina = 1) {
    let location = "CoordinadoresAdministrador";
    let clase = claseCoordinadoresAdministrador; //php
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openCoordinadoresAdministrador"; //js
    let funcionCrear = "crearCoordinadoresAdministrador"; //js
    let rol = rolCoordinadoresAdministrador;
    let titulo = "Coordinadores";
    let nombreTablaConfiguracion = "coordinadores";
    let camposInput = camposCoordinadoresAdministrador;
    let funcionModificacionDeTabla = "";
    let hayPaginacion = true;
    let filtro = false;
    let camposCard = {
        info1: "nombre",
        info2: "telefono_fijo",
        info3: "telefono_movil",
        info4: "direccion",
        info5: "email",
        id: "id",
    };
    await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, nombreTablaConfiguracion, camposInput, funcionModificacionDeTabla, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarCoordinadoresAdministrador(id) {
    let titulo = "Eliminar Coordinador";
    let mensaje = "¿Estas Seguro de eliminar este Coordinador? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarCoordinadoresAdministrador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarCoordinadoresAdministrador(id) {  
    let clase = claseCoordinadoresAdministrador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getCoordinadoresAdministrador();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Coordinador Eliminado";
        let mensaje = "El Coordinador ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el Coordinador pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openCoordinadoresAdministrador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseCoordinadoresAdministrador;
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
        funcion: "modalEliminarCoordinadoresAdministrador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposCoordinadoresAdministrador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //grid de acciones
    let gridAcciones = await getAccionesAdministradorDeCoordinador(idOriginal);

    //grid de voluntarios
    let gridVoluntarios = await getVoluntariosAdministradorDeCoordinador(idOriginal);

    //grid de beneficiados
    let gridBeneficiados = await getBeneficiadosAdministradorDeCoordinador(idOriginal);

    //Obtener el modal
    let titulo = "Editar CoordinadoresAdministrador";
    let contenido = form + gridAcciones + '<hr style="width: 99%; margin: 30px 0 10px 0;">' + gridVoluntarios + '<hr style="width: 99%; margin: 30px 0 10px 0;">' + gridBeneficiados;
    let funcionGuardar = funcionGuardarCoordinadoresAdministrador;
    let usuario = rolCoordinadoresAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposCoordinadoresAdministrador tiene un campo bloquear y es true, bloquear el campo
    camposCoordinadoresAdministrador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function getAccionesAdministradorDeCoordinador(idCoordinador, nPagina = 1) {
    let location = "AccionesAdministrador";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPorCoordinador"; //php
    let funcionAbrir = "openAccionesAdministrador"; //js
    let funcionCrear = "crearAccionesAdministrador"; //js
    let rol = rolAccionesAdministrador;
    let titulo = "Acciones";
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
    let devolverHtml = true;

    let datosFuncionLeer = {
        id_coordinador: idCoordinador
    };
    let grid = await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro, devolverHtml, datosFuncionLeer);
    return grid;
}

async function getVoluntariosAdministradorDeCoordinador(idCoordinador, nPagina = 1) {
    let location = "VoluntariosAdministrador";
    let clase = "Voluntarios" //php
    let funcionLeer = "listarPorCoordinador"; //php
    let funcionAbrir = "openVoluntariosAdministrador"; //js
    let funcionCrear = "crearVoluntariosAdministrador"; //js
    let rol = rolVoluntariosAdministrador;
    let titulo = "Voluntarios";
    let hayPaginacion = true;
    let filtro = false;
    let camposCard = {
        info1: "nombre",
        info2: "telefono_fijo",
        info3: "telefono_movil",
        info4: "direccion",
        info5: "email",
        id: "id",
    };

    let devolverHtml = true;

    let datosFuncionLeer = {
        id_coordinador: idCoordinador
    };

    let grid = await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro, devolverHtml, datosFuncionLeer);
    return grid;
}

async function getBeneficiadosAdministradorDeCoordinador(idCoordinador, nPagina = 1) {
    let location = "BeneficiadosAdministrador";
    let clase = "Beneficiarios" //php
    let funcionLeer = "listarPorCoordinador"; //php
    let funcionAbrir = "openBeneficiadosAdministrador"; //js
    let funcionCrear = "crearBeneficiadosAdministrador"; //js
    let rol = rolBeneficiadosAdministrador;
    let titulo = "Beneficiarios";
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

    let devolverHtml = true;

    let datosFuncionLeer = {
        id_coordinador: idCoordinador
    };

    let grid = await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro, devolverHtml, datosFuncionLeer);
    return grid;
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearCoordinadoresAdministrador() {
    let campos = await crearCamposDeFormulario(camposCoordinadoresAdministrador);
    let form = createForm(campos, "");

    let titulo = "Crear Coordinador";
    let contenido = form;
    let funcionGuardar = funcionGuardarCoordinadoresAdministrador;
    let usuario = rolCoordinadoresAdministrador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarCoordinadoresAdministrador(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposCoordinadoresAdministrador, datosExtra);

    let clase = claseCoordinadoresAdministrador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getCoordinadoresAdministrador();
    destruirModal();
}
