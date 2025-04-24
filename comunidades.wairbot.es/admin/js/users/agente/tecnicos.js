//tipo, actividad, nombre, referencia, telefono
const camposTecnicos = [
    {
        tipo: "text",
        id: "tipo",
        label: "Tipo",
        bloquear: true
    },{
        tipo: "text",
        id: "actividad",
        label: "Actividad",
        bloquear: true
    },{
        tipo: "text",
        id: "nombre",
        label: "Nombre",
        bloquear: true
    },{
        tipo: "text",
        id: "referencia",
        label: "Referencia",
        bloquear: true
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
        bloquear: true
    }
];

const claseTecnicos = "Tecnicos";
const funcionGuardarTecnicos = "guardarTecnicos";
const rolTecnicos = "AGENTE";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getTecnicos(nPagina = 1) {
    let location = "Tecnicos";
    let clase = "Tecnicos"; //php
    let funcionLeer = "listar"; //php
    let funcionAbrir = "openTecnicos"; //js
    let funcionCrear = "crearTecnicos"; //js
    let rol = rolTecnicos;
    let titulo = "Tecnicos";
    let hayPaginacion = true;
    let filtro = {
        localStorage: "comunidades",
        getGrid: "getTecnicos",
    }
    let camposCard = {
        info1: "nombre",
        info2: "tipo",
        info3: "referencia",
        info4: "telefono",
        info5: "referencia_Usuario",
        id: "id",
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, filtro);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarTecnicos(id) {
    let titulo = "Eliminar Tecnicos";
    let mensaje = "¿Estas Seguro de eliminar este lugar? <br> <br> <strong>Esta acción no se puede deshacer</strong>";
    let funcionAceptar = "eliminarTecnicos";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarTecnicos(id) {
    let card = document.getElementById(`card_${id}`);
    let codigoBot = card.getElementsByClassName("card_info_5")[0].innerText;
    let telefonoTecnico = card.getElementsByClassName("card_info_4")[0].innerText;
    
    let clase = claseTecnicos
    let funcion = "borrar"; //codigoBot, telefonoTecnico
    let datos = {
        id: id,
        codigoBot: codigoBot,
        telefonoTecnico: telefonoTecnico
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if (data.status === "success") {
        getTecnicos();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Técnico Eliminado";
        let mensaje = "El Técnico ha sido eliminado correctamente";
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
async function openTecnicos(id) {
    let idOriginal = id;

    let codigoBotId = document.getElementById(`card_${id}`).getElementsByClassName("card_info_5")[0].innerText;
    let telefonoTecnico = document.getElementById(`card_${id}`).getElementsByClassName("card_info_4")[0].innerText;

    // OBTENER DATOS //
    let clase = claseTecnicos;
    let funcion = "verUno"; //id, codigoBot
    let datos = {
        telefonoTecnico: telefonoTecnico,
        codigoBotId: codigoBotId,
        id: id,
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarTecnicos",
        id: idOriginal,
    };

    let campos = crearCamposDeFormulario(camposTecnicos, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    let comunidadesTecnico = createComunidadesTecnico(data.comunidades, idOriginal);

    //Obtener el modal
    let titulo = "Editar Tecnicos";
    let contenido = form + comunidadesTecnico;
    let funcionGuardar = funcionGuardarTecnicos;
    let usuario = rolTecnicos;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposTecnicos tiene un campo bloquear y es true, bloquear el campo
    camposTecnicos.forEach((c) => {
        if (c.bloquear) {
            document.getElementById(c.id).disabled = true;
        }
    });

    //eliminar el elemento con la clase modalBoton donde el texto sea Guardar
    let botonGuardar = document.getElementsByClassName("modalBoton");
    for (let i = 0; i < botonGuardar.length; i++) {
        if (botonGuardar[i].innerText === "Guardar") {
            botonGuardar[i].remove();
        }
    }
}

///////////////////////////////////////////////// COMUNIDADES DEL TÉCNICO /////////////////////////////////////////////////
function createComunidadesTecnico(dataComunidades, id) {
    let comunidadTecnico = "";
    dataComunidades.forEach((comunidad) => {
        // por cada comunidad un check para idenficar si es auxiliar o no y otro check para activo o no
        let auxiliar = "";
        let activo = "";
        let disabledAuxiliar = "";

        if (comunidad.esAuxiliar) {
            auxiliar = "checked";
        }
        if (comunidad.activo) {
            activo = "checked";
        } else {
            disabledAuxiliar = "disabled";
        }

        comunidadTecnico += `
            <div class="comunidadTecnico">
                <div class="comunidadTecnicoNombre">${comunidad.codigo_bot}</div>
                <div class="checkBoxGroup comunidadTecnicoAuxiliar">
                    <input type="checkbox" id="auxiliar_${comunidad.codigo_bot}" class="comunidadTecnicoInput" ${auxiliar} ${disabledAuxiliar}>
                    <label for="auxiliar">Auxiliar</label>
                </div>
                <div class="checkBoxGroup comunidadTecnicoActivo">
                    <input type="checkbox" id="activo_${comunidad.codigo_bot}" class="comunidadTecnicoInput" ${activo} onchange="toggleAuxiliar('${comunidad.codigo_bot}')">
                    <label for="activo">Activo</label>
                </div>
            </div>
        `;
    });

    let html = `
        <div class="comunidadesTecnicoContainer">
            <h3>Comunidades</h3>
            <div class="comunidadesTecnico">
                ${comunidadTecnico}
            </div>

            <button class="botonActualizarComunidadesTecnico" onclick="actualizarComunidadesTecnico(${id})">Actualizar Comunidades</button>
        </div>        
   `;

    return html;
}

function toggleAuxiliar(codigo_bot) {
    const activoCheckbox = document.getElementById(`activo_${codigo_bot}`);
    const auxiliarCheckbox = document.getElementById(`auxiliar_${codigo_bot}`);
    if (activoCheckbox.checked) {
        auxiliarCheckbox.disabled = false;
    } else {
        auxiliarCheckbox.disabled = true;
        auxiliarCheckbox.checked = false;
    }
}

async function actualizarComunidadesTecnico(id) {
    //obtener todos los elementos con clase comunidadTecnicoInput
    let elementos = document.getElementsByClassName("comunidadTecnicoInput");
    
    //obtener una lista de codigos de comunidades
    let codigos = [];
    for (let i = 0; i < elementos.length; i++) {
        let id = elementos[i].id;
        let codigo = id.split("_")[1];
        codigos.push(codigo);
    }

    //eliminar duplicados
    codigos = [...new Set(codigos)];

    //crear dataComunidades comprobando si es auxiliar y si está activo
    let dataComunidades = [];
    codigos.forEach((codigo) => {
        let auxiliar = document.getElementById(`auxiliar_${codigo}`).checked;
        let activo = document.getElementById(`activo_${codigo}`).checked;

        let comunidad = {
            codigo_bot: codigo,
            esAuxiliar: auxiliar,
            activo: activo
        };

        dataComunidades.push(comunidad);
    });

    //obtener dataTecnico
    let tipo = document.getElementById("tipo").value;
    let actividad = document.getElementById("actividad").value;
    let nombre = document.getElementById("nombre").value;
    let referencia = document.getElementById("referencia").value;
    let telefono = document.getElementById("telefono").value;

    let dataTecnico = {
        tipo: tipo,
        actividad: actividad,
        nombre: nombre,
        referencia: referencia,
        telefono: telefono
    };

    let data = {
        dataComunidades: dataComunidades,
        dataTecnico: dataTecnico,
        telefonoTecnico: telefono
    };

    let clase = claseTecnicos;
    let funcion = "asignarComunidades";
    let datos = data;
    let dataResponse = await queryAsync(clase, funcion, datos);

    //si es success mostrar modal de éxito
    if (dataResponse.status === "success") {
        //crear modal aviso de éxito
        let titulo = "Comunidades Actualizadas";
        let mensaje = "Las comunidades del Técnico han sido actualizadas correctamente";
        let funcionAceptar = "openTecnicosModal";
        let arrayParametros = [id];

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible actualizar las comunidades del Técnico";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

function openTecnicosModal(id) {
    destruirModalAviso();
    destruirModal();
    openTecnicos(id);
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearTecnicos() {
    //obtener selectFiltro y si está vacio mostrar modal de aviso
    let selectFiltro = document.getElementById("selectFiltro");
    if (selectFiltro.value === "") {
        let titulo = "Error";
        let mensaje = "Debes seleccionar una Comunidad antes de crear un Técnico";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
        return;
    }

    let campos = crearCamposDeFormulario(camposTecnicos);
    let form = createForm(campos, "");

    let titulo = "Crear Tecnicos";
    let contenido = form;
    let funcionGuardar = funcionGuardarTecnicos;
    let usuario = rolTecnicos;

    createModal(titulo, contenido, funcionGuardar, usuario);

    //obtener un input data list getSimpleDataList(id, label, options, value) para tipo

    //obtener las options
    let clase = "Tecnicos";
    let funcion = "listarTipos";

    let dataTipos = await queryAsync(clase, funcion, {});
    console.log(dataTipos);

    let options = dataTipos.data;
    
    let optionsHtml = "";
    options.forEach((option) => {
        optionsHtml += `<option value="${option}">`;
    });

    let inputTipo = document.getElementById("tipo");

    id = "tipo";
    label = "Tipo";
    options = optionsHtml;
    value = inputTipo.value;

    let dataListTipo = getSimpleDataList(id, label, options, value);

    //susituir el input tipo por el input dataListTipo
    
    //obtener el padre del inputTipo
    let padre = inputTipo.parentNode;

    //cambiar html del padre
    padre.innerHTML = dataListTipo;
}

async function guardarTecnicos(id = 0) {
    let datosExtra = {
    };

    let datos = getDatosFormulario(camposTecnicos, datosExtra);

    let clase = "Tecnicos";

    let funcion = "";
    if (id !== 0) {
        datos.id = id;
        funcion = "editar";
    }else{
        funcion = "crear"; //data, codigoBot
    }

    await queryAsync(clase, funcion, datos);

    getTecnicos();
    destruirModal();
}