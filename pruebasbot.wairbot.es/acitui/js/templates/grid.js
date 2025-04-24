/**
 Si no existe funcionCrear no se muestra el boton de crear
 Si no existe funcionAvanzar no se muestra la paginacion
 */
function createGrid(
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
    filtro = false
) {
    let paginacion = "";
    if(hayPaginacion){
        camposCard = JSON.stringify(camposCard);

        //convertir a base64 para que no se pierda la informacion, que incluya acentos y caracteres especiales
        let camposCardCodificados = encodeURIComponent(camposCard);
        camposCard = btoa(camposCardCodificados);
        
        paginacion = `
        <div class="paginacion">
            <div 
                class="paginaAnterior"
                onclick="retrocerPagina('${location}', '${clase}', '${funcionLeer}', '${funcionAbrir}', '${funcionCrear}', '${rol}', '${titulo}', '${camposCard}')"
            >
                &lt;&lt;
            </div>
            <div class="paginaActual">
                <span id="paginaActual">${paginaActual}</span>/<span id="totalPaginas">${totalPaginas}</span>
            </div>
            <div
                class="paginaSiguiente"
                onclick="avanzarPagina('${location}', '${clase}', '${funcionLeer}', '${funcionAbrir}', '${funcionCrear}', '${rol}', '${titulo}', '${camposCard}')"
            >
                &gt;&gt;
            </div>
        </div>
        `;
    }

    let botonCrear = "";
    if(funcionCrear){
        botonCrear = `<div class="botonGrid" onclick="${funcionCrear}()">+</div>`;
    }

    let selectFiltro = "";
    if(filtro){
        let datosFiltro = localStorage.getItem(filtro.localStorage);
        datosFiltro = JSON.parse(datosFiltro);

        let funcionVerConFiltro = filtro.getGrid;

        //comprobar si en localStorage hay un filtro activo
        let selectedFilter = "";
        let filtroActivo = localStorage.getItem('filtro_activo');
        if(filtroActivo){
            selectedFilter = filtroActivo;
        }

        let options = `<option value="">Todas las Comunidades</option>`;
        for(let dato of datosFiltro){
            //options += `<option value="${dato}">${dato}</option>`;
            let selected = "";
            if(selectedFilter === dato){
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

    let grid = `
    ${selectFiltro}
    <div class="headerGrid">
        ${botonCrear}
        <div class="tituloGrid">${titulo}</div>
        ${paginacion}
    </div>
    <div class="grid">
        ${cards}
    </div>
    `;
    return grid;
}