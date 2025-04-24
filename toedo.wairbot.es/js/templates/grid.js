function createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder) {
    let paginacion = "";
    if(funcionAvanzar){
        paginacion = `
        <div class="paginacion">
            <div class="paginaAnterior" onclick="${funcionRetroceder}()">&lt;&lt;</div>
            <div class="paginaActual">
                <span id="paginaActual">${paginaActual}</span>/<span id="totalPaginas">${totalPaginas}</span>
            </div>
            <div class="paginaSiguiente" onclick="${funcionAvanzar}()">&gt;&gt;</div>
        </div>
        `;
    }

    console.log(funcionCrear);

    let botonCrear = "";
    if(funcionCrear){
        botonCrear = `<div class="botonGrid" onclick="${funcionCrear}()">+</div>`;
    }

    let grid = `
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