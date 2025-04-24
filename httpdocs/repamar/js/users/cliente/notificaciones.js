//getByClienteId
async function getNotificacionesCliente() {
    //obtener los datos de los agentes
    let clase = 'Notificaciones';
    let funcion = 'getByClienteId';
    let datos = {
    };

    let data = await queryAsync(clase, funcion, datos);

    console.log(data);

    let paginaActual = null;
    let totalPaginas = null;

    data = data.data;

    //OBTENER CARDS CON LOS DATOS
    let cards = "";
    for (let i = 0; i < data.length; i++) {
        let card = `
        <div class="card">
            <div class="card-body" style="padding: 0.5rem;">
                <div class="card-text">${data[i].texto}</div>
            </div>
        </div>
        `;

        cards += card;
    }

    let titulo = "Notificaciones";
    let funcionCrear = null;
    let funcionAvanzar = null;
    let funcionRetroceder = null;
    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);
    let administrador = document.getElementById("cliente");
    administrador.innerHTML = grid;
}