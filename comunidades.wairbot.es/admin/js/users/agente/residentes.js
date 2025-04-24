/*
BACKEND:
Clase: Residentes
Funciones:
- listar:
    - Request: nPagina, search, filtro
- verUno:
    - Request: id, codigoBotId
- crear:
    - Request: data, codigoBot
    - data: nombre, telefono, referencia
- editar:
    - Request: id, data, codigoBot
- borrar:
    - Request: id, codigoBot
*/
//email, password, prefijo, telefono, codigo_bot, tiempo, idioma
const camposResidentes = [
    {
        tipo: "text",
        id: "name",
        label: "Nombre",
    },{
        tipo: "text",
        id: "user_phone",
        label: "Teléfono",
    },{
        tipo: "text",
        id: "reference",
        label: "Referencia",
    }
];

const claseResidentes = "Residentes";
const funcionGuardarResidentes = "guardarResidentes";
const rolResidentes = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getResidentes(nPagina = 1) {
    let location = "Residentes";
    let clase = "Residentes"; //php
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openResidentes"; //js
    let funcionCrear = "crearResidentes"; //js
    let rol = rolResidentes;
    let titulo = "Residentes";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getResidentes",
    }
    let camposCard = {
        info1: "name",
        info2: "user_phone",
        info3: "",
        info4: "",
        info5: "reference",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarResidentes(id) {
    let titulo = "Eliminar Residentes";
    let mensaje = "¿Estas Seguro de eliminar este Residente? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarResidentes";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarResidentes(id) {
    let codigoBot = document.getElementById(`selectFiltro`).value;
    
    let clase = claseResidentes
    let funcion = "borrar"; //id, codigoBot
    let datos = {
        id: id,
        codigoBot: codigoBot
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if (data.status === "success") {
        getResidentes();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Residente Eliminado";
        let mensaje = "El Residente ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el Residente pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openResidentes(id) {
    console.log(id);
    //obtener el codigoBotId de la card
    let codigoBot = document.getElementById(`selectFiltro`).value;

    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseResidentes;
    let funcion = "verUno";
    let datos = {
        id: id,
        codigoBotId: codigoBot,
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarResidentes",
        id: idOriginal,
    };

    let campos = crearCamposDeFormulario(camposResidentes, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Residentes";
    let contenido = form;
    let funcionGuardar = funcionGuardarResidentes;
    let usuario = rolResidentes;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposResidentes tiene un campo bloquear y es true, bloquear el campo
    camposResidentes.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearResidentes() {
    //obtener selectFiltro y si está vacio mostrar modal de aviso
    let selectFiltro = document.getElementById("selectFiltro");
    if (selectFiltro.value === "") {
        let titulo = "Error";
        let mensaje = "Debes seleccionar una comunidad antes de crear un Residente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
        return;
    }

    let campos = crearCamposDeFormulario(camposResidentes);
    let form = createForm(campos, "");

    let titulo = "Crear Residentes";
    let contenido = form;
    let funcionGuardar = funcionGuardarResidentes;
    let usuario = rolResidentes;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarResidentes(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposResidentes, datosExtra);

    let clase = "Residentes";

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getResidentes();
    destruirModal();
}
