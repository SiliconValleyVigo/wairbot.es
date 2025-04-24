/*
BACKEND:
CLASE: FormulariosPersonalizados
METODOS: listar, crear, verUno, borrar

LISTAR: nPagina, search, filtro, codigoBot
CREAR: data, codigoBot - DATA: titulo, pregunta, tipoRespuesta
VERUNO: id, codigoBot
BORRAR: id, codigoBot

*/
/*

campos = [{
    tipo: text, textarea, select, datalist
    id: string
    label: string
    options: array
    defaultValue: string
    bloquear: bol
}...]
*/
//email, password, prefijo, telefono, codigo_bot, tiempo, idioma
const camposFormularios = [
    {
        tipo: "text",
        id: "titulo",
        label: "Título",
        defaultValue: "",
    },
    {
        tipo: "text",
        id: "pregunta",
        label: "Pregunta",
        defaultValue: "",
    },
    {
        tipo: "select",
        id: "tipoRespuesta",
        label: "Tipo de Respuesta",
        options: {
            "aceptar_rechazar": "Aceptar o Rechazar",
            "texto": "Texto Personalizado",
        },
        defaultValue: "texto",
    },
];

const claseFormularios = "FormulariosPersonalizados";
const funcionGuardarFormularios = "guardarFormularios";
const rolFormularios = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getFormularios(nPagina = 1) {
    let location = "Formularios";
    let clase = claseFormularios;
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openFormularios"; //js
    let funcionCrear = "crearFormularios"; //js
    let rol = rolFormularios;
    let titulo = "Formularios";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getFormularios",
    }
    let camposCard = {
        info1: "title",
        info2: "",
        info3: "",
        info4: "id",
        info5: "admin_phone",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarFormularios(id) {
    let titulo = "Eliminar Formularios";
    let mensaje = "¿Estás seguro de eliminar este formulario? <br> <br> <strong>Se eliminará el formulario y las respuestas</strong>";
    let funcionAceptar = "eliminarFormularios";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarFormularios(id) {
    let clase = claseFormularios

    let card = document.getElementById(`card_${id}`);
    let codigoBotId = card.getElementsByClassName("card_info_5")[0].innerText;


    let funcion = "borrar";
    let datos = {
        id: id,
        codigoBotId: codigoBotId,
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if (data.status === "success") {
        getFormularios();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Formulario Eliminado";
        let mensaje = "El Formularios ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar la Formularios pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openFormularios(id) {
    let idOriginal = id;

    //obtener el codigoBot de la card
    let card = document.getElementById(`card_${id}`);
    let codigoBotId = card.getElementsByClassName("card_info_5")[0].innerText;

    // OBTENER DATOS //
    let clase = claseFormularios;
    let funcion = "verUno";
    let datos = {
        id: id,
        codigoBotId: codigoBotId,
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarFormularios",
        id: idOriginal,
    };

    let campos = crearCamposDeFormulario([], {}, botonEliminar);
    let form = createForm(campos, idOriginal);

    let fields = JSON.parse(data.fields);
    let pregunta = "";
    let tipoPregunta = "";

    for (let field of fields) {
        //el tipo de pregunta es el name_column que no es nombre ni direccion
        if (field.name_column !== "nombre" && field.name_column !== "direccion") {
            tipoPregunta = field.name_column;
            pregunta = field.label;
            break;
        }
    }

    //si tipo de pregunta es res_a_, disponibilidad o sugerencia, tipoPregunta es texto
    if (tipoPregunta === "res_a_" || tipoPregunta === "disponibilidad" || tipoPregunta === "sugerencia") {
        tipoPregunta = "Texto Personalizado";
    }

    //si tipo de pregunta es res_b_, tipoPregunta es aceptar_rechazar
    if (tipoPregunta === "res_b_") {
        tipoPregunta = "Aceptar o Rechazar";
    }

    let tituloForm = data.title;

    //cambiar _ por espacio y la primera letra a mayúscula
    tituloForm = tituloForm.replace(/_/g, " ");
    tituloForm = tituloForm.charAt(0).toUpperCase() + tituloForm.slice(1);

    let fichaFormulario = `
        <div class="ficha">
            <div class="fichaTitulo">Formulario</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Titulo:&nbsp;</strong>${tituloForm}</div>
                <div class="fichaCampo"><strong>Pregunta:&nbsp;</strong>${pregunta}</div>
                <div class="fichaCampo"><strong>Tipo de Respuesta:&nbsp;</strong>${tipoPregunta}</div>
            </div>
        </div>
    `;

    let fichasContainer = `
          <div class="fichasContainer">
              ${fichaFormulario}
          </div>
      `;

    //Obtener el modal
    let titulo = "Editar Formularios";
    let contenido = fichasContainer + form;
    let funcionGuardar = funcionGuardarFormularios;
    let usuario = rolFormularios;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposFormularios tiene un campo bloquear y es true, bloquear el campo
    camposFormularios.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearFormularios() {
    //obtener selectFiltro y si está vacio mostrar modal de aviso
    let selectFiltro = document.getElementById("selectFiltro");
    if (selectFiltro.value === "") {
        let titulo = "Error";
        let mensaje = "Debes seleccionar una Formularios";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
        return;
    }

    let campos = crearCamposDeFormulario(camposFormularios);
    let form = createForm(campos, "");

    let titulo = "Crear Formularios";
    let contenido = form;
    let funcionGuardar = funcionGuardarFormularios;
    let usuario = rolFormularios;

    createModal(titulo, contenido, funcionGuardar, usuario);

    //impedir que en titulo se pueda escribir: incidencia, _in_tec_, tecnico, lugares
    //impedir que en titulo se puedan escribir cualquier caracter que no sea letra o número
    //impedir que el titulo ocupe más de 40 caracteres
    let inputTitulo = document.getElementById("titulo");
    inputTitulo.addEventListener("input", () => {
        let value = inputTitulo.value;
        let newValue = value.replace(/[^a-zA-Z0-9]/g, "");
        newValue = newValue.substring(0, 40);
        inputTitulo.value = newValue;
    });
}

async function guardarFormularios(id = 0) {
    //obtener id_agente del usuario
    let usuario = JSON.parse(localStorage.getItem("usuario"));
    let id_agente = usuario.id;

    let datosExtra = {
        id_agente: id_agente,
    };

    let datos = getDatosFormulario(camposFormularios, datosExtra);

    let clase = claseFormularios;

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "modificar";
    } else {
        console.log(datos);
        datos.data.titulo = datos.data.titulo.replace(/ /g, "_");
        let tipoRespuesta = "";
        if (datos.data.tipoRespuesta === "Texto Personalizado") {
            tipoRespuesta = "res_a_";
        } else {
            tipoRespuesta = "res_b_";
        }

        datos.data.tipoRespuesta = tipoRespuesta;

        funcion = "crear"; //CREAR: data, codigoBot - DATA: titulo, pregunta, tipoRespuesta
    }

    await queryAsync(clase, funcion, datos);

    getFormularios();
    destruirModal();
}
