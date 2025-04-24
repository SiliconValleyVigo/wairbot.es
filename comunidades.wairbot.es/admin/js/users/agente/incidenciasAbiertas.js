/*
lugar
descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas
tipo
imagen
reg_date
codigo
usuario
tecnico
acciones
*/
const camposIncidenciasAbiertas = [
    {
        tipo: "text",
        id: "lugar",
        label: "Lugar",
        bloquear:true,
    },{
        tipo: "text",
        id: "descripcion_plugin_ComunidadesCheckSelectorIncidenciasAbiertas",
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

const rolIncidenciasAbiertas = "AGENTE";

async function getIncidenciasAbiertas(nPagina = 1) {
    let location = "IncienciasAbiertas";
    let clase = "IncidenciasAbiertas";//php
    let funcionLeer = "listar";//php
    let funcionAbrir = "openIncidenciaAbierta";//js
    let funcionCrear = null;//js
    let rol = rolIncidenciasAbiertas;
    let titulo = "Incidencias Abiertas";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getIncidenciasAbiertas",
    }
    let camposCard = {
        info1: "referencia_Usuario",
        info2: "lugar",
        info3: "reg_date",
        info4: "codigo",
        info5: "tecnico",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

async function openIncidenciaAbierta(id) {
    let idOriginal = id;

    //obtener la card con el id="card_${id}" y obtener el texto de card_info_4 para obtener el codigo de la incidencia
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_1")[0].innerText;

    // OBTENER DATOS //
    let clase = 'IncidenciasAbiertas';
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
        funcion: "modalCerrarIncidenciaAbierta",
        id: idOriginal
    }

    //si algún valor de data es null o undefined, se le asigna un string vacío
    for (let key in data) {
        if (data[key] === null || data[key] === undefined) {
            data[key] = "";
        }
    }

    let campos = crearCamposDeFormulario(camposIncidenciasAbiertas, data, botonCerrar);
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
    let funcionGuardar = "guardarIncidenciaAbierta";
    let usuario = rolIncidenciasAbiertas;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    camposIncidenciasAbiertas.forEach(c => {
        const element = document.getElementById(c.id);
        if (element && c.bloquear) {
            element.disabled = true;
        }
    });
}

async function modalCerrarIncidenciaAbierta(id){
    let titulo = "Cerrar Incidencia";
    let mensaje = "¿Estás seguro de que quieres cerrar esta incidencia? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "cerrarIncidenciaAbierta";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function cerrarIncidenciaAbierta(id) {
    console.log(id);
    let card = document.getElementById(`card_${id}`);
    console.log(card);
    let titulo = document.getElementById(id).innerText;
    console.log(titulo);
    //codigo bot es el titulo sin "Incidencia en "
    let codigoBot = titulo.replace("Incidencia en ", "");
    let codigoIncidencia = card.getElementsByClassName("card_info_4")[0].innerText;

    let clase = 'IncidenciasAbiertas';
    let funcion = 'cerrar';
    let datos = {
        codigoBot2: codigoBot,
        codigoIncidencia: codigoIncidencia
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getIncidenciasAbiertas();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Incidencia Cerrada";
        let mensaje = "La incidencia ha sido cerrada correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);

    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible cerrar la incidencia";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

async function guardarIncidenciaAbierta(id = 0){
    getTecnicos();
    destruirModal();
}