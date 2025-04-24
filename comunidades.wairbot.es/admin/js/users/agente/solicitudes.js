
/*
BACKEND:
Clase: ResultadosFormularios
Metodos:
    - Listar($request)
        datos: nPagina, search, filtro

    - verUno($request)
        datos: id, codigoBotId, sourceTable

    - Responder($request)
        datos: id, codigoBotId, sourceTable, data
        data = {
            nombre: "",
            direccion: "",
            dato: "",
            respuesta: "",
            telefono_usuario: ""
        }

    - Borrar($request)
        datos: id, codigoBotId, sourceTable

campos = [{
    tipo: "dataList",
    id: "prefijo",
    label: "Codigo Telefónico Internacional",
    options: optionPrefix,
    defaultValue: "34",
    bloquear: true,
}...]
*/

const camposSolicitudes = [
    {
        tipo: "text",
        id: "referencia_Usuario",
        label: "Codigo de Bot",
        bloquear: true,
    }, {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
        bloquear: true,
    }, {
        tipo: "text",
        id: "direccion",
        label: "Dirección",
        bloquear: true,
    }, {
        tipo: "text",
        id: "source_table",
        label: "Tabla",
        bloquear: true,
    }, {
        tipo: "text",
        id: "dato",
        label: "Dato",
        bloquear: true,
    }, {
        tipo: "textarea",
        id: "respuesta",
        label: "Respuesta Personalizada",
        bloquear: false,
    }, {
        tipo: "phone_link",
        id: "telefono_usuario",
        label: "Contacto Usuario",
        bloquear: true,
    
    }

];

const claseSolicitudes = "ResultadosFormularios";
const rolSolicitudes = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getSolicitudes(nPagina = 1) {
    let location = "Solicitudes";
    let clase = claseSolicitudes;
    let funcionLeer = "listar";//php
    let funcionAbrir = "openSolicitudes";//js
    let funcionCrear = null;//js
    let rol = rolSolicitudes;
    let titulo = "Solicitudes";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getSolicitudes",
    }
    let camposCard = {
        info1: "referencia_Usuario",
        info2: "nombre",
        info3: "direccion",
        info4: "source_table",
        info5: "dato",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);

}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarSolicitudes(id) {
    let titulo = "Guardar Solicitud en Historial";
    let mensaje = "Se guardará la solicitud en HISTORIAL  <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarSolicitudes";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarSolicitudes(id) {
    //obtener el codigoBotId de la card
    let card = document.getElementById(`card_${id}`);
    let codigoBotId = card.getElementsByClassName("card_info_1")[0].innerText;
    let sourceTable = card.getElementsByClassName("card_info_4")[0].innerText;

    //guardar la solicitud en historial
    let clase = claseSolicitudes;
    let funcion = 'Borrar';
    let datos = {
        id: id,
        codigoBotId: codigoBotId,
        sourceTable: sourceTable
    };

    let data = await queryAsync(clase, funcion, datos);

    if (data.status === "success") {
        getSolicitudes();
        destruirModalAviso();

        //crear modal aviso de éxito
        let titulo = "Solicitud Guardada";
        let mensaje = "La solicitud ha sido guardada en HISTORIAL correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';

}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openSolicitudes(id) {
    let idOriginal = id;

    //obtener la card con el id="card_${id}" y obtener el texto de card_info_4 para obtener el codigo de la incidencia
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_1")[0].innerText;
    let sourceTable = card.getElementsByClassName("card_info_4")[0].innerText;

    // OBTENER DATOS //
    let clase = claseSolicitudes;
    let funcion = 'verUno';
    let datos = {
        id: id,
        codigoBotId: codigoBot,
        sourceTable: sourceTable
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    //Crear el formulario
    let botonCerrar = {
        texto: "Guardar en Historial",
        funcion: "modalEliminarSolicitudes",
        id: idOriginal
    }

    //si algún valor de data es null o undefined, se le asigna un string vacío
    for (let key in data) {
        if (data[key] === null || data[key] === undefined) {
            data[key] = "";
        }
    }

    let campos = crearCamposDeFormulario(camposSolicitudes, data, botonCerrar);
    let form = createForm(campos, idOriginal);

    let formEnviarMensaje = `
        <style>
            .modalBoton {
                margin-bottom: 10px;
            }
        </style>
        <div class="modalBoton" onclick="enviarRespuesta(${id}, '${codigoBot}', '${sourceTable}', 'ACEPTAR')">Aceptar Petición</div>
        <div class="modalBoton" onclick="enviarRespuesta(${id}, '${codigoBot}', '${sourceTable}', 'RECHAZAR')">Rechazar Petición</div>
        <div class="modalBoton" onclick="enviarRespuesta(${id}, '${codigoBot}', '${sourceTable}', 'PERSONALIZADO')">Enviar Mensaje Personalizado</div>
    `;

    //Obtener el modal
    let titulo = "Solicitud de " + codigoBot;
    let contenido = form + formEnviarMensaje;
    let funcionGuardar = "guardarSolicitudes";
    let usuario = rolSolicitudes;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

/*
datos: id, codigoBotId, sourceTable, data
        data = {
            nombre: "",
            direccion: "",
            dato: "",
            respuesta: "",
            telefono_usuario: ""
        }
*/
async function enviarRespuesta(id, codigoBot, sourceTable, tipo) {
    //obtener los ids nombre, direccion, dato (inputs), respuesta (textarea), telefono_usuario (phone_link)
    let nombre = document.getElementById("nombre").value;
    let direccion = document.getElementById("direccion").value;
    let dato = document.getElementById("dato").value;

    //respuesta es un textarea
    let respuesta = document.getElementById("respuesta");
    respuesta = respuesta.value;

    console.log(respuesta);

    if (tipo === "ACEPTAR") {
        respuesta = "Su petición ha sido aceptada: " + respuesta;
    } else if (tipo === "RECHAZAR") {
        respuesta = "Su petición ha sido rechazada: " + respuesta;
    }

    let telefono_usuario = document.getElementById("telefono_usuario");
    telefono_usuario = telefono_usuario.href;
    telefono_usuario = telefono_usuario.replace(/\D/g, "");

    let clase = claseSolicitudes;
    let funcion = "Responder";
    let datos = {
        id: id,
        codigoBotId: codigoBot,
        sourceTable: sourceTable,
        data: {
            nombre: nombre,
            direccion: direccion,
            dato: dato,
            respuesta: respuesta,
            telefono_usuario: telefono_usuario
        }
    };

    let data = await queryAsync(clase, funcion, datos);

    //abrir un modal de mensaje enviado
    let titulo = "Mensaje Enviado";
    let mensaje = "El mensaje ha sido enviado correctamente";
    let funcionAceptar = "destruirModalAviso";
    let arrayParametros = null;

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);

    //cerrar el modal
    destruirModal();

    //actualizar la grid
    getSolicitudes();
}