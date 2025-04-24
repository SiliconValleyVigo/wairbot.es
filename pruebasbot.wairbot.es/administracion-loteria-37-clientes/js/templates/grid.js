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
        <div onclick="exportarExcel('${clase}', '${funcionLeer}')" style="cursor: pointer; margin-left: 1em;">
            <img src="imgs/excel.svg" alt="exportarExcel" width="30" height="30">
        </div>
    `;

    let grid = `
    ${selectFiltro}
    <div class="headerGrid headerGrid${funcionCrear}">
        <div class="crearMasTituloHeaderGrid">
            ${botonCrear}
            ${botonExportarExcel}
            <div class="tituloGrid">${titulo}</div>
        </div>
        ${filtroGrid}
        ${paginacion}
    </div>
    <div class="grid grid${funcionCrear}">
        ${cards}
    </div>
    `;

    dataListColeccion = [];

    return grid;
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

    if (hayPaginacion) {
        let cambioGridOrTable = "";
        if (grid_or_table === "grid") {
            cambioGridOrTable = `
                <div class="cambioGridOrTable">
                    <button
                        onclick="
                            actualizarConfiguracion('${nombreTablaConfiguracion}', 'table', 'grid_or_table');
                            recargar(${parametrosRecargar})
                        "
                        style="
                            border: none; background: transparent; cursor: pointer;
                            margin-right: 0.5em; display: flex; justify-content: center;
                            align-items: center;
                        "
                    >
                        <img src="imgs/tabla.svg" alt="table" width="20" height="20">
                    </button>
                </div>
            `;
        }

        if (grid_or_table === "table") {
            cambioGridOrTable = `
                <div class="cambioGridOrTable">
                    <button
                        onclick="
                            actualizarConfiguracion('${nombreTablaConfiguracion}', 'grid', 'grid_or_table');
                            recargar(${parametrosRecargar})
                        "
                        style="
                            border: none; background: transparent; cursor: pointer;
                            margin-right: 0.5em; display: flex; justify-content: center;
                            align-items: center;
                        "
                    >
                        <img src="imgs/cuadricula.svg" alt="grid" width="20" height="20">
                    </button>
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
            <div class="filtroGrid" style="display: flex; flex-direction: column; width: 95%; align-items: center;">
                <div style="display: flex; flex-direction: row; align-items: center;">
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
                </div>
            </div>
            `;
        }

        paginacion = `
        <div class="paginacion">
            ${cambioGridOrTable}

            <div style="display: flex; flex-direction: column;">
                <label for="resultadoPorPagina">Mostrar:</label>
                <input 
                    type="number" id="rows_per_page" value="${rows_per_page}" min="1" max="1000"
                    onchange="
                        actualizarConfiguracion('${nombreTablaConfiguracion}', this.value, 'rows_per_page');
                        recargar(${parametrosRecargar})
                    "
                >
            </div>

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
    /*
    Utilidades
    obtenerElemenosDeUnaColumnaSinRepetir
    tabla
    columna
    */
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