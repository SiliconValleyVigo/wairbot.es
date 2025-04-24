async function createTable(
    data,
    hayPaginacion,
    paginaActual,
    totalPaginas,
    rows_per_page,
    grid_or_table,
    titulo,
    location,
    clase,
    funcionCrear,
    funcionLeer,
    funcionAbrir,
    rol,
    campos,
    nombreTablaConfiguracion,
    camposInput,
    funcionModificacionDeTabla,
    camposCard
) {
    //si camposCard es un JSON, convertirlo a base64
    if (typeof camposCard === "object") {
        camposCard = JSON.stringify(camposCard);

        //convertir a base64 para que no se pierda la informacion, que incluya acentos y caracteres especiales
        let camposCardCodificados = encodeURIComponent(camposCard);
        camposCard = btoa(camposCardCodificados);
    }

    if (typeof camposInput === "object") {
        camposInput = JSON.stringify(camposInput);

        //convertir a base64 para que no se pierda la informacion, que incluya acentos y caracteres especiales
        let camposInputCodificados = encodeURIComponent(camposInput);
        camposInput = btoa(camposInputCodificados);
    }

    let headerGridOrTable = await createHeaderGridOrTable(
        hayPaginacion,
        paginaActual,
        totalPaginas,
        rows_per_page,
        grid_or_table,
        titulo,
        location,
        clase,
        funcionCrear,
        funcionLeer,
        funcionAbrir,
        rol,
        camposCard,
        campos,
        nombreTablaConfiguracion,
        camposInput,
        funcionModificacionDeTabla
    );

    let paginacion = headerGridOrTable.paginacion;
    let botonCrear = headerGridOrTable.botonCrear;

    let table = await tablaHtml(data, camposInput, nombreTablaConfiguracion, funcionAbrir);

    let alturaCabecera = sumarAlturasCabeceras();
    let altoDeTabla = window.innerHeight - alturaCabecera - 50;

    let botonExportarExcel = `
        <div onclick="exportarExcel('${clase}', '${funcionLeer}')" style="cursor: pointer; margin-left: 1em;">
            <img src="imgs/excel.svg" alt="exportarExcel" width="30" height="30">
        </div>
    `;

    let tableHtml = `
    <style>
        .table-wrapper {
            overflow-x: auto;
            width: 100%;
        }

        table {
            border-collapse: collapse;
            table-layout: auto;
            width: auto;
          }
            
        th, td {
            border: 1px solid #ddd;
            text-align: left;
            overflow: hidden;
            white-space: nowrap;
          }

        th {
            padding: 10px;
            position: relative;
            background-color: #0a4fff75;
        }

        .resizer {
            cursor: col-resize;
            width: 5px;
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: #0a4fff26;
          }
            
        .tableContainer {
            width: 100%;
            height: ${altoDeTabla}px;
            overflow: auto;
        }

        .campoHeaderTablaContainer {
            display: flex;
            justify-content: space-between;
        }
        .campoHeaderTabla {
            display: inline-block;
        }
        .campoHeaderTablaBotones {
            display: flex;
            flex-direction: row;
        }
        
        /* Estilos para filas alternas */
        tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #ffffff;
        }

        .campoHeaderTablaBoton {
            cursor: pointer;
        }

    </style>
    <div class="headerGrid">
        <div class="crearMasTituloHeaderGrid">
            ${botonCrear}
            ${botonExportarExcel}
            <div class="tituloGrid">${titulo}</div>
        </div>
        ${paginacion}
    </div>
    <div class="tableContainer">
        ${table}
    </div>
    <div id="formularioFiltro"
         style="
            display: none;
            position: absolute;
            top: 20px;
            left: 0;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            z-index: 100;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 200px;
            padding: 15px;
    "></div>
    `;

    dataListColeccion = []; //reiniciar dataListColeccion para poder cargar otra página

    return tableHtml;
}


//al hacer click en cualquier lugar que no sea el formulario o en algún elemento con la clase campoHeaderTablaBotonFiltro, ocultarlo
document.addEventListener('click', function(event) {
    // Verificar si el clic fue en un elemento con la clase 'campoHeaderTablaBotonFiltro'
    if (event.target.classList.contains('campoHeaderTablaBotonFiltro')) {
        return; // Salir de la función si se hace clic en 'campoHeaderTablaBotonFiltro'
    }
    
    let formularioFiltro = document.getElementById("formularioFiltro");
    let padre = event.target.parentElement;

    if (padre.classList.contains('campoHeaderTablaBotonFiltro')) {
        return;
    }

    // Comprobar si está visible
    if (formularioFiltro && formularioFiltro.style.display === "block") {
        // Comprobar si el clic no fue dentro del formulario
        if (!formularioFiltro.contains(event.target)) {
            formularioFiltro.style.display = "none";
        }
    }
});

async function generarThead(campos, configuracion, nombreTablaConfiguracion, order_by_array, ancho_columnas) {
    console.log(campos);
    // Obtener los campos de la tabla de configuración
    let visualizacion = JSON.parse(configuracion.visualizacion);

    //si visualizacion es null, o es un json vacío o un string vacío o visualizacion.length es 0, obtener los campos de la cabecera de la tabla por su id con la clase campoTableHeader
    if (visualizacion === null || visualizacion === "[]" || visualizacion === "" || visualizacion.length === 0) {
        let keys = [];
        for (let campo of campos) {
            keys.push(campo.id);
        }

        //crear visualizacion con los campos de keys
        let count = 1;
        for (let key of keys) {
            let visible = true;
            let orden = count;
            visualizacion.push({ id: key, visible: visible, orden: orden });
            count++;
        }
    }

    // Filtrar y ordenar los campos según la configuración de visualización
    let camposVisibles = visualizacion
        .filter(campo => campo.visible)
        .sort((a, b) => a.orden - b.orden);

    console.log(camposVisibles);

    let thead = "<thead><tr>";
    for (let campoVisualizacion of camposVisibles) {
        let key = campoVisualizacion.id;
        console.log(campos);//{tipo: 'text', id: 'nombre', label: 'Nombre'} // {tipo: 'text', id: 'nombre', label: 'Nombre'}
        console.log(key, campos[0].id);
        let campo = campos.find(c => c.id === key);
        console.log(campo);

        // Si order_by_array contiene key, asignar orden
        let orden = "sin_orden";
        let imgOrden = "imgs/sinOrden.svg";

        if (order_by_array.hasOwnProperty(key)) {
            orden = order_by_array[key];
            if (orden === "ASC") {
                imgOrden = "imgs/ordenAscendente.svg";
            } else if (orden === "DESC") {
                imgOrden = "imgs/ordenDescendente.svg";
            }
        }

        let imgFiltro = "imgs/sinFiltro.svg";

        // Comprobar si existe un filtro con la key
        let filtros = JSON.parse(configuracion.filtros);
        if (filtros.hasOwnProperty(key)) {
            imgFiltro = "imgs/conFiltro.svg";
        }

        // Tamaño de la columna
        // Comprobar si existe el id en ancho_columnas
        let ancho = 150;
        if (ancho_columnas.hasOwnProperty(key)) {
            ancho = ancho_columnas[key];
        }

        let id = nombreTablaConfiguracion + "_" + campo.id;
        thead += `
        <th class="campoTableHeader" id="${id}" style="width: ${ancho}px; position: relative;">
            <div class="campoHeaderTablaContainer">
                <div class="campoHeaderTabla">
                    ${campo.label}
                </div>
                <div class="campoHeaderTablaBotones">
                    <div
                        class="campoHeaderTablaBoton campoHeaderTablaBotonOrden"
                        onclick="ordenarTabla('${id}', '${orden}', '${configuracion.order_by}', '${nombreTablaConfiguracion}')"
                    >
                        <img src="${imgOrden}" alt="ordenar" width="8px" height="15px" style="margin-left: 10px">
                    </div>
                     <div class="campoHeaderTablaBoton campoHeaderTablaBotonFiltro" onclick="mostrarFiltro('${id}', this)">
                         <img src="${imgFiltro}" alt="filtrar" width="8px" height="15px" style="margin-left: 10px">
                     </div>
                </div>
            </div>
            <div class="resizer" onmousedown="startResize(event, '${id}', '${nombreTablaConfiguracion}')"></div>
        </th>`;
    }
    thead += "</tr></thead>";
    return thead;
}

async function generarTbody(data, campos, nombreTablaConfiguracion, funcionAbrir, configuracion) {
    // Obtener los campos de la tabla de configuración
    let visualizacion = JSON.parse(configuracion.visualizacion);

    if (visualizacion === null || visualizacion === "[]" || visualizacion === "" || visualizacion.length === 0) {
        let keys = [];
        for (let campo of campos) {
            keys.push(campo.id);
        }

        //crear visualizacion con los campos de keys
        let count = 1;
        for (let key of keys) {
            let visible = true;
            let orden = count;
            visualizacion.push({ id: key, visible: visible, orden: orden });
            count++;
        }
    }

    console.log(visualizacion);

    // Filtrar y ordenar los campos según la configuración de visualización
    let camposVisibles = visualizacion
        .filter(campo => campo.visible)
        .sort((a, b) => a.orden - b.orden);

    let tbody = "<tbody>";
    for (let registro of data) {
        tbody += "<tr>";
        for (let campoVisualizacion of camposVisibles) {
            let id = campoVisualizacion.id;
            let campo = campos.find(c => c.id === id);
            let value = registro[id];
            let id_number = registro.id;

            console.log(campo);

            let input = await getTableInput(campo, value, nombreTablaConfiguracion, id_number);

            // Si input no es null o undefined, asignar input a value
            if (input !== null && input !== undefined && input !== "") {
                value = input;
            }

            tbody += `
            <td class="${nombreTablaConfiguracion}_${id}" oncontextmenu="mostrarBotonEditar(event, '${id_number}', '${funcionAbrir}', '${nombreTablaConfiguracion}')">
                ${value}
            </td>`;
        }
        tbody += "</tr>";
    }
    tbody += "</tbody>";
    return tbody;
}

let editButton;

function mostrarBotonEditar(event, id, funcionAbrir, nombreTablaConfiguracion) {
    event.preventDefault();

    // Eliminar el botón de edición si ya existe
    if (editButton) {
        editButton.remove();
    }

    // Crear el botón de edición
    const buttonHTML = `
        <div id="editButton" style="
                position: absolute;
                top: ${event.clientY}px; 
                left: ${event.clientX}px;
                background-color: white;
                border: 1px solid #ccc;
                padding: 1rem;
                z-index: 1000;
                display: flex;
                flex-direction: column;
                align-items: center;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            ">
            <button 
                onclick="editarFila('${id}', '${funcionAbrir}')"
                style="
                    background-color: var(--azul);
                    color: white;
                    border: none;
                    padding: 0.5rem 1rem;
                    margin-bottom: 2rem;
                    width: 150px;
                "
            >
                Editar
            </button>
            <button 
                onclick="configurarVistaDeTabla('${nombreTablaConfiguracion}')"
                style="
                    background-color: var(--azul);
                    color: white;
                    border: none;
                    padding: 0.5rem 1rem;
                    width: 150px;
                "
            >
                Configurar
            </button>
        </div>
    `;

    editButton = document.createElement('div');
    editButton.innerHTML = buttonHTML;

    document.body.appendChild(editButton);
}

async function configurarVistaDeTabla(nombreTablaConfiguracion) {
    //cerrar el botón de edición
    if (editButton) {
        editButton.remove();
        editButton = null;
    }
    //obtener los datos de configuracion de la tabla
    let configuracion = await getTablaConfiguracion(nombreTablaConfiguracion);

    let visualizacion = JSON.parse(configuracion.visualizacion);

    //si viualiacion es null, o es [], obtener los campos de la tabla
    let campos = [];
    if (visualizacion === null || visualizacion === "[]" || visualizacion === "" || visualizacion.length === 0) {
        //obtener los campos de la cabecera de la tabla por su id con la clase campoTableHeader
        let camposCabecera = document.getElementsByClassName("campoTableHeader");
        count = 1;
        for (let campo of camposCabecera) {
            let restarAId = campo.id.split("_")[0];
            let id = campo.id.replace(restarAId + "_", "");
            let visible = true;
            let orden = count;
            count++;
            campos.push({ id: id, visible: visible, orden: orden });
        }
    } else {
        campos = visualizacion;
    }

    console.log(campos);

    let form = "";
    for (let campo of campos) {
        //cada campo debe tener un select con dos opciones: visible y no visible y un input para el orden, que debe ser un número, al cambiar de numero, se debe cambiar el orden de los campos
        let id = campo.id;
        let visible = campo.visible;
        let orden = campo.orden;

        let checkedVisible = "";
        let checkedNoVisible = "";
        if (visible) {
            checkedVisible = "checked";
        } else {
            checkedNoVisible = "checked";
        }

        form += `
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: center; padding: 10px; border: 1px solid #ccc; width: 70%;">
            <div style="font-weight: bold; color: var(--azul); font-size: 1.2rem; width: 33%;">
                ${id}
            </div>
            <div style="display: flex; flex-direction: column; width: 33%;">
                <div style="display: flex; flex-direction: row;">
                    <input type="radio" id="${id}_visible" name="${id}" value="visible" ${checkedVisible}>
                    <label for="${id}_visible">Visible</label>
                </div>
                <div style="display: flex; flex-direction: row;">
                    <input type="radio" id="${id}_no_visible" name="${id}" value="no_visible" ${checkedNoVisible}>
                    <label for="${id}_no_visible">No Visible</label>
                </div>
            </div>
            <div style="width: 33%; display: flex; justify-content: center; align-items: center;">
                <style>
                    /* Rotar las flechas del input type number */
                    input[type="number"]::-webkit-inner-spin-button,
                    input[type="number"]::-webkit-outer-spin-button {
                        -webkit-transform: rotate(180deg);
                        transform: rotate(180deg);
                    }

                    input[type="number"] {
                        padding: 0.5rem;
                        font-size: 1.1rem;
                        border-radius: 50px;
                        text-align: center;
                    }
                </style>
                <input type="number" class="ordenConfiguracionTabla" id="${id}_orden" name="${id}_orden" value="${orden}" style="width: 50px;" onchange="cambiarOrdenConfiguracionTabla('${id}', this.value)">
            </div>
        </div>
        `;
    }

    //div con display none con el nombreTablaConfiguracion como innerText
    form += `<div style="display: none;" id="paraObtenerNombreTablaConfiguracion">${nombreTablaConfiguracion}</div>`;

    let titulo = "Configurar Tabla"
    let contenido = form;
    let funcionGuardar = "guardarConfiguracionTablaOrdenAndVisible";

    //obtener usuario de localStorage
    let user = localStorage.getItem('usuario');
    let rol = JSON.parse(user).rol;

    let usuario =  rol;
    let idOriginal = 'modal';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

async function guardarConfiguracionTablaOrdenAndVisible() {
    //obtener el nombre de la tabla de configuracion
    let nombreTablaConfiguracion = document.getElementById("paraObtenerNombreTablaConfiguracion").innerText;

    //obtener los campos de la configuración
    let campos = Array.from(document.getElementsByClassName("ordenConfiguracionTabla"));

    let camposOrdenados = [];
    for (let campo of campos) {
        let id = campo.id.replace("_orden", "");
        let orden = parseInt(campo.value, 10);
        let visible = document.getElementById(id + "_visible").checked;

        camposOrdenados.push({ id: id, orden: orden, visible: visible });
    }

    //ordenar camposOrdenados por orden
    camposOrdenados.sort((a, b) => a.orden - b.orden);

    //convertir camposOrdenados a JSON
    let camposOrdenadosJson = JSON.stringify(camposOrdenados);

    //actualizar visualizacion en la tabla de configuracion
    let clase = "ConfiguracionTablas";
    let funcion = "cambiarVisualizacion";
    let datos = {
        tabla: nombreTablaConfiguracion,
        visualizacion: camposOrdenadosJson
    };

    let response = await queryAsync(clase, funcion, datos);

    //cerrar el modal
    destruirModal();

    //recargar la tabla
    //obtener location de localStorage
    let location = localStorage.getItem("location");

    //obtener funcion de locationHandlers
    let funcionRecarga = locationHandlers[location];
    
    //ejecutar funcionRecarga
    funcionRecarga();
}

async function cambiarOrdenConfiguracionTabla(id, nuevoOrden) {
    let divNombreTabla = document.getElementById("paraObtenerNombreTablaConfiguracion").outerHTML;
    const inputs = Array.from(document.getElementsByClassName("ordenConfiguracionTabla"));

    let campos = inputs.map(input => ({
        id: input.id.replace("_orden", ""),
        orden: parseInt(input.value, 10)
    }));

    console.log(id, nuevoOrden);
    console.log(campos);

    //obtener el elemento que cambia
    let campoCambiado = campos.find(campo => campo.id === id);

    console.log(campoCambiado);

    //eliminar el campo cambiado del array volver a numerar el orden
    let camposSinCambiado = [];
    let count = 1;
    for (let campo of campos) {
        if (campo.id !== id) {
            campo.orden = count;
            count++;
            camposSinCambiado.push(campo);
        }
    }

    //insertar el campo cambiado en la posición correcta y si un campo es igual o mayor a nuevoOrden, sumarle 1
    let camposOrdenados = [];
    for (let campo of camposSinCambiado) {
        if (campo.orden >= nuevoOrden) {
            campo.orden++;
        }
        camposOrdenados.push(campo);
    }

    console.log(campoCambiado);
    //insertar el campo cambiado en la posición correcta y convertir a entero
    camposOrdenados.push(campoCambiado);
    
    console.log(camposOrdenados);

    //ordenar camposOrdenados por orden
    camposOrdenados.sort((a, b) => a.orden - b.orden);

    console.log(camposOrdenados);

    let nuevoHtml = "";
    for (let campo of camposOrdenados) {
        let id = campo.id;
        let orden = campo.orden;

        console.log(id, orden);

        let elemento = document.getElementById(id + "_orden");
        elemento.value = orden;
        let elementoHtml = elemento.parentElement.parentElement.outerHTML;
        nuevoHtml += elementoHtml;
    }

    console.log(nuevoHtml);

    //añaadir divNombreTabla al nuevoHtml
    nuevoHtml += divNombreTabla;

    //cambiar el nuevo html en la clase modalBodyContenido
    document.querySelector(".modalBodyContenido").innerHTML = nuevoHtml;

    //esperar 250ms y renumerar los campos
    await new Promise(resolve => setTimeout(resolve, 250));

    //obtener los campos del nuevo html
    let inputsParaRenumerar = Array.from(document.getElementsByClassName("ordenConfiguracionTabla"));

    //renumerar los campos
    let countParaRenumerar = 1;
    for (let input of inputsParaRenumerar) {
        input.value = countParaRenumerar;
        countParaRenumerar++;
    } 
}



// Ocultar el botón de edición cuando se haga clic en cualquier otro lugar
document.addEventListener('click', (event) => {
    if (editButton && !editButton.contains(event.target)) {
        editButton.remove();
        editButton = null;
    }
});

// Función para editar la fila
function editarFila(id, funcionAbrir) {
    window[funcionAbrir](id);
}

async function tablaHtml(data, campos, nombreTablaConfiguracion, funcionAbrir) {
    let configuracion = await getTablaConfiguracion(nombreTablaConfiguracion);

    let order_by_array = getArrayOrderBy(configuracion);
    let ancho_columnas = JSON.parse(configuracion.columnas);

    //si campos en un base64, convertirlo a JSON
    if (typeof campos === "string") {
        let camposDecodificados = atob(campos);
        campos = JSON.parse(decodeURIComponent(camposDecodificados));
    }

    //obtener la suma de los anchos de las columnas
    let anchoColumnas = 0;
    for (let key in ancho_columnas) {
        anchoColumnas += ancho_columnas[key];
    }

    //si anchoColumnas es 0, asiginar número de columnas * 150
    if (anchoColumnas === 0) {
        anchoColumnas = campos.length * 500;
    }

    let html = `<div class="table-wrapper" style="width: ${anchoColumnas + 100}px"><table id="${nombreTablaConfiguracion}">`;

    for (let i = 0; i < data.length; i += 20) {
        let subset = data.slice(i, i + 20);
        let thead = await generarThead(campos, configuracion, nombreTablaConfiguracion, order_by_array, ancho_columnas);
        let tbody = await generarTbody(subset, campos, nombreTablaConfiguracion, funcionAbrir, configuracion);
        html += thead + tbody;
    }

    html += "</table></div>";
    return html;
}

//mostrar filtro
async function mostrarFiltro(id, boton) {
    const formulario = document.getElementById('formularioFiltro');
    const rect = boton.getBoundingClientRect();
    formulario.style.top = `${rect.bottom + window.scrollY}px`;
    formulario.style.left = `${rect.left + window.scrollX}px`;
    formulario.style.display = 'block';

    //options de la columna
    let idSplit = id.split("_");
    let nombreTablaConfiguracion = idSplit[0];

    //sustituir nombreTablaConfiguracion_ por ""
    let campo = id.replace(nombreTablaConfiguracion + "_", "");

    let options = await getOptionsElementosDisponiblesEnColumna(campo, nombreTablaConfiguracion);

    //obtener configuracion
    let configuracion = await getTablaConfiguracion(nombreTablaConfiguracion);
    let filtros = JSON.parse(configuracion.filtros);

    //si hay un filtro con la key igual a campo, asignar valor a input de texto
    let valor = "";
    let checked0 = "";
    let checked1 = "";
    let checked2 = "";
    let checked3 = "";
    if (filtros.hasOwnProperty(campo)) {
        valor = filtros[campo];

        //si valor contiene !, asignar valor sin el !
        if (valor.includes("!")) {
            valor = valor.replace("!", "");
            checked2 = "checked";
        }

        //si valor contiene ?, asignar valor sin el ?
        if (valor.includes("?")) {
            valor = valor.replace("?", "");
            checked3 = "checked";
        }

        //si no contiene ! ni ?, asignar valor
        if (!valor.includes("!") && !valor.includes("?")) {
            checked1 = "checked";
        }
    } else {
        checked0 = "checked";
    }

    let formularioHtml = `
        <style>       
            .formularioFiltroTablas label { 
                margin-top: 10px;
            }
            
            .formularioFiltroTablas input[type="text"] {
                margin-bottom: 10px;
                padding: 0.5rem;
                width: calc(100% - 1rem);
            }

            .formularioFiltroTablas button {
                margin-top: 10px;
                width: 100%;
                background-color: var(--azul);
                border: none;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 5px;
                cursor: pointer;
            }

            .formularioFiltroTablas select {
                padding: 0.5rem;
                width: 100%;
                margin-bottom: 10px;
            }

            .formularioFiltroTablas .labelPrincipal {
                font-weight: bold;
                color: var(--azul);
            }
        </style>
        <div class="formularioFiltroTablas">
            <label for="condicion" class=labelPrincipal>CONDICION:</label><br>
            <input type="radio" id="condicion0FiltroTabla" name="condicion" value="" ${checked0}>
            <label for="condicion1">Sin Filtro</label><br>
            <input type="radio" id="condicion1FiltroTabla" name="condicion" value="igual" ${checked1}>
            <label for="condicion1">Que sea igual</label><br>
            <input type="radio" id="condicion2FiltroTabla" name="condicion" value="no_igual" ${checked2}>
            <label for="condicion2">Que no sea igual</label><br>
            <input type="radio" id="condicion3FiltroTabla" name="condicion" value="contiene" ${checked3}>
            <label for="condicion3">Que contenga</label><br><br>
    
            <label for="valor" class=labelPrincipal>VALOR:</label><br>
            <input type="text" id="valorFiltroTabla" name="valor" value="${valor}"><br><br>
    
            <label for="valor" class=labelPrincipal>OPCIONES:</label><br>
            <select id="opcionesFiltroTabla" name="opciones" onchange="document.getElementById('valorFiltroTabla').value = this.value">
                ${options}
            </select><br><br>
    
            <button onclick="filtrarTabla('${nombreTablaConfiguracion}', '${campo}')">
                Filtrar
            </button>
        </div>        
    `;

    formulario.innerHTML = formularioHtml;
}

//filtrar tabla
async function filtrarTabla(nombreTablaConfiguracion, campo) {
    //obtener condicion
    let condicion = "";
    let radios = document.getElementsByName('condicion');
    for (let radio of radios) {
        if (radio.checked) {
            condicion = radio.value;
            break;
        }
    }

    //obtener valor
    let valor = document.getElementById("valorFiltroTabla").value;

    //obtener opciones
    let opciones = document.getElementById("opcionesFiltroTabla");
    let opcion = opciones.value;

    //[nombre_columna => "valor"]    [nombre_columna => "!valor"]    [nombre_columna => "?valor"]
    if (condicion === "igual") {
        //si valor es "", asignar opcion
        if (valor === "") {
            valor = opcion;
        }
    } else if (condicion === "no_igual") {
        //si valor es "", asignar opcion
        if (valor === "") {
            valor = "!" + opcion;
        }else{
            valor = "!" + valor;
        }
    } else if (condicion === "contiene") {
        //si valor es "", asignar opcion
        if (valor === "") {
            valor = "?" + opcion;
        }else{
            valor = "?" + valor;
        }
    } else {
        valor = "";
    }

    let configuracion = await getTablaConfiguracion(nombreTablaConfiguracion);

    //si configuracion.filtros está vacío o es [], asignar {}
    if (configuracion.filtros === "" || configuracion.filtros === "[]" || configuracion.filtros === null) {
        configuracion.filtros = "{}";
    }

    let filtros = JSON.parse(configuracion.filtros);

    //actualizar filtros
    filtros[campo] = valor;

    //si valor es "", eliminar campo de filtros
    if (valor === "") {
        delete filtros[campo];
    }
    

    //obtener location de localStorage
    let location = localStorage.getItem("location");

    //obtener funcion de locationHandlers
    let funcionRecarga = locationHandlers[location];

    //filtrar tabla
    let clase = "ConfiguracionTablas";
    let funcion = "guardarFiltros";

    let datos = {
        tabla: nombreTablaConfiguracion,
        filtros: filtros,
    };

    let data = await queryAsync(clase, funcion, datos);

    //ejecutar funcionRecarga
    funcionRecarga();
}

async function getOptionsElementosDisponiblesEnColumna(campo, nombreTablaConfiguracion) {
    let clase = "Utilidades";
    let funcion = "obtenerElemenosDeUnaColumnaSinRepetir";
    let data = {
        tabla: nombreTablaConfiguracion,
        columna: campo
    };

    let elementos = await queryAsync(clase, funcion, data);
    elementos = elementos;

    //eliminar los elementos que sean null
    elementos = elementos.filter((elemento) => {
        return elemento !== null;
    });

    let options = "<option value=''>Sin filtro</option>";
    for (let elemento of elementos) {
        //si elemento contiene más de 20 caracteres, cortar
        let elementoMostrar = elemento;
        if (elemento.length > 50) {
            elementoMostrar = elementoMostrar.substring(0, 50) + "...";
        }

        options += `<option value="${elemento}">${elementoMostrar}</option>`;
    }

    return options;
}

//ordenar tabla
async function ordenarTabla(id, orden, dataOrderBy, nombreTablaConfiguracion) {
    //eliminar "nombreTablaConfiguracion_" de id
    let idSplit = id.split("_");
    id = idSplit[1];

    //si orden es sin_orden, asignar ASC
    if (orden === "sin_orden") {
        orden = "ASC";
    } else if (orden === "ASC") {
        orden = "DESC";
    } else if (orden === "DESC") {
        orden = "sin_orden";
    }

    //si dataOrderBy está vacío, asignar id y orden
    if (dataOrderBy === "") {
        dataOrderBy = id + " " + orden;
    } else {
        //si dataOrderBy contiene id, eliminarlo
        if (dataOrderBy.includes(id)) {
            let dataOrderByArray = dataOrderBy.split(", ");
            let dataOrderByArrayFiltrado = dataOrderByArray.filter((e) => !e.includes(id));
            dataOrderBy = dataOrderByArrayFiltrado.join(", ");
        }

        //si orden es sin_orden, no añadir id y orden a dataOrderBy
        if (orden !== "sin_orden") {
            //añadir id y orden a dataOrderBy
            dataOrderBy += ", " + id + " " + orden;
        }
    }

    //si no contiene " ORDER BY ", añadirlo
    if (!dataOrderBy.includes("ORDER BY")) {
        dataOrderBy = " ORDER BY " + dataOrderBy;
    }

    //BY ,
    dataOrderBy = dataOrderBy.replace("BY , ", "BY ");

    //eliminar todos los espacios en blanco
    dataOrderByaForCheck = dataOrderBy.replace(/\s/g, "");

    //si solo contiene " ORDER BY ", asignar ""
    if (dataOrderByaForCheck === "ORDERBY") {
        dataOrderBy = "";
    }

    //guardar dataOrderBy en la base de datos
    let clase = "ConfiguracionTablas";
    let funcion = "cambiarOrderBy";

    let datos = {
        tabla: nombreTablaConfiguracion,
        order_by: dataOrderBy,
    };

    let data = await queryAsync(clase, funcion, datos);

    //recargar la tabla
    //obtener location de localStorage
    let location = localStorage.getItem("location");

    //obtener funcion de locationHandlers
    let funcionRecarga = locationHandlers[location];
    
    //ejecutar funcionRecarga
    funcionRecarga();
}

// Variables de estado para controlar el redimensionado
let isResizing = false;
let nombreTablaConfiguracionResizing = "";
let startX = 0;
let startWidth = 0;
let currentColumn = null;

function startResize(e, id, nombreTablaConfiguracion) {
    currentColumn = document.getElementById(id); // Detecta la columna asociada
    nombreTablaConfiguracionResizing = nombreTablaConfiguracion;
    isResizing = true;
    startX = e.pageX; // Obtener la posición inicial del mouse
    startWidth = currentColumn.offsetWidth; // Obtener el ancho inicial de la columna
    document.addEventListener('mousemove', resizeColumn); // Evento para redimensionar
    document.addEventListener('mouseup', stopResize); // Evento para detener el redimensionado
}

// Función para redimensionar la columna
function resizeColumn(e) {
    if (isResizing) { 
        const newWidth = startWidth + (e.pageX - startX); // Calcula el nuevo ancho
        if (newWidth > 50) { // Permitir disminuir el tamaño hasta un mínimo de 50px
            // Seleccionar todas las columnas con el mismo ID y aplicarles el nuevo ancho
            let id = currentColumn.id;
            let columnas = document.querySelectorAll(`#${id}`);
            columnas.forEach(columna => {
                columna.style.width = newWidth + 'px';
            });

            // Añadir el mismo ancho pero en max-width a los td de esa columna
            let idSplit = id.split("_");
            let nombreTablaConfiguracion = idSplit[0];
            let campo = idSplit[1];
            let tds = document.querySelectorAll(`.${nombreTablaConfiguracion}_${campo}`);
            tds.forEach(td => {
                td.style.maxWidth = newWidth + "px";
            });

            // Recalcular el ancho total de las columnas
            let anchoColumnas = 0;
            let todasColumnas = document.querySelectorAll(".campoTableHeader");
            todasColumnas.forEach(columna => {
                anchoColumnas += columna.offsetWidth;
            });

            // Añadir 100px a la tabla
            document.querySelector(".table-wrapper").style.width = anchoColumnas + 100 + "px";
        }
    }
}

// Función para detener el redimensionado y mostrar el resultado
function stopResize() {
    if (isResizing) {
        const columnText = currentColumn.innerText; // Obtener el texto de la columna (su nombre)
        const newWidth = currentColumn.offsetWidth; // Obtener el ancho final de la columna

        //obtener los anchos de todas las columnas {id: ancho} (el id sin el nombre de la tabla)
        let columnas = document.querySelectorAll(".campoTableHeader");
        let ancho_columnas = {};
        for (let columna of columnas) {
            let id = columna.id;

            //eliminar "nombreTablaConfiguracion_" de id
            let eliminar = nombreTablaConfiguracionResizing + "_";
            id = id.replace(eliminar, "");

            let ancho = columna.offsetWidth;
            ancho_columnas[id] = ancho;
        }

        //guardar ancho_columnas en la base de datos
        let clase = "ConfiguracionTablas";
        let funcion = "guardarColumnas";

        let datos = {
            tabla: nombreTablaConfiguracionResizing,
            columnas: ancho_columnas,
        };

        queryAsync(clase, funcion, datos);

        isResizing = false;
        document.removeEventListener('mousemove', resizeColumn);
        document.removeEventListener('mouseup', stopResize);
    }
}

function sumarAlturasCabeceras() {
    // Seleccionar los elementos del DOM
    const nav2Container = document.getElementById('nav2Container');
    const headerGrande = document.getElementById('headerGrande');
    const headerSmall = document.getElementById('headerSmall');

    //comprobar si el ancho de la pantalla es menor a 800px
    let headerGrid = 0;
    if (window.innerWidth > 744) {
        headerGrid = 50;
    } else {
        headerGrid = 150;
    }

    // Obtener la altura de cada elemento
    const headerGridHeight = headerGrid;
    const nav2ContainerHeight = nav2Container ? nav2Container.offsetHeight : 0;
    const headerGrandeHeight = headerGrande ? headerGrande.offsetHeight : 0;
    const headerSmallHeight = headerSmall ? headerSmall.offsetHeight : 0;

    // Sumar las alturas
    const sumaTotal = headerGridHeight + nav2ContainerHeight + headerGrandeHeight + headerSmallHeight;

    // Devolver la suma total
    return sumaTotal;
}

//obtener configuracion de la tabla
async function getTablaConfiguracion(nombreTablaConfiguracion) {
    let clase = "ConfiguracionTablas";
    let funcion = "getConfiguracionTablas";

    let datos = {
        tabla: nombreTablaConfiguracion,
    };

    let data = await queryAsync(clase, funcion, datos);
    return data.data;
}

function getArrayOrderBy(configuracion) {
    let order_by_array = [];
    //si configuracion.order_by está vacío, se asigna como sin_orden, si configuracion.order_by contiene DESC o ASC, se asigna como DESC o ASC
    if (configuracion.order_by !== "") {
        let dataOrderBy = configuracion.order_by;
        //eliminar " ORDER BY" del string
        dataOrderBy = dataOrderBy.replace(" ORDER BY ", "");
        
        //convertir en array usando , como separador
        order_by_array = dataOrderBy.split(", ");
    }

    //convertir cada elemento de order_by_array en un objeto con id y orden
    let order_by_array_obj = [];
    for (let order_by of order_by_array) {
        //separar por espacio
        let order_by_split = order_by.split(" ");
        let id = order_by_split[0];
        let orden = order_by_split[1];

        order_by_array_obj[id] = orden;
    }

    return order_by_array_obj;
}