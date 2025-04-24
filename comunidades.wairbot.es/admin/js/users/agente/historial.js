/*
BACKEND
Clase: historial
Metodos: 
    listar
        datos: nPagina, search, filtro

    verUno
        datos: id, codigoBotId
*/

const camposHistorial = [
    {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
        bloquear: true,
    },{
        tipo: "text",
        id: "direccion",
        label: "Dirección",
        bloquear: true,
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
        bloquear: true,
    },{
        tipo: "textarea",
        id: "dato",
        label: "Dato",
        bloquear: true,
    },{
        tipo: "textarea",
        id: "respuesta",
        label: "Respuesta",
        bloquear: true,
    }
];

const claseHistorial = "Historial";
const rolHistorial = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getHistorial(nPagina = 1) {
    let location = "Historial";
    let clase = claseHistorial;
    let funcionLeer = "listar";//php
    let funcionAbrir = "openHistorial";//js
    let funcionCrear = null;//js
    let rol = rolHistorial;
    let titulo = "Historial";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getHistorial",
    }
    let camposCard = {
        info1: "nombre",
        info2: "direccion",
        info3: "telefono",
        info4: "dato",
        info5: "respuesta",
        id: "id",
        oculto: "source_table"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openHistorial(id) {
    let idOriginal = id;

    //obtener la card con el id="card_${id}" y obtener el texto de card_info_4 para obtener el codigo de la incidencia
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_oculto")[0].innerText;

    //eliminar historial_formularios_ de codigoBot
    codigoBot = codigoBot.replace("historial_formularios_", "");

    // OBTENER DATOS //
    let clase = claseHistorial;
    let funcion = 'verUno';
    let datos = {
        id: id,
        codigoBotId: codigoBot
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    //si algún valor de data es null o undefined, se le asigna un string vacío
    for (let key in data) {
        if (data[key] === null || data[key] === undefined) {
            data[key] = "";
        }
    }

    let campos = crearCamposDeFormulario(camposHistorial, data, null);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Historial de " + codigoBot;
    let contenido = form;
    let funcionGuardar = "guardarHistorial";
    let usuario = rolHistorial;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //bloquear los campos
    camposHistorial.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}