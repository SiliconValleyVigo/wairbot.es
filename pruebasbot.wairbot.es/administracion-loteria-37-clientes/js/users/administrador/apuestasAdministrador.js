//APUESTAS: id, id_cliente, juego, imagen, fecha, timestamp
const camposApuestasAdministrador = [
    {
        tipo: "dataListApi",
        id: "id_cliente",
        label: "Cliente",
        options: "Clientes::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "dataListCompleto",
        id: "juego",
        label: "Juego",
        options: "juegos::apuestas",
    },{
        tipo: "file",
        id: "imagen",
        label: "Imagen",
    },{
        tipo: "date",
        id: "fecha",
        label: "Fecha compra",
    },{
        tipo: "coin",
        id: "importe",
        label: "Importe",
    },{
        tipo: "dataListCompleto",
        id: "plazos",
        label: "Plazos de las Apuestas",
        options: "plazos::apuestas",
    }
];

//Ficha de cliente
//Insertar Chat
//Botón para enviar un mensaje al cliente para confirmar la apuesta

const configApuestasAdministrador = {
    location: "apuestasAdministrador",
    titulo: "Apuestas",
    campos: camposApuestasAdministrador,
    clase: "Apuestas",
    nombreFuncion: "Apuestas",
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "id_cliente_nombre",
        info2: "juego",
        info3: "",
        info4: "",
        info5: "fecha",
        id: "id",
    },
    mensajes: {
        eliminarTitulo: "Eliminar Apuesta",
        eliminarMensaje: "¿Estas Seguro de eliminar esta Apuesta? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Apuesta Eliminada",
        eliminarExitoMensaje: "La Apuesta ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar la Apuesta pues tiene elementos asociados",
    },
    datosExtra: {},
    funcionesExternas: {
        get: function() { console.log("Función externa ejecutada después de get");},
        modalEliminar: function() { console.log("Función externa ejecutada después de modalEliminar");},
        eliminar: function(data) { console.log("Función externa ejecutada después de eliminar");},
        open: function(data) { console.log("Función externa ejecutada después de open");},
        crear: function() {console.log("Función externa ejecutada después de crear");},
        guardar: function(data) { 
            let modals = document.getElementsByClassName("modalTitulo");
            console.log("Modals", modals);

            // Convertir HTMLCollection a Array
            let modalsArray = Array.from(modals);

            for (let modal of modalsArray) {
                if (modal.innerText == "Editar Clientes") {
                    //obtener el id: "cartera" dentro del modal
                    let cartera = document.getElementById("cartera");
                    let importe = document.getElementById("importe");

                    console.log("Importe", importe.value);
                    console.log("cartera", cartera.value);
                    
                    //float con 2 decimales
                    let importeValue = parseFloat(importe.value).toFixed(2);
                    let carteraValue = parseFloat(cartera.value).toFixed(2);

                    //actualizar el valor de la cartera
                    cartera.value = (parseFloat(carteraValue) - parseFloat(importeValue)).toFixed(2);

                    console.log("cartera", cartera.value);
                }
            }
        }
    },
    funcionesContenidoOpen: [
        async function(id, data) {
        }
    ]
};

const paginaApuestasAdministrador = new Pagina(configApuestasAdministrador);

async function getApuestasAdministrador() {
    await paginaApuestasAdministrador.get();
}