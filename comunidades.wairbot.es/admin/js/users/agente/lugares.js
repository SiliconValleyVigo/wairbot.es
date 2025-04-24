/*
BACKEND:
Clase: Lugares
Funciones:
    listar
        datos: nPagina, search, filtro

    verUno:
        datos: id, codigoBot

    crear
        datos: data, codigoBot

    editar
        datos: id, data, codigoBot

    borrar
        datos: id, codigoBot

data: lugar_1, lugar_2
*/
/*
campos = [{
    tipo: "dataList",
    id: "prefijo",
    label: "Codigo Telefónico Internacional",
    options: optionPrefix,
    defaultValue: "34",
    bloquear: true,
}...]
*/
//email, password, prefijo, telefono, codigo_bot, tiempo, idioma
const camposLugares = [
    {
        tipo: "text",
        id: "lugar_1",
        label: "Lugar 1",
    },{
        tipo: "text",
        id: "lugar_2",
        label: "Lugar 2",
    }
];

const claseLugares = "Lugares";
const funcionGuardarLugares = "guardarLugares";
const rolLugares = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getLugares(nPagina = 1) {
    let location = "Lugares";
    let clase = "Lugares"; //php
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openLugares"; //js
    let funcionCrear = "crearLugares"; //js
    let rol = rolLugares;
    let titulo = "Lugares";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getLugares",
    }
    let camposCard = {
        info1: "lugar_1",
        info2: "lugar_2",
        info3: "",
        info4: "",
        info5: "referencia_Usuario",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarLugares(id) {
    let titulo = "Eliminar Lugares";
    let mensaje = "¿Estas Seguro de eliminar este lugar? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarLugares";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarLugares(id) {
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_5")[0].innerText; 
    
    let clase = claseLugares
    let funcion = "borrar"; //id, codigoBot
    let datos = {
        id: id,
        codigoBot: codigoBot
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if (data.status === "success") {
        getLugares();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Lugar Eliminado";
        let mensaje = "El Lugar ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el lugar pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openLugares(id) {
    //obtener el codigoBotId de la card
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_5")[0].innerText;

    let idOriginal = id;

    // OBTENER DATOS //
    let clase = claseLugares;
    let funcion = "verUno";
    let datos = {
        id: id,
        codigoBotId: codigoBot,
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarLugares",
        id: idOriginal,
    };

    let campos = crearCamposDeFormulario(camposLugares, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Lugares";
    let contenido = form;
    let funcionGuardar = funcionGuardarLugares;
    let usuario = rolLugares;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposLugares tiene un campo bloquear y es true, bloquear el campo
    camposLugares.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearLugares() {
    //obtener selectFiltro y si está vacio mostrar modal de aviso
    let selectFiltro = document.getElementById("selectFiltro");
    if (selectFiltro.value === "") {
        let titulo = "Error";
        let mensaje = "Debes seleccionar una comunidad antes de crear un lugar";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
        return;
    }

    let campos = crearCamposDeFormulario(camposLugares);
    let form = createForm(campos, "");

    let titulo = "Crear Lugares";
    let contenido = form;
    let funcionGuardar = funcionGuardarLugares;
    let usuario = rolLugares;

    createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarLugares(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposLugares, datosExtra);

    let clase = "Lugares";

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    } else {
        funcion = "crear";
    }

    await queryAsync(clase, funcion, datos);

    getLugares();
    destruirModal();
}
