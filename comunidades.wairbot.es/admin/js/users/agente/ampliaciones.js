/*
BACKEND:
Clase: AmpliacionesPendientes
Funciones:
- listar
     datos: nPagina, search, filtro
- verUna
        datos: id, codigoBotId
- aceptar
        datos: id, codigoBotId, data
        data: texto
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
const camposAmpliaciones = [
    {
        tipo: "textarea",
        id: "texto",
        label: "Ampliación de Incidencia",
        bloquear: true,
    }, {
        tipo: "textarea",
        id: "respuesta",
        label: "Respuesta Personalizada",
        bloquear: false,
    }
];

const claseAmpliaciones = "AmpliacionesPendientes";
const rolAmpliaciones = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAmpliaciones(nPagina = 1) {
    let location = "Ampliaciones";
    let clase = claseAmpliaciones;
    let funcionLeer = "listar";//php
    let funcionAbrir = "openAmpliaciones";//js
    let funcionCrear = null;//js
    let rol = rolAmpliaciones;
    let titulo = "Ampliaciones Pendientes";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getAmpliaciones",
    }
    let camposCard = {
        info1: "texto",
        info5: "referencia_Usuario",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);

}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openAmpliaciones(id) {
    let idOriginal = id;

    //obtener la card con el id="card_${id}" y obtener el texto de card_info_4 para obtener el codigo de la incidencia
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_5")[0].innerText;

    // OBTENER DATOS //
    let clase = claseAmpliaciones;
    let funcion = 'verUno';
    let datos = {
        id: id,
        codigoBotId: codigoBot 
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    //Crear el formulario
    let botonCerrar = {
        texto: "Aceptar Ampliación",
        funcion: "aceptarAmpliaciones",
        id: idOriginal
    }

    //si algún valor de data es null o undefined, se le asigna un string vacío
    for (let key in data) {
        if (data[key] === null || data[key] === undefined) {
            data[key] = "";
        }
    }

    let campos = crearCamposDeFormulario(camposAmpliaciones, data, botonCerrar);
    let form = createForm(campos, idOriginal);

    let fichaTecnico = "";
    if (data.tecnico) {
        let tecnico = data.tecnico;
        fichaTecnico = `
            <style>
                .ficha {
                    margin-bottom: 10px;
                }
            </style>
            <div class="ficha">
                <div class="fichaTitulo">Tecnico</div>
                <div class="fichaContenido">
                    <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${tecnico.nombre}</div>
                    <div class="fichaCampo"><strong>Referencia:&nbsp;</strong> ${tecnico.referencia}</div>
                    <div class="fichaCampo"><strong>Tipo:&nbsp;</strong> ${tecnico.tipo}</div>
                    <div class="fichaCampo"><strong>Actividad:&nbsp;</strong> ${tecnico.actividad}</div>
                    <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> <a href="tel:${tecnico.telefono}">${tecnico.telefono}</a></div>
                    <div class="fichaCampo"><strong>Whatsapp:&nbsp;</strong> <a href="https://wa.me/${tecnico.telefono}">${tecnico.telefono}</a></div>
                </div>
            </div>
        `;
    }

    let fichasContainer = `
          <div class="fichasContainer">
              ${fichaTecnico}
          </div>
      `;

    let texto = data.texto;

    texto = encodeURIComponent(texto);

    let formEnviarMensaje = `
        <style>
            .modalBoton {
                margin-bottom: 10px;
            }
        </style>
        <div class="modalBoton" onclick="aceptarAmpliaciones(${id}, '${texto}', 'ACEPTAR')">Aceptar Ampliación</div>
        <div class="modalBoton" onclick="aceptarAmpliaciones(${id}, '${texto}', 'RECHAZAR')">Rechazar Ampliación</div>
        <div class="modalBoton" onclick="aceptarAmpliaciones(${id}, '${texto}', 'PERSONALIZADO')">Enviar Mensaje Personalizado</div>
    `;

    //Obtener el modal
    let titulo = "Ampliación de Incidencia";
    let contenido = form + fichasContainer + formEnviarMensaje;
    let funcionGuardar = "guardarAmpliaciones";
    let usuario = rolAmpliaciones;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

async function aceptarAmpliaciones(id, texto, tipo) {
    //obtener codigoBotId
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_5")[0].innerText;

    texto = decodeURIComponent(texto);

    //obtener los ids nombre, direccion, dato (inputs), respuesta (textarea), telefono_usuario (phone_link)
    let respuesta = document.getElementById("respuesta");
    respuesta = respuesta.value;

    if (tipo === "ACEPTAR") {
        respuesta = "Su petición de Ampliación de Incidencia ha sido *ACEPTADA* por el administrador: \n" + respuesta + "\n\nAMPLIACIÓN:\n" + texto;
    } else if (tipo === "RECHAZAR") {
        respuesta = "Su petición de Ampliación de Incidencia ha sido *RECHAZADA* por el administrador: " + respuesta + "\n\nAMPLIACIÓN:\n" + texto;
    } else if (tipo === "PERSONALIZADO") {
        respuesta = "Mensaje sobre la ampliación de incidencia: " + respuesta + "\n\nAMPLIACIÓN:\n" + texto;
    }

    let clase = claseAmpliaciones;
    let funcion = "aceptar";
    let datos = {
        id: id,
        data: {
            texto: respuesta,
            codigoBotId: codigoBot
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
    getAmpliaciones();
}