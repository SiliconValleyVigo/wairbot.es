/*
returnGrid = true
datosFuncionLeer = {}
(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina, nombreTablaConfiguracion, camposInput, funcionModificacionDeTabla, filtro, returnGrid, datosFuncionLeer);
*/
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

        let administrador = document.getElementById(rol);
        administrador.innerHTML = grid;
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

        let administrador = document.getElementById(rol);
        administrador.innerHTML = table;

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
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
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
    let paginaActual = document.getElementById("paginaActual").innerText;
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

/*
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

let campos = [
    {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
    },{
        tipo: "dataList",
        id: "id_cliente",
        label: "Cliente",
        options: [],
    }
]

let botonEliminar = {
    texto: "Eliminar",
    funcion: "eliminarCliente",
    id: 10
}
*/
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
        }
    }

    if(botonEliminar !== null){
        let boton = getBoton(botonEliminar.texto, botonEliminar.funcion, botonEliminar.id);
        camposResult.push(boton);
    }

    return camposResult;
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

/*
    let email = document.getElementById("email").value;

    let datos = {
        data: {
            email: email,
            rol: 'AGENTE'
        }
    };

    camposExtra = {
        rol: 'AGENTE'
    }
*/
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

/*
Quiero hacer un web component con las siguientes caracteristicas:
Debe recibir dos parametros: 
    - data: un objeto con los datos a mostrar en formato {key: value}
    - key: la key de referencia en el objeto data
    - Se debe obtener un input donde al buscar tanto por key como por value se muestre el valor de la key en el input pero al recoger su valor se debe recoger la key
*/