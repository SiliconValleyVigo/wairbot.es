const camposIncidenciasCerradas = [
    {
        tipo: "text",
        id: "lugar",
        label: "Lugar",
        bloquear:true,
    },{
        tipo: "text",
        id: "descripcion",
        label: "Descripción",
        bloquear:true,
    },{
        tipo: "text",
        id: "tipo",
        label: "Tipo",
        bloquear:true,
    },{
        tipo: "text",
        id: "codigo",
        label: "Código de Incidencia",
        bloquear:true,
    },{
        tipo: "phone_link",
        id: "usuario",
        label: "Contacto Usuario",
        bloquear:true,
    },{
        tipo: "phone_link",
        id: "tecnico",
        label: "Contacto Técnico",
        bloquear:true,
    },{
        tipo: "textarea",
        id: "acciones",
        label: "Acciones",
        bloquear:true,
    },{
        tipo: "file",
        id: "imagen",
        label: "Imagen",
        bloquear:true,
    },{
        tipo: "text",
        id: "reg_date",
        label: "Fecha de Registro",
        bloquear:true,
    }
];

const claseIncidenciasCerradas = "IncidenciasCerradas";
const rolIncidenciasCerradas = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getIncidenciasCerradas(nPagina = 1) {
    let location = "IncienciasCerradas";
    let clase = claseIncidenciasCerradas;
    let funcionLeer = "listar";//php
    let funcionAbrir = "openIncidenciasCerradas";//js
    let funcionCrear = null;//js
    let rol = rolIncidenciasCerradas;
    let titulo = "Incidencias Cerradas";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getIncidenciasCerradas",
    }
    let camposCard = {
        info1: "referencia_Usuario",
        info2: "lugar",
        info3: "tipo",
        info4: "reg_date",
        info5: "tecnico",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}


///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openIncidenciasCerradas(id) {
    let idOriginal = id;

    //obtener la card con el id="card_${id}" y obtener el texto de card_info_4 para obtener el codigo de la incidencia
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_1")[0].innerText;

    // OBTENER DATOS //
    let clase = claseIncidenciasCerradas;
    let funcion = 'verUno';
    let datos = {
        id: id,
        codigoBotId: codigoBot
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    //Crear el formulario
    let botonCerrar = {
        texto: "Cerrar Incidencia",
        funcion: "modalCerrarIncidenciaCerrada",
        id: idOriginal
    }

    //si algún valor de data es null o undefined, se le asigna un string vacío
    for (let key in data) {
        if (data[key] === null || data[key] === undefined) {
            data[key] = "";
        }
    }

    let campos = crearCamposDeFormulario(camposIncidenciasCerradas, data, botonCerrar);
    let form = createForm(campos, idOriginal);

    let fichaTecnico = "";
    if (data.datosTecnico) {
        let tecnico = data.datosTecnico;
        fichaTecnico = `
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

    //Obtener el modal
    let titulo = "Incidencia en " + codigoBot;
    let contenido = form + fichasContainer;
    let funcionGuardar = "guardarIncidenciaCerrada";
    let usuario = rolIncidenciasCerradas;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    camposIncidenciasCerradas.forEach(c => {
        const element = document.getElementById(c.id);
        if (element && c.bloquear) {
            element.disabled = true;
        }
    });
}