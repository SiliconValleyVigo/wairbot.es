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
    console.log(data);

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
        //camposCard.oculto pasar de id_alumno&id_curso a "id_alumno::card["id_alumno"]&id_curso::card["id_curso"]
        let oculto = "";
        console.log(camposCard.oculto);
        if(camposCard.oculto !== "" && camposCard.oculto !== undefined){
            let ocultoArray = camposCard.oculto.split("&");
            for(let o of ocultoArray){
                let oSplit = o.split("::");
                oculto += `${oSplit[0]}::${card[oSplit[0]]}&`;
            }

            oculto = oculto.slice(0, -1);
        }

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