//PREMIOS: id, id_cliente, id_apuesta, juego, importe, timestamp
const camposPremiosAdministrador = [
    {
        tipo: "dataListApi",
        id: "id_cliente",
        label: "Cliente",
        options: "Clientes::listarNombreMasId",
        defaultValue: "",
    },{
        tipo: "dataListApi",
        id: "id_apuesta",
        label: "Apuesta",
        options: "Apuestas::listarFechaJuegoMasId",
        defaultValue: "",
    },{
        tipo: "dataListCompleto",
        id: "juego",
        label: "Juego",
        options: "juegos::apuestas",
    },{
        tipo: "text",
        id: "importe",
        label: "Importe",
    }
];

//ficha de cliente
//Al change en el id_apuesta, se actualiza el juego
//Al hacer input en el importe, solo permite escribir números y un único punto
//Implementar chat
//botón para enviar un mensaje al cliente para avisar del premio

const configPremiosAdministrador = {
    location: "premiosAdministrador",
    titulo: "Premios",
    campos: camposPremiosAdministrador,
    clase: "Premios",
    nombreFuncion: "Premios",
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "id_cliente_nombre",
        info2: "juego",
        info3: "",
        info4: "id_apuesta_fecha",
        info5: "importe",
        id: "id",
    },
    mensajes: {
        eliminarTitulo: "Eliminar Premio",
        eliminarMensaje: "¿Estas Seguro de eliminar este Premio? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Premio Eliminado",
        eliminarExitoMensaje: "El Premio ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar el Premio pues tiene elementos asociados",
    },
    datosExtra: {},
    funcionesExternas: {
        get: function() { console.log("Función externa ejecutada después de get");},
        modalEliminar: function() { console.log("Función externa ejecutada después de modalEliminar");},
        eliminar: function(data) { console.log("Función externa ejecutada después de eliminar");},
        open: function(data) { console.log("Función externa ejecutada después de open");},
        crear: function() { console.log("Función externa ejecutada después de crear");},
        guardar: function(data) {
            let modals = document.getElementsByClassName("modalTitulo");
            console.log("Modals", modals);

            // Convertir HTMLCollection a Array
            let modalsArray = Array.from(modals);

            for (let modal of modalsArray) {
                if (modal.innerText == "Editar Clientes") {
                    //obtener el id: "Bote" dentro del modal
                    let Bote = document.getElementById("bote");
                    let importe = document.getElementById("importe");

                    console.log("Importe", importe.value);
                    console.log("Bote", Bote.value);
                    
                    //float con 2 decimales
                    let importeValue = parseFloat(importe.value).toFixed(2);
                    let BoteValue = parseFloat(Bote.value).toFixed(2);

                    //actualizar el valor de la Bote
                    Bote.value = (parseFloat(BoteValue) + parseFloat(importeValue)).toFixed(2);

                    console.log("Bote", Bote.value);
                }
            }
        }
    },
    funcionesContenidoOpen: [
        async function(id, data) {
        }
    ]
};

const paginaPremiosAdministrador = new Pagina(configPremiosAdministrador);

async function getPremiosAdministrador() {
    await paginaPremiosAdministrador.get();
}

document.addEventListener("input", function(e) {
    if (e.target.id === "importe") {
        // Reemplazar comas por puntos
        let value = e.target.value.replace(/,/g, ".");
        
        // Asegurarse de que no haya más de un punto
        let parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limitar a dos cifras después del punto
        if (parts.length === 2) {
            parts[1] = parts[1].substring(0, 2);
            value = parts.join('.');
        }
        
        // Eliminar cualquier carácter que no sea un número o un punto
        e.target.value = value.replace(/[^0-9.]/g, "");
    }
});