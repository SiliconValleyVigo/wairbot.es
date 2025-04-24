/**
 Si no existe funcionCrear no se muestra el boton de crear
 Si no existe funcionAvanzar no se muestra la paginacion
 */
async function createGrid(
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
    camposInput = {},
    funcionModificacionDeTabla = ""
) {
    camposCard = JSON.stringify(camposCard);

    //convertir a base64 para que no se pierda la informacion, que incluya acentos y caracteres especiales
    let camposCardCodificados = encodeURIComponent(camposCard);
    camposCard = btoa(camposCardCodificados);

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
    let filtroGrid = headerGridOrTable.filtroGrid;
    let botonCrear = headerGridOrTable.botonCrear;
    let cambioGridOrTable = headerGridOrTable.botonCambioGridOrTable;
    let inputMostarPorPagina = headerGridOrTable.inputMostarPorPagina;

    let selectFiltro = "";
    if (filtro) {
        //comprobar si el objeto filtro tiene un elemento con la key localStorage
        if (!filtro.localStorage) {
            let clase = filtro.clase;
            let funcion = filtro.funcion;
            let data = filtro.data;

            let datosFiltro = await queryAsync(clase, funcion, data);
            datosFiltro = datosFiltro.data.menu; //['opcion1', 'opcion2', 'opcion3']

            let funcionVerConFiltro = "filtroMasAplicarBusqueda";

            let options = `<option value="">Todas</option>`;
            for (let dato of datosFiltro) {
                options += `<option value="${dato}">${dato}</option>`;
            }

            selectFiltro = `
                <div class="selectFiltroContainer">
                    <select class="selectFiltro" id="selectFiltro" onchange="${funcionVerConFiltro}(this.value)">
                        ${options}
                    </select>
                </div>
            `;
        } else {
            let datosFiltro = localStorage.getItem(filtro.localStorage);
            datosFiltro = JSON.parse(datosFiltro);

            let funcionVerConFiltro = filtro.getGrid;

            //comprobar si en localStorage hay un filtro activo
            let selectedFilter = "";
            let filtroActivo = localStorage.getItem('filtro_activo');
            if (filtroActivo) {
                selectedFilter = filtroActivo;
            }

            let options = `<option value="">Todas</option>`;
            for (let dato of datosFiltro) {
                //options += `<option value="${dato}">${dato}</option>`;
                let selected = "";
                if (selectedFilter === dato) {
                    selected = "selected";
                }
                options += `<option value="${dato}" ${selected}>${dato}</option>`;
            }

            selectFiltro = `
                <div class="selectFiltroContainer">
                    <select class="selectFiltro" id="selectFiltro" onchange="${funcionVerConFiltro}(1), localStorage.setItem('filtro_activo', this.value)">
                        ${options}
                    </select>
                </div>
            `;
        }
    }

    let botonExportarExcel = `
        <div onclick="exportarExcel('${clase}', '${funcionLeer}')">
            Exportar a Excel
        </div>
    `;

    let botonImportarExcel = `
        <div>
            <label for="inputFile">Importar Excel</label>
            <input type="file" id="inputFile" accept=".xlsx" onchange="importarExcel('${clase}', this.files[0])">
        </div>
    `;

    let botonDescargarPlantillaExcel = `
        <div onclick="descargarPlantillaExcel('${clase}', '${funcionLeer}')">
            Descargar Plantilla
        </div>
    `;

    //crear un dropdown con: cambioGridOrTable, inputMostarPorPagina, filtroGrid, botonExportarExcel, botonImportarExcel, botonDescargarPlantillaExcel, el icono del dropdown está en imgs/tools.svg
    let dropdown = `
        <style>
            .dropdown {
                position: relative;
                display: inline-block;
            }
            .dropbtn {
                border: none;
                cursor: pointer;
                border-radius: 5px;
            }
            .dropdown-content {
                display: none;
                position: absolute;
                background-color: #f9f9f9;
                width: auto;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                z-index: 1;
                padding: 1rem;
                transform: translateX(-30%);
            }

            .dropdown-content div {
                font-size: 1rem;
                margin: 1rem 0;
                cursor: pointer;
            }

            .dropdown-content div:hover {
                color: #007bff;
            }

            .dropdown-content hr {
                margin: 0;
            }    
        </style>
        <div class="dropdown">
            <button class="dropbtn" onclick="mostrarDropdownTools()">
                <img src="imgs/tools.svg" alt="tools" width="25" height="25">
            </button>
            <div class="dropdown-content" id="dropdownContent" style="display: none;">
                ${cambioGridOrTable}
                <hr>
                ${inputMostarPorPagina}
                <hr>
                ${filtroGrid}
                <hr>
                ${botonExportarExcel}
                <hr>
                ${botonImportarExcel}
                <hr>
                ${botonDescargarPlantillaExcel}
            </div>
        </div>
    `;

    let grid = `
    ${selectFiltro}
    <div class="headerGrid headerGrid${funcionCrear}">
        <div class="tituloGrid">${titulo}</div>
        <div class="crearMasTituloHeaderGrid">
            ${botonCrear}
            ${dropdown}
            ${paginacion}
        </div>
    </div>
    <div class="grid grid${funcionCrear}">
        ${cards}
    </div>
    `;

    dataListColeccion = [];

    return grid;
}

async function mostrarDropdownTools() {
    let dropdownContent = document.getElementById("dropdownContent");
    if (dropdownContent.style.display === "none") {
        dropdownContent.style.display = "block";
    } else {
        dropdownContent.style.display = "none";
    }
}

async function exportarExcel(clase, funcionLeer) {
    let funcion = funcionLeer + "Excel";
    let data = {};

    await queryAsync(clase, funcion, data);

    //descargar el archivo que está en la url actual + public + tablaExportada.xlsx
    let url = window.location.href;
    url += "api/public/tablaExportada.xlsx";
    window.open(url, '_blank');
}

/*
insertarDatosDesdeExcel
tabla
excelBase64
reiniciarTabla
*/

async function descargarPlantillaExcel(clase, funcionLeer) {
    let funcion = 'plantillaExcel';
    let data = {};

    await queryAsync(clase, funcion, data);

    //descargar el archivo que está en la url actual + public + tablaExportada.xlsx
    let url = window.location.href;
    url += "api/public/plantillaExcel.xlsx";
    window.open(url, '_blank');
}

async function importarExcel(clase, file) {
    let funcion = "insertarDatosDesdeExcel";
    let reiniciarTabla = false;

    //la talba es la clase con la primera letra en minúscula
    clase = clase.charAt(0).toLowerCase() + clase.slice(1);

    //si hay letras mayúsculas en el nombre de la clase, cambiar las por "_" + letra en minúscula
    let mayusculas = clase.match(/[A-Z]/g);
    if (mayusculas) {
        for (let letra of mayusculas) {
            clase = clase.replace(letra, "_" + letra.toLowerCase());
        }
    }

    let reader = new FileReader();
    reader.onload = async function(e) {
        let excelBase64 = e.target.result;
        // Eliminar el prefijo 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'
        let base64Index = excelBase64.indexOf('base64,') + 'base64,'.length;
        excelBase64 = excelBase64.substring(base64Index);

        let data = {
            tabla: clase,
            excel: excelBase64,
            reiniciar: reiniciarTabla
        };

        await queryAsync(clase, funcion, data);

        //recargar la tabla
        //obtener location de localStorage
        let location = localStorage.getItem("location");
        //obtener la función de recarga de locationHandlers
        let funcionRecarga = locationHandlers[location];
        //ejecutar la función de recarga
        funcionRecarga();
    };

    reader.readAsDataURL(file);
}

async function createHeaderGridOrTable(
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
) {
    //si camposInput es un objeto, convertirlo a string y a base64 encodeURIComponent
    if (typeof camposInput === "object") {
        camposInput = JSON.stringify(camposInput);
        camposInput = encodeURIComponent(camposInput);
        camposInput = btoa(camposInput);
    }

    let parametrosRecargar = `
        '${location}',
        '${clase}',
        '${funcionLeer}',
        '${funcionAbrir}',
        '${funcionCrear}',
        '${rol}',
        '${titulo}',
        '${camposCard}',
        '${nombreTablaConfiguracion}',
        '${camposInput}',
        false,
        false, 
        {},
        '${funcionModificacionDeTabla}'
    `;

    let paginacion = "";
    let filtroGrid = "";
    let inputMostarPorPagina = "";
    let cambioGridOrTable = "";

    if (hayPaginacion) {
        if (grid_or_table === "grid") {
            cambioGridOrTable = `
                <div class="cambioGridOrTable">
                    <div
                        onclick="
                            actualizarConfiguracion('${nombreTablaConfiguracion}', 'table', 'grid_or_table');
                            recargar(${parametrosRecargar})
                        "
                    >
                        Cambiar a Tabla
                    </div>
                </div>
            `;
        }

        if (grid_or_table === "table") {
            cambioGridOrTable = `
                <div class="cambioGridOrTable">
                    <div
                        onclick="
                            actualizarConfiguracion('${nombreTablaConfiguracion}', 'grid', 'grid_or_table');
                            recargar(${parametrosRecargar})
                        "
                    >
                        Cambiar a Cuadrícula
                    </div>
                </div>
            `;
        }

        if (grid_or_table === "grid") {
            //crear un select con los campos
            let options = "<option value=''>Sin filtro</option>";
            for (let campo of campos) {
                options += `<option value="${campo}">${campo}</option>`;
            }

            filtroGrid = `
            <div class="filtroGrid" style="display: flex; flex-direction: column; align-items: center;">
                <span style="display: flex; flex-direction: row; align-items: center;">
                    <label 
                        for="columnasFiltrosGrid"
                        style="margin-right: 0.5em;"
                    >Filtrar:</label>
                    <select 
                        id="columnasFiltrosGrid" onchange="selectElementosDisponiblesEnColumna(this.value, '${nombreTablaConfiguracion}')"
                        style="padding: 0.5em; margin-right: 1em; max-width: 100px;"
                    >
                        ${options}
                    </select>
                    <select
                        id="searchFiltroGrid"
                        onchange="aplicarBusquedaFiltrosGrid(this.value, '${nombreTablaConfiguracion}')"
                        style="padding: 0.5em; max-width: 100px;"
                    >
                    </select>
                    <button
                        onclick="aplicarBusquedaFiltrosGrid('', '')"
                        style = "border: none; background: transparent; cursor: pointer; margin-left: 0.5em;"
                    >X</button>
                </span>
            </div>
            `;
        }

        inputMostarPorPagina = `
            <div style=" display: flex; flex-direction: row; justify-content: center; align-items: center; gap: 10px">
                <label for="resultadoPorPagina">Mostrar:</label>
                <input 
                    type="number" id="rows_per_page" value="${rows_per_page}" min="1" max="1000"
                    onchange="
                        actualizarConfiguracion('${nombreTablaConfiguracion}', this.value, 'rows_per_page');
                        recargar(${parametrosRecargar})"
                    style="padding: 0.5em; width: calc(100% - 1em);"
                >
            </div>
        `;

        paginacion = `
        <div class="paginacion">
            <div style="display: flex; flex-direction: row; max-height: 35px;">
                <div 
                class="paginaAnterior"
                onclick="retrocerPagina(${parametrosRecargar})"
                >
                    &lt;&lt;
                </div>
                <div class="paginaActual">
                    <span id="paginaActual">${paginaActual}</span>/<span id="totalPaginas">${totalPaginas}</span>
                </div>
                <div
                    class="paginaSiguiente"
                    onclick="avanzarPagina(${parametrosRecargar})"
                >
                    &gt;&gt;
                </div>
            </div>
        </div>
        `;
    }

    let botonCrear = "";
    if (funcionCrear) {
        botonCrear = `<div class="botonGrid" onclick="${funcionCrear}()">+</div>`;
    }

    return {
        botonCambioGridOrTable: cambioGridOrTable,
        inputMostarPorPagina: inputMostarPorPagina,
        paginacion: paginacion,
        filtroGrid: filtroGrid,
        botonCrear: botonCrear
    };
}

//filtro + aplicarBusqueda();
async function filtroMasAplicarBusqueda(value) {
    let buscador = document.getElementById("buscador");
    buscador.value = value;

    aplicarBusqueda();

    //esperar a que se aplique la busqueda
    await new Promise(r => setTimeout(r, 1000));

    //borrar busqueda
    buscador.value = "";

    //insertar la opcion seleccionada en el select
    let selectFiltro = document.getElementById("selectFiltro");
    selectFiltro.value = value;
}

async function selectElementosDisponiblesEnColumna(campo, nombreTablaConfiguracion) {
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

    let searchFiltroGrid = document.getElementById("searchFiltroGrid");
    searchFiltroGrid.innerHTML = "";
    let options = "<option value=''>Sin filtro</option>";
    for (let elemento of elementos) {
        options += `<option value="${elemento}">${elemento}</option>`;
    }
    searchFiltroGrid.innerHTML = options;
}

async function aplicarBusquedaFiltrosGrid(value, nombreTablaConfiguracion) {
    //si nombreTablaConfiguracion está vacío o no existe, borrar la busqueda y salir
    if (!nombreTablaConfiguracion) {
        let buscador = document.getElementById("buscador");
        buscador.value = "";

        aplicarBusqueda();
        return;
    }
    let columna = document.getElementById("columnasFiltrosGrid").value;
    //si value o columna están vacíos o no existen, borrar la busqueda y salir
    if (!columna) {
        let buscador = document.getElementById("buscador");
        buscador.value = "";
        aplicarBusqueda();
        return;
    }

    let busqueda = columna + "::" + value;

    let buscador = document.getElementById("buscador");
    buscador.value = busqueda;

    await aplicarBusqueda();

    //esperar a que cargue la grid y insertar el filtro y la columna
    await new Promise(r => setTimeout(r, 1000));

    let columnasFiltrosGrid = document.getElementById("columnasFiltrosGrid");
    columnasFiltrosGrid.value = columna;

    //cargo los elementos de la columna
    await selectElementosDisponiblesEnColumna(columna, nombreTablaConfiguracion);

    let searchFiltroGrid = document.getElementById("searchFiltroGrid");
    searchFiltroGrid.value = value;
}

async function actualizarConfiguracion(nombreTablaConfiguracion, valor, metodo) {
    if (metodo === "rows_per_page") {
        let clase = "ConfiguracionTablas";
        let funcion = "cambiarRowPerPage";
        let data = {
            tabla: nombreTablaConfiguracion,
            row_per_page: valor,
        };

        await queryAsync(clase, funcion, data);
    }

    if (metodo === "grid_or_table") {
        let clase = "ConfiguracionTablas";
        let funcion = "cambiarGridOrTabla";
        let data = {
            tabla: nombreTablaConfiguracion,
            grid_or_table: valor,
        };

        await queryAsync(clase, funcion, data);
    }
}