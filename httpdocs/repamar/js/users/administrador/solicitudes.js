async function getSolicitudes() {
    localStorage.setItem("location", "solicitudes");
    let data = localStorage.getItem('solicitudes');
    data = JSON.parse(data);
    data = data.data;
    console.log(data);

    //obtener las cards con los datos
    let cards = cardsSolicitudes(data);

    let titulo = "Solicitudes";
    let funcionCrear = "crearSolicitud";
    let funcionAvanzar = "avanzarSolicitudes";
    let funcionRetroceder = "retrocedSolicitudes";

    let grid = createGrid(cards, 1, 1, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("administrador");
    administrador.innerHTML = grid;

    //eliminar la el objeto con la clase paginacion
    let paginacion = document.querySelector(".paginacion");
    if(paginacion){
        paginacion.remove();
    }
}

function cardsSolicitudes(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openSolicitud";

        let agente = card.agente;
        let motor = card.motor;

        let imagen = agente.imagen;

        let info1 = "Agente: " + agente.nombre + " |" + agente.referencia;
        let info2 = agente.telefono_contacto + " | " + agente.email;
        let info3 = "<strong>Solicita:</strong>"
        let info4 = motor.marca + " " + motor.modelo + " | " + motor.codigo;
        let info5 = `
            <button onclick="asignarSolicitud(${agente.id}, ${motor.id})">Asignar</button>
        `;
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

async function asignarSolicitud(idAgente, idMotor) {
    let clase = 'Motores';
    let funcion = 'update';
    let id = idMotor;
    let datos = {
        id : id,
        data : {
            id_agente : idAgente
        }
    };

    await queryAsync(clase, funcion, datos);
    
    getSolicitudes();

    openVendido(id);
}

function openSolicitud(id) {
    let idOriginal = id;
}

