//CLIENTES: id, nombre, telefono, cartera, bote, timestamp
const camposClientesAdministrador = [
    {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
    },{
        tipo: "text",
        id: "cartera",
        label: "Cartera",
    },{
        tipo: "text",
        id: "bote",
        label: "Bote",
    }
];

//Insertar Chat

const configClientesAdministrador = {
    location: "clientesAdministrador",
    titulo: "Clientes",
    campos: camposClientesAdministrador,
    clase: "Clientes",
    nombreFuncion: "Clientes",
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "nombre",
        info2: "telefono",
        info3: "",
        info4: "cartera",
        info5: "bote",
        id: "id",
    },
    mensajes: {
        eliminarTitulo: "Eliminar Cliente",
        eliminarMensaje: "¿Estas Seguro de eliminar este Cliente? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Cliente Eliminado",
        eliminarExitoMensaje: "El Cliente ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar el Cliente pues tiene elementos asociados",
    },
    datosExtra: {},
    funcionesExternas: {
        get: function() { 
            //Insertar aviso de mensaje: <whatsapp-chat-notification userPhone="34600857882" codigoAdmin="wairbotrepair1_CHAT_"></whatsapp-chat-notification>
            
            //obtener todos los elementos con la clase "cardInfo"
            let cards = document.getElementsByClassName("cardInfo");

            //obtener todos los card_info_1(nombre) y card_info_2(teléfono) de las cards y añadir el componente a continuación del nombre y un espacio en blanco
            for (let card of cards) {
                let nombre = card.getElementsByClassName("card_info_1")[0];
                let telefono = card.getElementsByClassName("card_info_2")[0];

                let aviso = `
                    <span>
                        <whatsapp-chat-notification userPhone="${telefono.innerText}" codigoAdmin="loterias37vigo_CHAT_"></whatsapp-chat-notification>
                    </span>
                `;

                nombre.innerHTML = nombre.innerHTML + "&nbsp;" + aviso;
                nombre.style.display = "flex";
                nombre.style.jutifyContent = "center";
                nombre.style.alignItems = "center";
            }

        },
        modalEliminar: function() { console.log("Función externa ejecutada después de modalEliminar");},
        eliminar: function(data) { console.log("Función externa ejecutada después de eliminar", data);},
        open: function(data) { console.log("Función externa ejecutada después de open", data);},
        crear: function() { console.log("Función externa ejecutada después de crear");},
        guardar: function(data) { console.log("Función externa ejecutada después de guardar", data);}
    },
    funcionesContenidoOpen: [
        async function(id, data) {
            console.log({data});
            //Apuestas
            let configApuestasEnCliente = configApuestasAdministrador;
            configApuestasEnCliente.location = "clientesAdministrador";
            configApuestasEnCliente.funcionLeer = "listarPorCliente";
            configApuestasEnCliente.returnHtml = true;
            configApuestasEnCliente.dataFuncionLeerHtml = {id_cliente: id};

            const paginaApuestasEnCliente = new Pagina(configApuestasEnCliente);
            return await paginaApuestasEnCliente.get();
        },
        async function(id, data) {
            //añadir un separador
            let separador = `
                <div class="separador" style="margin-top: 20px; margin-bottom: 20px; border-top: 1px solid #ccc; width: 100%;"></div>
            `;
            return separador;
        },
        async function(id, data) {
            console.log({data});
            //Premios
            let configPremiosEnCliente = configPremiosAdministrador;
            configPremiosEnCliente.location = "clientesAdministrador";
            configPremiosEnCliente.funcionLeer = "listarPorCliente";
            configPremiosEnCliente.returnHtml = true;
            configPremiosEnCliente.dataFuncionLeerHtml = {id_cliente: id};

            const paginaPremiosEnCliente = new Pagina(configPremiosEnCliente);
            return await paginaPremiosEnCliente.get();
        },
        async function(id, data) {
            //añadir un separador
            let separador = `
                <div class="separador" style="margin-top: 20px; margin-bottom: 20px; border-top: 1px solid #ccc; width: 100%;"></div>
            `;
            return separador;
        },
        async function(id, data) {
            let nombre = data.nombre;
            let telefono = data.telefono;
            let chat = `
                <div style="width: 100%; height: 500px; background-color: transparent;">
                    <whatsapp-chat-user
                        userName="${nombre}"
                        userPhone="${telefono}"
                        codigoAdministradorBot="loterias37vigo_CHAT_"
                        passwordAdministradorBot="15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225">
                    </whatsapp-chat-user>
                </div>
            `;
            return chat;
        },
        async function(id, data) {
            let botones = `
                <div style="display: flex; justify-content: center; align-items: center; margin-top: 20px; width: 100%;">
                    <button class="botonGrid" id="botonAvisoSaldo" style="border: none; font-size: 1.5rem; margin-right: 20px;">
                        Aviso Saldo
                    </button>
                    <button class="botonGrid" id="botonAvisoBote" style="border: none; font-size: 1.5rem; margin-right: 20px;">
                        Aviso Bote
                    </button>
                </div>
            `;

            return botones;
        }
    ]
};

const paginaClientesAdministrador = new Pagina(configClientesAdministrador);

async function getClientesAdministrador() {
    await paginaClientesAdministrador.get();
}

//al hacer input en el cartera o bote, solo permite escribir números y un único punto
document.addEventListener("input", function(e) {
    if (e.target.id === "cartera" || e.target.id === "bote") {
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

document.addEventListener("click", function(e) {
    if (e.target.id === "botonAvisoSaldo") {
        let whatsappChatUser = document.querySelector("whatsapp-chat-user");
        let textarea = whatsappChatUser.shadowRoot.getElementById("autoResizeTextarea");

        let cartera = document.getElementById("cartera");

        let mensajeSaldo = `Se ha actualizado tu saldo: ${cartera.value} €\n\nPuedes consultar tu información accediendo a este enlace y pulsando enviar:\nhttps://wa.me/34886080849?text=loterias37vigo%231%2C1`;
        textarea.value = mensajeSaldo;
    }

    if (e.target.id === "botonAvisoBote") {
        let whatsappChatUser = document.querySelector("whatsapp-chat-user");
        let textarea = whatsappChatUser.shadowRoot.getElementById("autoResizeTextarea");

        let bote = document.getElementById("bote");

        let baseUrl = "https://wa.me/34886080849?text=";
        let message = "loterias37vigo#1,1";
        let encodedMessage = encodeURIComponent(message);
        let formattedUrl = `${baseUrl}${encodedMessage}`;

        let mensajeBote = `Se ha actualizado el bote: ${bote.value} €\n\nPuedes consultar tu información accediendo a este enlace y pulsando enviar:\nhttps://wa.me/34886080849?text=loterias37vigo%231%2C1`;
        textarea.value = mensajeBote;
    }
});