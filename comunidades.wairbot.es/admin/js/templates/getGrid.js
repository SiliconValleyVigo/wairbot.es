
async function getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina = 1, filtro = false) {
    localStorage.setItem("location", location);

    //obtener los datos de los agentes
    let datos = {
        nPagina: nPagina,
        rol: rol
    };

    //comprobar si el input buscador esta vacio
    let buscador = document.getElementById("buscador");
    buscador = buscador.value;
    if (buscador !== "" && buscador !== null && buscador !== undefined) {
        datos['search'] = buscador;
    }

    console.log({clase, funcionLeer, datos});

    let data = await queryAsync(clase, funcionLeer, datos);

    let paginaActual = data.data.paginaActual;
    let totalPaginas = data.data.paginasTotales;

    data = data.data.data;

    //obtener las cards con los datos
    let cards = getCards(data, funcionAbrir, camposCard);

    let grid = createGrid(
        cards,
        hayPaginacion,
        paginaActual,
        totalPaginas,
        titulo,
        location,
        clase,
        funcionCrear,
        funcionLeer,
        funcionAbrir,
        rol,
        camposCard,
        filtro
    );

    let administrador = document.getElementById(rol);
    administrador.innerHTML = grid;
}

function getCards(data, funcionAbrir, camposCard) {
    console.log(data);
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


function avanzarPagina(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, camposCard, filtro = false) {
    let hayPaginacion = true;
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    
    let camposCardDecodificados = atob(camposCard);
    camposCard = decodeURIComponent(camposCardDecodificados);
    camposCard = JSON.parse(camposCard);

    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, parseInt(paginaActual) + 1, filtro);
}

function retrocerPagina(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, camposCard, filtro = false) {
    let hayPaginacion = true;
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }

    let camposCardDecodificados = atob(camposCard);
    camposCard = decodeURIComponent(camposCardDecodificados);
    camposCard = JSON.parse(camposCard);

    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, parseInt(paginaActual) - 1, filtro);
}

/*
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
function crearCamposDeFormulario(campos, data = null, botonEliminar = null){
    console.log({campos, data});
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
            let input = getSimpleDataList(id, label, options, value)
            camposResult.push(input);
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
        }
    }

    if(botonEliminar !== null){
        let boton = getBoton(botonEliminar.texto, botonEliminar.funcion, botonEliminar.id);
        camposResult.push(boton);
    }

    return camposResult;
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
        let id = campo.id;
        let value = document.getElementById(id).value;
        datos[id] = value;
    }

    if(camposExtra !== null){
        for(let campo in camposExtra){
            datos[campo] = camposExtra[campo];
        }
    }

    return {data: datos};
}