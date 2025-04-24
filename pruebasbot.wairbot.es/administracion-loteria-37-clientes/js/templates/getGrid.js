async function getGrid(
    location, 
    clase, 
    funcionLeer, 
    funcionAbrir, 
    funcionCrear, 
    rol, 
    titulo, 
    hayPaginacion, 
    camposCard, 
    nPagina = 1,
    nombreTablaConfiguracion = "",
    camposInput = {},
    funcionModificacionDeTabla = "",
    filtro = false,
    returnGrid = false, 
    datosFuncionLeer = {},
){
    localStorage.setItem("location", location);

    //obtener los datos de los agentes
    let datos = {
        nPagina: nPagina,
        rol: rol
    };

    //si datos funcion leer no esta vacio se añade a datos
    if(Object.keys(datosFuncionLeer).length !== 0){
        datos = {...datos, ...datosFuncionLeer};
    }

    //comprobar si el input buscador esta vacio
    let buscador = document.getElementById("buscador");
    buscador = buscador.value;
    if (buscador !== "" && buscador !== null && buscador !== undefined) {
        datos['search'] = buscador;
    }

    //comprobar si hay que añadir parametros extra
    let estaRecargando = false;
    let modalBodyContenido = document.querySelector(`.headerGrid${funcionCrear}`);
    if (modalBodyContenido !== null) {
        if(funcionLeer.includes("listarPor")){
            estaRecargando = true;
            //eliminar el listarPor de funcionLeer
            let datoExtra = funcionLeer.split("listarPor")[1];
            //todo en minusculas y con "id_" delante
            datoExtra = "id_" + datoExtra.toLowerCase();
            
            //obtener el id del elemento que es el id del elemento html con la clase modalTitulo
            let id = document.querySelector(".modalTitulo").id;
            datos[datoExtra] = id;
        }
    }

    let data = await queryAsync(clase, funcionLeer, datos);

    //obtener un arrya de las keys de data.data.data[0]
    //comprobar si data.data.data[0] existe
    let campos = [];
    if(data.data.data.length !== 0){
        campos = Object.keys(data.data.data[0]);
        campos = campos.filter((campo) => campo !== "id" && campo !== "timestamp");
    }

    let paginaActual = data.data.paginaActual;
    let totalPaginas = data.data.paginasTotales;
    let rows_per_page = data.data.rows_per_page;
    let grid_or_table = data.data.grid_or_table;

    data = data.data.data;

    if(grid_or_table === "grid"){
        //obtener las cards con los datos
        let cards = getCards(data, funcionAbrir, camposCard);

        let grid = await createGrid(
            cards,
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
            filtro,
            campos,
            nombreTablaConfiguracion,
            camposInput,
            funcionModificacionDeTabla
        );

        if(returnGrid){
            return grid;
        }

        //comprobar si existe la clase modalBodyContenido y si existe eliminar las clases headerGrid y grid e insertar grid en modalBodyContenido en el mismo lugar que estaba grid
        modalBodyContenido = document.querySelector(`.headerGrid${funcionCrear}`);
        let modal = document.getElementById("modal");

        if (modalBodyContenido !== null && modal !== null) {
            let tablaParaSustitir = document.querySelector(`.tableContainer${funcionCrear}`);
            //si no es null eliminar grid
            if(tablaParaSustitir !== null){
                tablaParaSustitir.remove();
            }
            // obtener la posicion de headerGrid${funcionCrear}
            let headerGridParaSustituir = document.querySelector(`.headerGrid${funcionCrear}`);
            let gridParaSustitir = document.querySelector(`.grid${funcionCrear}`);
            let parent = headerGridParaSustituir.parentNode;
            let posicion = Array.prototype.indexOf.call(parent.children, headerGridParaSustituir);
        
            if(estaRecargando === false){
                // eliminar headerGrid y grid
                headerGridParaSustituir.remove();
                gridParaSustitir.remove();
            }
        
            // convertir grid de string a nodos
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = grid;
            let gridNodes = Array.from(tempDiv.childNodes);
        
            // insertar cada nodo en la misma posición
            gridNodes.forEach((node, index) => {
                if (posicion + index >= parent.children.length) {
                    parent.appendChild(node);
                } else {
                    parent.insertBefore(node, parent.children[posicion + index]);
                }
            });

            //asegurarse de que solo hay un .headerGrid${funcionCrear} y si hay más eliminar todos menos el último
            let headerGrids = document.querySelectorAll(`.headerGrid${funcionCrear}`);
            if(headerGrids.length > 1){
                for(let i = 0; i < headerGrids.length - 1; i++){
                    headerGrids[i].remove();
                }
            }

            //asegurarse de que solo hay un .grid${funcionCrear} y si hay más eliminar todos menos el último
            let grids = document.querySelectorAll(`.grid${funcionCrear}`);
            if(grids.length > 1){
                for(let i = 0; i < grids.length - 1; i++){
                    grids[i].remove();
                }
            }
        } else {
            let administrador = document.getElementById(rol);
            administrador.innerHTML = grid;
        }
    }

    if(grid_or_table === "table"){
        let table = await createTable(
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
        );

        if(returnGrid){
            return table;
        }

        modalBodyContenido = document.querySelector(`.headerGrid${funcionCrear}`);
        let modal = document.getElementById("modal");

        if (modalBodyContenido !== null && modal !== null) {
            // obtener la posicion de headerGrid${funcionCrear}
            let gridParaSustitir = document.querySelector(`.grid${funcionCrear}`);
            //si no es null eliminar grid
            if(gridParaSustitir !== null){
                gridParaSustitir.remove();
            }

            let headerGridParaSustituir = document.querySelector(`.headerGrid${funcionCrear}`);
            let tablaParaSustitir = document.querySelector(`.tableContainer${funcionCrear}`);
            let parent = headerGridParaSustituir.parentNode;
            let posicion = Array.prototype.indexOf.call(parent.children, headerGridParaSustituir);
        
            if(estaRecargando === false){
                // eliminar headerGrid y grid
                headerGridParaSustituir.remove();
                tablaParaSustitir.remove();
            }
        
            // convertir grid de string a nodos
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = table;
            let gridNodes = Array.from(tempDiv.childNodes);
        
            // insertar cada nodo en la misma posición
            gridNodes.forEach((node, index) => {
                if (posicion + index >= parent.children.length) {
                    parent.appendChild(node);
                } else {
                    parent.insertBefore(node, parent.children[posicion + index]);
                }
            });

            //asegurarse de que solo hay un .headerGrid${funcionCrear} y si hay más eliminar todos menos el último
            let headerGrids = document.querySelectorAll(`.headerGrid${funcionCrear}`);
            if(headerGrids.length > 1){
                for(let i = 0; i < headerGrids.length - 1; i++){
                    headerGrids[i].remove();
                }
            }

            //asegurarse de que solo hay un .grid${funcionCrear} y si hay más eliminar todos menos el último
            let grids = document.querySelectorAll(`.tableContainer${funcionCrear}`);
            if(grids.length > 1){
                for(let i = 0; i < grids.length - 1; i++){
                    grids[i].remove();
                }
            }
        } else {
            let administrador = document.getElementById(rol);
            administrador.innerHTML = table;
        }

        //esperar a que la tabla se cargue con un delay de 1000ms
        setTimeout(() => {
            if(funcionModificacionDeTabla !== ""){
                window[funcionModificacionDeTabla]();
            }
        }, 1000);
    }

    return data;
}

function getCards(data, funcionAbrir, camposCard) {
    let cards = "";
    for (let card of data) {
        let imagen = card["imagen"] || false;
        let info1 = card[camposCard.info1] || "";
        let info2 = card[camposCard.info2] || "";
        let info3 = card[camposCard.info3] || "";
        let info4 = card[camposCard.info4] || "";
        let info5 = card[camposCard.info5] || "";
        let id = card["id"]
        let oculto = card[camposCard.oculto] || "";

        cards += createCard(funcionAbrir, imagen, info1, info2, info3, info4, info5, id, oculto);
    }

    return cards;
}


function avanzarPagina(
    location, 
    clase, 
    funcionLeer, 
    funcionAbrir, 
    funcionCrear, 
    rol, 
    titulo,
    camposCard,
    nombreTablaConfiguracion,
    camposInput = {},
    filtro = false,
    returnGrid = false,
    datosFuncionLeer = {},
    funcionModificacionDeTabla = ""
) {
    let hayPaginacion = true;
    // Obtener el contenedor principal usando la clase
    let headerGrid = document.querySelector(`.headerGrid${funcionCrear}`);

    // Obtener los elementos dentro del contenedor
    let paginaActual = headerGrid.querySelector("#paginaActual").innerText;
    let totalPaginas = headerGrid.querySelector("#totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    
    let camposCardDecodificados = atob(camposCard);
    camposCard = decodeURIComponent(camposCardDecodificados);
    camposCard = JSON.parse(camposCard);

    let camposInputDecodificados = atob(camposInput);
    camposInput = decodeURIComponent(camposInputDecodificados);
    camposInput = JSON.parse(camposInput);

    getGrid(
        location, 
        clase, 
        funcionLeer, 
        funcionAbrir, 
        funcionCrear, 
        rol, 
        titulo, 
        hayPaginacion, 
        camposCard, 
        parseInt(paginaActual) + 1,
        nombreTablaConfiguracion, 
        camposInput,
        funcionModificacionDeTabla,
        filtro,
        returnGrid, 
        datosFuncionLeer
    );
}

function retrocerPagina(
    location, 
    clase, 
    funcionLeer, 
    funcionAbrir, 
    funcionCrear, 
    rol, 
    titulo,
    camposCard,
    nombreTablaConfiguracion,
    camposInput = {},
    filtro = false,
    returnGrid = false,
    datosFuncionLeer = {},
    funcionModificacionDeTabla = ""
){
    let hayPaginacion = true;
    // Obtener el contenedor principal usando la clase
    let headerGrid = document.querySelector(`.headerGrid${funcionCrear}`);
    
    // Obtener los elementos dentro del contenedor
    let paginaActual = headerGrid.querySelector("#paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }

    let camposCardDecodificados = atob(camposCard);
    camposCard = decodeURIComponent(camposCardDecodificados);
    camposCard = JSON.parse(camposCard);

    let camposInputDecodificados = atob(camposInput);
    camposInput = decodeURIComponent(camposInputDecodificados);
    camposInput = JSON.parse(camposInput);

    getGrid(
        location, 
        clase, 
        funcionLeer, 
        funcionAbrir, 
        funcionCrear, 
        rol, 
        titulo, 
        hayPaginacion, 
        camposCard, 
        parseInt(paginaActual) - 1, 
        nombreTablaConfiguracion, 
        camposInput,
        funcionModificacionDeTabla,
        filtro,
        returnGrid, 
        datosFuncionLeer
    );
}

function recargar(
    location, 
    clase, 
    funcionLeer, 
    funcionAbrir, 
    funcionCrear, 
    rol, 
    titulo,
    camposCard,
    nombreTablaConfiguracion,
    camposInput = {},
    filtro = false,
    returnGrid = false,
    datosFuncionLeer = {},
    funcionModificacionDeTabla = ""
) {
    let hayPaginacion = true;
    let paginaActual = document.getElementById("paginaActual").innerText;

    if(camposCard !== ""){
        let camposCardDecodificados = atob(camposCard);
        camposCard = decodeURIComponent(camposCardDecodificados);
        camposCard = JSON.parse(camposCard);
    }

    let camposInputDecodificados = atob(camposInput);
    camposInput = decodeURIComponent(camposInputDecodificados);
    camposInput = JSON.parse(camposInput);

    getGrid(
        location, 
        clase, 
        funcionLeer, 
        funcionAbrir, 
        funcionCrear, 
        rol, 
        titulo, 
        hayPaginacion, 
        camposCard, 
        parseInt(paginaActual), 
        nombreTablaConfiguracion, 
        camposInput,
        funcionModificacionDeTabla,
        filtro,
        returnGrid, 
        datosFuncionLeer
    );
}

async function crearCamposDeFormulario(campos, data = null, botonEliminar = null){
    let camposResult = [];
    for(let campo of campos){
        let tipo = campo.tipo;
        let id = campo.id;
        let label = campo.label;
        let value = (data && data.hasOwnProperty(id)) ? data[id] : "";

        //si value es "" comprueba si existe campo.default y lo asigna
        if(value === "" && campo.hasOwnProperty("defaultValue")){
            value = campo.defaultValue;
        }

        //getInputTextArea, getInputFile
        if(tipo === "text"){
            let input = getInputText(id, label, value);
            camposResult.push(input);
        } else if(tipo === "dataList"){
            let options = campo.options;

            //comprobar si existe el campo onchange
            if(campo.hasOwnProperty("onchange")){
                let onchange = campo.onchange;
                let input = getSimpleDataList(id, label, options, value, onchange);
                camposResult.push(input);
            } else {
                let input = getSimpleDataList(id, label, options, value)
                camposResult.push(input);
            }
        } else if(tipo === "textarea"){
            let input = getInputTextArea(id, label, value);
            camposResult.push(input);
        } else if(tipo === "file"){
            let input = getInputFile(id, label, value);
            camposResult.push(input);
        } else if(tipo === "select"){
            let options = campo.options;
            let input = getSelect(id, label, options, value);
            camposResult.push(input);
        } else if(tipo === "phone_link"){
            let input = getPhoneLink(id, label, value);
            camposResult.push(input);
        } else if(tipo === "date"){
            let input = getInputDate(id, label, value)
            camposResult.push(input);
        } else if(tipo === "dataListApi"){
            //sepsarar options por ::
            let optionsArray = campo.options.split("::");
            let apiClass = optionsArray[0];
            let apiFunction = optionsArray[1];
            let input = await getDataListApi(id, label, value, apiClass, apiFunction);
            camposResult.push(input);
        } else if(tipo === "dataListCompleto"){
            let optionsArray = campo.options.split("::");
            let nombre_select = optionsArray[0];
            let tabla_select = optionsArray[1];
            
            let id_input_relacionado = "";
            let onchangeFunction = "";

            if(optionsArray.length === 4){
                id_input_relacionado = optionsArray[2];
                onchangeFunction = optionsArray[3];
            }
        
            let input = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);
            camposResult.push(input);
        } else if(tipo === "number"){
            let input = getInputNumber(id, label, value);
            camposResult.push(input);
        } else if(tipo === "time"){
            let input = getInputTime(id, label, value);
            camposResult.push(input);
        } else if(tipo === "json"){
            let input = getInputJson(id, label, value);
            camposResult.push(input);
        } else if(tipo === "coin"){
            let input = getInputCoin(id, label, value);
            camposResult.push(input);
        }
    }

    if(botonEliminar !== null){
        let boton = getBoton(botonEliminar.texto, botonEliminar.funcion, botonEliminar.id);
        camposResult.push(boton);
    }

    return camposResult;
}

function getInputCoin(id, label, value) {
    let input = `
        <div class="modalInputGroup">
            <div class="modalLabel">${label}</div>
            <input type="text" id="${id}" value="${value}">
        </div>
        <script>
            document.getElementById('${id}').addEventListener('input', function(event) {
                let value = event.target.value;
                // Reemplazar comas por puntos
                value = value.replace(/,/g, '.');
                // Permitir solo números y un punto
                value = value.replace(/[^0-9.]/g, '');
                // Permitir solo un punto
                let parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                event.target.value = value;
            });
        </script>
    `;
    return input;
}

function getInputJson(id, label, value) {
    if (id === "json_date") {
        // Parsear el valor a JSON
        let dates = [];
        //comprobar si value es un json
        if (value !== "" && value !== null) {
            dates = JSON.parse(value);
        }

        // Generar un div con un input tipo date por cada fecha en el array y un botón para generar más inputs tipo date
        let input = `
            <div id="json_date_container" style="display: flex; flex-direction: column; justify-content: center; align-items: center; width: 100%;">
                <label for="${id}" style="margin-bottom: 10px;">${label}</label>
        `;

        if (dates.length === 0) {
            input += `
                <input 
                    type="date"
                    id="${id}_0" 
                    style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white; margin-bottom: 10px;"
                    onchange="saveDatesToLocalStorage()">
            `;
        } else {
            dates.forEach((date, index) => {
                input += `
                    <input 
                        type="date"
                        id="${id}_${index}" 
                        value="${date}"
                        style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white; margin-bottom: 10px;"
                        onchange="saveDatesToLocalStorage()">
                `;
            });
        }

        input += `
                <div id="additional_dates" style="width: 100%;"></div>
                <button 
                    id="add_date_button"
                    onclick="addInputJsonDate()"
                    style="border: none; background-color: #007bff; color: white; padding: 10px; margin-top: 10px; margin-bottom: 10px;">
                    Añadir Fecha
                </button>
                <input type="hidden" id="${id}" value='${JSON.stringify(dates)}'>
            </div>
        `;

        return input;
    }
}

function addInputJsonDate(){
    console.log("addInputJsonDate");
    let div = document.createElement("div");
    div.style.display = "flex";
    div.style.flexDirection = "column";
    div.style.justifyContent = "center";
    div.style.alignItems = "center";
    div.style.width = "100%";

    let input = document.createElement("input");
    input.type = "date";
    input.style.marginBottom = "10px";
    input.style.width = "90%";
    input.style.padding = "10px";
    input.style.border = "1px solid #ccc";
    input.style.borderRadius = "5px";
    input.style.backgroundColor = "white";
    input.onchange = saveDatesToLocalStorage;
    
    div.appendChild(input);

    let additional_dates = document.getElementById("additional_dates");
    additional_dates.appendChild(div);
}

function saveDatesToLocalStorage(){
    let dates = [];
    let inputs = document.querySelectorAll('#json_date_container input[type="date"]');
    inputs.forEach(input => {
        if(input.value) {
            dates.push(input.value);
        }
    });
    localStorage.setItem('json_date', JSON.stringify(dates));
}

async function getDataListApi(id, label, value, apiClass, apiFunction){
    let clase = apiClass;
    let funcion = apiFunction;
    let data = await queryAsync(clase, funcion, {});

    let options = data.data;

    let input = `
        <data-search 
            id="${id}"
            data='${JSON.stringify(options)}'
            default-value="${value}"
            label="${label}"
            style="    
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                width: 100%;
            "
        >
        </data-search>
    `;

    return input;
}

function getDatosFormulario(campos, camposExtra = null){
    let datos = {};
    for(let campo of campos){
        let tipo = campo.tipo;
        let id = campo.id;

        let value = "";
        if(tipo === "dataListApi"){
            value = document.getElementById(id).getSelectedKey();
        }else{
            let element = document.getElementById(id); 
            value = element.value;
        }

        datos[id] = value;
    }

    if(camposExtra !== null){
        for(let campo in camposExtra){
            datos[campo] = camposExtra[campo];
        }
    }

    return {data: datos};
}