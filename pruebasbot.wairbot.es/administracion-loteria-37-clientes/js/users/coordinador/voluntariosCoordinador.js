
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
id_coordinador
*/
const camposVoluntariosCoordinador = [
    {
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
        options: {"ACTIVO":"Activo", "INACTIVO":"Inactivo"},
    },{
        tipo: "text",
        id: "horas_disponibles",
        label: "Horas Disponibles",
    },{
        tipo: "text",
        id: "dias_disponibles",
        label: "Días Disponibles",
    },{
        tipo: "text",
        id: "delegacion",
        label: "Delegación",
    },{
        tipo: "text",
        id: "localizacion",
        label: "Localización",
    },{
        tipo: "dataListCompleto",
        id: "preferencias",
        label: "Preferencias",
        options: "preferencias::usuarios",
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

const claseVoluntariosCoordinador = "Voluntarios";//php
const funcionGuardarVoluntariosCoordinador = "guardarVoluntariosCoordinador";
const rolVoluntariosCoordinador = "COORDINADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getVoluntariosCoordinador(nPagina = 1) {
    let location = "VoluntariosCoordinador";
    let clase = claseVoluntariosCoordinador; //php
    let funcionLeer = "listarPorCoordinador"; //php
    let funcionAbrir = "openVoluntariosCoordinador"; //js
    let funcionCrear = "crearVoluntariosCoordinador"; //js
    let rol = rolVoluntariosCoordinador;
    let titulo = "Voluntarios";
    let nombreTablaConfiguracion = "voluntarios";
    let camposInput = camposVoluntariosCoordinador;
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
function modalEliminarVoluntariosCoordinador(id) {
    let titulo = "Eliminar Voluntario";
    let mensaje = "¿Estas Seguro de eliminar este voluntario? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarVoluntariosCoordinador";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarVoluntariosCoordinador(id) {  
    let clase = claseVoluntariosCoordinador
    let funcion = "eliminar";
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getVoluntariosCoordinador();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Voluntario Eliminado";
        let mensaje = "El voluntario ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el voluntario pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openVoluntariosCoordinador(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseVoluntariosCoordinador;
    let funcion = "obtenerPorId";
    let datos = {
        id: id,
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarVoluntariosCoordinador",
        id: idOriginal,
    };

    let campos = await crearCamposDeFormulario(camposVoluntariosCoordinador, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //grid de acciones
    let gridAcciones = await getAccionesAdministradorDeVoluntarioEnVoluntariosCoordinador(idOriginal);

    //Obtener el modal
    let titulo = "Editar Voluntario";
    let contenido = form + gridAcciones;
    let funcionGuardar = funcionGuardarVoluntariosCoordinador;
    let usuario = rolVoluntariosCoordinador;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposVoluntariosCoordinador tiene un campo bloquear y es true, bloquear el campo
    camposVoluntariosCoordinador.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

async function getAccionesAdministradorDeVoluntarioEnVoluntariosCoordinador(id, nPagina = 1) {
    let location = "AccionesAdministrador";
    let clase = "Acciones"; //php
    let funcionLeer = "listarPorVoluntario"; //php
    let funcionAbrir = "openAccionesAdministrador"; //js
    let funcionCrear = "crearAccionesAdministrador"; //js
    let rol = rolAccionesAdministrador;
    let titulo = "Acciones";
    let hayPaginacion = true;
    let filtro = false;
    let nombreTablaConfiguracion = "acciones";
    let camposInput = camposAccionesAdministrador;
    let funcionModificacionDeTabla = "";
    let camposCard = {
        info1: "id_beneficiario_nombre",
        info2: "id_voluntario_nombre",
        info3: "fecha",
        info4: "descripcion",
        info5: "estado",
        id: "id",
    };
    let returnGrid = true;

    let datosFuncionLeer = {
        id_voluntario: id
    };
    
    let grid = await getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, nombreTablaConfiguracion,   camposInput, funcionModificacionDeTabla, filtro, returnGrid, datosFuncionLeer);

    return grid;
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearVoluntariosCoordinador() {
    let campos = await crearCamposDeFormulario(camposVoluntariosCoordinador);
    let form = createForm(campos, "");

    let titulo = "Crear Voluntario";
    let contenido = form;
    let funcionGuardar = funcionGuardarVoluntariosCoordinador;
    let usuario = rolVoluntariosCoordinador;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarVoluntariosCoordinador(id = 0) {
    let usuario = localStorage.getItem("usuario");
    usuario = JSON.parse(usuario);

    let datosExtra = {
        id_coordinador: usuario.id,
    };

    let datos = getDatosFormulario(camposVoluntariosCoordinador, datosExtra);

    let clase = claseVoluntariosCoordinador;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getVoluntariosCoordinador();
    destruirModal();
}
