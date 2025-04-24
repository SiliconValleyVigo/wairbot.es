//email, password, prefijo, telefono, codigo_bot, tiempo, idioma
const camposComunidades = [
    {
        tipo: "dataList",
        id: "prefijo",
        label: "Codigo Telefónico Internacional",
        options: optionPrefix,
        defaultValue: "34",
        bloquear:true,
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
        bloquear:true,
    },{
        tipo: "text",
        id: "codigo_bot",
        label: "Código Bot",
        bloquear:true,
    },{
        tipo: "text",
        id: "tiempo",
        label: "Tiempo de Contestacion (en horas)",
        defaultValue: "24",
    },{
        tipo: "dataList",
        id: "idioma",
        label: "Idioma del Bot",
        options: optionLang,
        defaultValue: "es",
        bloquear:true,
    }
];

const funcionGuardarComunidades = "guardarComunidades";
const rolComunidades = "AGENTE";


///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getComunidades(nPagina = 1) {
    let location = "Comunidades";
    let clase = "Comunidades";//php
    let funcionLeer = "listar";//php
    let funcionAbrir = "openComunidades";//js
    let funcionCrear = "crearComunidades";//js
    let rol = rolComunidades;
    let titulo = "Comunidades";
    let hayPaginacion = true;
    let camposCard = {
        info1: "codigo_bot",
        info2: "",
        info3: "telefono",
        info4: "tiempo",
        info5: "idioma",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarComunidades(id) {
    let titulo = "Eliminar Comunidad";
    let mensaje = "¿Estás seguro de que quieres eliminar esta comunidad? <br> <br> <strong>Se Eliminarán todos los datos asociados a esta comunidad</strong>";
    let funcionAceptar = "eliminarComunidades";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros); 
}

async function eliminarComunidades(id) {
    let clase = 'Comunidades';
    let funcion = 'eliminar';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getComunidades();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Comunidad Eliminada";
        let mensaje = "La comunidad ha sido eliminada correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar la comunidad pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openComunidades(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'Comunidades';
    let funcion = 'verUno';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarComunidades",
        id: idOriginal
    }

    let campos = crearCamposDeFormulario(camposComunidades, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //url whatsapp con el texto data.codigo_bot
    let url = `https://wa.me/34886080849?text=${data.codigo_bot}`;

    let formEnviarMensaje = `
        <style>
            .modalBoton {
                margin-bottom: 10px;
            }
        </style>
        <div class="modalBoton" onclick="generarYDescargarQR('${url}')">Descargar Código QR</div>
        <a href="${url}" target="_blank" class="modalBoton">Iniciar el Bot en Whatsapp</a>
    `;

    //grid de tecnicos por comunidad
    let tecnicos = data.tecnicos;
    let id_comunidad = data.id;
    let codigo_bot = data.codigo_bot;
    let gridTecnicos = gridTecnicosPorComunidad(tecnicos, id_comunidad, codigo_bot);

    //tabla de estados de mensajes (logs)
    /*
    obtenerLogs
codigo_bot
    */
    funcion = 'obtenerLogs';
    datos = {
        codigo_bot: data.codigo_bot
    };
    let logs = await queryAsync(clase, funcion, datos);
    logs = logs.data;

    //Obtener el modal
    let titulo = "Editar Comunidad";
    let contenido = form + formEnviarMensaje + gridTecnicos + logs;
    let funcionGuardar = funcionGuardarComunidades;
    let usuario = rolComunidades;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //si camposComunidades tiene un campo bloquear y es true, bloquear el campo
    camposComunidades.forEach(c => {
        if(c.bloquear){
            document.getElementById(c.id).disabled = true;
        }
    });
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearComunidades() {
    let campos = crearCamposDeFormulario(camposComunidades);
    let form = createForm(campos, "");

    let titulo = "Crear Comunidad";
    let contenido = form;
    let funcionGuardar = funcionGuardarComunidades;
    let usuario = rolComunidades;

    createModal(titulo, contenido, funcionGuardar, usuario);

    //impedir que el campo codigo_bot tenga más de 20 caracteres y que solo puedan contener letras y números
    document.getElementById('codigo_bot').addEventListener('input', function(){
        if(this.value.length > 20){
            this.value = this.value.slice(0, 20);
        }
        this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
    });
}

async function guardarComunidades(id = 0){
    console.log("guardarComunidades");
    //obtener id_agente del usuario
    let usuario = JSON.parse(localStorage.getItem('usuario'));
    let id_agente = usuario.id;

    let datosExtra = {
        id_agente: id_agente
    };

    let datos = getDatosFormulario(camposComunidades, datosExtra);
    console.log(datos);

    let clase = 'Comunidades';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'modificar';
    }else{
        funcion = 'crear';
    }

    await queryAsync(clase, funcion, datos);
    
    //obtener un array sencillo con todos los textos de la clase card_info_1
    let comunidades = [];
    let cards = document.getElementsByClassName('card_info_1');
    for(let i = 0; i < cards.length; i++){
        comunidades.push(cards[i].textContent);
    }

    //añadir el nuevo código_bot al array
    comunidades.push(datos.data.codigo_bot);

    localStorage.setItem('comunidades', JSON.stringify(comunidades));

    getComunidades();
    destruirModal();
}

async function generarYDescargarQR(url) {
    // Genera el QR
    const canvas = document.createElement('canvas');
    await QRCode.toCanvas(canvas, url, {
        width: 500,
        margin: 2.5,
    });

    // Convierte el canvas a una imagen PNG
    const imagenUrl = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");

    // Crea un enlace para descargar
    const descargarLink = document.createElement('a');
    descargarLink.href = imagenUrl;
    descargarLink.download = 'QR.png';

    // Simula un clic en el enlace para descargar
    document.body.appendChild(descargarLink);
    descargarLink.click();
    document.body.removeChild(descargarLink);
}

///////////////////////////////////////////////// TECNICOS DE COMUNIDAD /////////////////////////////////////////////////
function gridTecnicosPorComunidad(tecnicos, id_comunidad, codigo_bot) {

    tecnicos = Object.values(tecnicos);

    let comunidadTecnico = "";
    tecnicos.forEach((tecnico) => {
        let tipo = tecnico.tipo;
        let nombre = tecnico.nombre;
        let telefono = tecnico.telefono;
        let codigoComunidad = tecnico.referencia_usuario;
        let principalOrAuxiliar = tecnico.origen;

        // por cada comunidad un check para idenficar si es auxiliar o no y otro check para activo o no
        let auxiliar = "";
        let activo = "";
        let disabledAuxiliar = "";

        if (principalOrAuxiliar === "tecnicos_auxiliares") {
            auxiliar = "checked";
        }

        if (codigoComunidad === codigo_bot) {
            activo = "checked";
        } else {
            disabledAuxiliar = "disabled";
        }

        comunidadTecnico += `
            <div class="comunidadTecnico">
                <div class="comunidadTecnicoNombre">${nombre}</div>
                <div class="comunidadTecnicoNombre" style="font-size: 0.8em;">
                ${tipo}</div>
                <div class="checkBoxGroup comunidadTecnicoAuxiliar">
                    <input type="checkbox" id="auxiliar_${telefono}" class="comunidadTecnicoInput" ${auxiliar} ${disabledAuxiliar}>
                    <label for="auxiliar">Auxiliar</label>
                </div>
                <div class="checkBoxGroup comunidadTecnicoActivo">
                    <input type="checkbox" id="activo_${telefono}" class="comunidadTecnicoInput" ${activo} onchange="toggleAuxiliarComunidad('${telefono}')">
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

            <button class="botonActualizarComunidadesTecnico" onclick="actualizarTecnicoComunidad('${codigo_bot}', ${id_comunidad})">Actualizar Tecnicos</button>
        </div>        
   `;

    return html;
}

function toggleAuxiliarComunidad(telefono) {
    const activoCheckbox = document.getElementById(`activo_${telefono}`);
    const auxiliarCheckbox = document.getElementById(`auxiliar_${telefono}`);
    if (activoCheckbox.checked) {
        auxiliarCheckbox.disabled = false;
    } else {
        auxiliarCheckbox.disabled = true;
        auxiliarCheckbox.checked = false;
    }
}

async function actualizarTecnicoComunidad(codigo_bot, id_comunidad) {
    //obtener todos los elementos con clase comunidadTecnicoInput
    let elementos = document.getElementsByClassName("comunidadTecnicoInput");
    
    //obtener una lista de codigos de comunidades
    let telefonos = [];
    for (let i = 0; i < elementos.length; i++) {
        let id = elementos[i].id;
        let telefono = id.split("_")[1];
        telefonos.push(telefono);
    }

    //eliminar duplicados
    telefonos = [...new Set(telefonos)];

    //crear dataComunidades comprobando si es auxiliar y si está activo
    let dataTecnicos = [];
    telefonos.forEach((telefono) => {
        let auxiliar = document.getElementById(`auxiliar_${telefono}`).checked;
        let activo = document.getElementById(`activo_${telefono}`).checked;

        let comunidad = {
            telefono: telefono,
            esAuxiliar: auxiliar,
            activo: activo
        };

        dataTecnicos.push(comunidad);
    });

    let data = {
        dataComunidades: dataTecnicos,
        id_comunidad: id_comunidad,
        codigo_bot: codigo_bot
    };

    let clase = "Comunidades";
    let funcion = "asignarTecnicosComunidad";
    let datos = data;
    let dataResponse = await queryAsync(clase, funcion, datos);

    //si es success mostrar modal de éxito
    if (dataResponse.status === "success") {
        //crear modal aviso de éxito
        let titulo = "Comunidades Actualizadas";
        let mensaje = "Los técnicos de la comunidad han sido actualizadas correctamente";
        let funcionAceptar = "openComunidadModal";
        let arrayParametros = [id_comunidad];

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    } else {
        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible actualizar los Técnicos de la Comunidad";	
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

function openComunidadModal(id) {
    destruirModalAviso();
    destruirModal();
    openComunidades(id);
}
