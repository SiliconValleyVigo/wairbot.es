//<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

/*
EJEMPLOS DE USO:

<div style="width: 50%; height: 500px; background-color: red;">
        <whatsapp-chat-user userName="Usuario" userPhone="34600857882" codigoAdministradorBot="wairbotrepair1_CHAT_"
        passwordAdministradorBot="15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225">
    </whatsapp-chat-user>
</div>

<whatsapp-chat-notification userPhone="34600857882" codigoAdmin="wairbotrepair1_CHAT_"></whatsapp-chat-notification>
<whatsapp-chat-notification userPhone="111" codigoAdmin="wairbotrepair1_CHAT_"></whatsapp-chat-notification>
<whatsapp-chat-notification userPhone="222" codigoAdmin="wairbotrepair1_CHAT_"></whatsapp-chat-notification>
<whatsapp-chat-notification userPhone="" codigoAdmin="wairbotrepair1_CHAT_"></whatsapp-chat-notification>

<script src="./chat-whatsapp/chat-whatsapp.js"></script>
*/

class SocketSingleton {
    constructor() {
        if (!SocketSingleton.instance) {
            this.socket = this.initializeSocket();
            SocketSingleton.instance = this;
        }
        return SocketSingleton.instance;
    }

    initializeSocket() {
        // Aquí inicializas tu socket, por ejemplo con socket.io
        const socket = io("https://wairbothook.wairbot.es/socket.io");

        // Manejar eventos de conexión y errores
        socket.on("connect", () => {
            console.log("Socket conectado:", socket.id);
        });

        socket.on("connect_error", (error) => {
            console.error("Error de conexión del socket:", error);
        });

        socket.on("disconnect", (reason) => {
            console.log("Socket desconectado. Razón:", reason);
        });

        return socket;
    }

    static getInstance() {
        if (!SocketSingleton.instance) {
            SocketSingleton.instance = new SocketSingleton();
        }
        return SocketSingleton.instance;
    }

    getSocket() {
        return this.socket;
    }
}

class WhatsappChatUser extends HTMLElement {
    static get observedAttributes() {
        return ["userName", "userPhone", "codigoAdministradorBot", "passwordAdministradorBot"];
    }

    constructor() {
        super();
        this.shadow = this.attachShadow({ mode: "open" });
        this.render();
        this.initSocket();
        this.getConversation();
        this.socket = null;
    }

    render() {
        this.shadow.innerHTML = `
        <style>
            .chatWhatsappContainer {
                width: calc(100% - 20px);
                height: calc(100% - 20px);
                margin: 0 auto;
                padding: 10px;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 0.9rem;
                display: flex;
                flex-direction: column;
                color: #111b21;
            }

            .chatWhatsappContainer h2 {
                text-align: left;
                margin: 0;
                padding: 8px;
                background-color: #25d366;
                color: white;
                border-radius: 10px 10px 0 0;
                font-size: medium;
            }

            .chatWhatsappContainer .conversation {
                width: calc(100% - 20px);
                height: calc(100% - 20px);
                background-color: #fdf3e6;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 10px;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
                scrollbar-width: thin;
                scrollbar-color: #c6c4bf transparent;
            }

            .chatWhatsappContainer .conversacionPage {
                display: flex;
                flex-direction: column;
            }

            .chatWhatsappContainer .mensajesAnteriores {
                width: 100%;
                padding: 10px;
                color: #25d366;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                background-color: transparent;
            }

            .chatWhatsappContainer .mensajesAnteriores:hover {
                background-color: white;
                color: #111b21;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
            }

            .chatWhatsappContainer .message {
                display: flex;
                flex-direction: column;
            }

            .chatWhatsappContainer .message div {
                padding: 10px;
            }

            .chatWhatsappContainer .adminMensaje {
                background-color: #d9fdd3;
                box-shadow: 0 1px .5px rgba(11, 20, 26, 0.3);
                align-self: flex-end;
                border-radius: 10px 10px 0 10px;
                word-break: break-word;
                max-width: 80%;
            }

            .chatWhatsappContainer .userMensaje {
                background-color: white;
                box-shadow: 0 1px .5px rgba(11, 20, 26, 0.3);
                align-self: flex-start;
                border-radius: 10px 10px 10px 0;
                word-break: break-word;
                max-width: 80%;
            }

            .chatWhatsappContainer .hora_adminMensaje {
                font-size: 0.7rem;
                color: gray;
                align-self: flex-end;
                padding: 5px 10px 10px 10px !important;
            }

            .chatWhatsappContainer .hora_userMensaje {
                font-size: 0.7rem;
                color: gray;
                align-self: flex-start;
                padding: 5px 10px 10px 10px !important;
            }

            .chatWhatsappContainer .horaMensaje {
                font-size: 0.7rem;
                color: gray;
                align-self: flex-start;
                padding: 5px 10px 10px 10px !important;
            }

            .chatWhatsappContainer .mensajeFilePreview {
                display: flex;
                flex-direction: column;
            }

            .chatWhatsappContainer .mensajeFilePreview img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border-radius: 10px;
            }

            .chatWhatsappContainer .adminMensajeFile{
                background-color: #d9fdd3;
                box-shadow: 0 1px .5px rgba(11, 20, 26, 0.3);
                align-self: flex-end;
                border-radius: 10px 10px 0 10px;
                margin-bottom: 5px;
                word-break: break-word;
            }

            .chatWhatsappContainer .userMensajeFile{
                background-color: white;
                box-shadow: 0 1px .5px rgba(11, 20, 26, 0.3);
                align-self: flex-start;
                border-radius: 10px 10px 10px 0;
                margin-bottom: 5px;
                word-break: break-word;
            }

            .chatWhatsappContainer .input {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                background-color: #f1f1f1;
                border-radius: 0 0 10px 10px;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
            }

            .chatWhatsappContainer .input button {
                background-color: transparent;
                border: none;
                cursor: pointer;
            }

            .chatWhatsappContainer .input button svg {
                fill: #000000;
            }

            .chatWhatsappContainer .input textarea {
                width: calc(100% - 60px);
                padding: 10px;
                border: none;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
                resize: none;
                background-color: white;
                height: 30px;
                max-height: 200px;
                overflow-y: auto;
            }

            .chatWhatsappContainer #botonEstadisticas {
                width: 100%;
                text-align: right;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                background-color: transparent;
                height: 0;
                z-index: 1;
                transform: translateY(10px);
            }

            .chatWhatsappContainer .estadisticasContainer {
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: white;
                max-height: 300px;
            }

            .chatWhatsappContainer .statisticsWhatsappContainer {
                width: calc(100% - 20px);
                padding: 10px;
                background-color: white;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
                margin-top: 10px;
                max-height: 300px;
                overflow-y: auto;

                /* barra de desplazamiento */
                scrollbar-width: thin;
                scrollbar-color: #c6c4bf transparent;
            }

            .chatWhatsappContainer .estadistica {
                display: flex;
                flex-direction: column;
                margin-bottom: 10px;
            }

            .chatWhatsappContainer .estadisticaMes {
                font-weight: bold;
                font-size: 1.1rem;
                color: #25d366;
            }

            .chatWhatsappContainer .estadisticaInfo {
                display: flex;
                flex-direction: column;
                background-color: #f1f1f1;
                padding: 10px;
                border-radius: 10px;
            }

            .chatWhatsappContainer .estadisticaItem {
                display: flex;
                flex-direction: column;
                margin-bottom: 10px;
            }

            .chatWhatsappContainer .estadisticaTitulo {
                font-weight: bold;
            }

            .chatWhatsappContainer .estadisticaValor {
                font-size: 0.9rem;
            }

            .chatWhatsappContainer .statisticsWhatsappContainer button {
                width: 100%;
                padding: 10px;
                color: #25d366;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                background-color: transparent;
            }

            .chatWhatsappContainer .statisticsWhatsappContainer button:hover {
                background-color: white;
                color: #111b21;
                box-shadow: 0 1px .5px rgba(11, 20, 26, .13);
            }

            .chatWhatsappContainer .preview {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .chatWhatsappContainer .preview img {
                width: 100px;
                height: 100px;
                object-fit: cover;
                border-radius: 10px;
            }
        </style>
        
        <div class="chatWhatsappContainer">
            <button onclick="this.getRootNode().host.getStatistics()" id="botonEstadisticas">📊</button>
            <h2>${this.getAttribute("userName")}</h2>
            <div class="estadisticasContainer"></div>
            <div class="preview" id="preview" onclick="this.getRootNode().host.limpiarPreview()"></div>
            <div class="conversation"></div>
            <div class="input">
                
                <button onclick="this.getRootNode().host.openFileDialog()">
                    <svg fill="#000000" height="20px" width="20px" version="1.1" id="Layer_1"
                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                	viewBox="0 0 512 512" xml:space="preserve">
                    <g>
                	    <g>
                		    <path d="M359.784,103.784v262.919c0,57.226-46.557,103.784-103.784,103.784s-103.784-46.557-103.784-103.784V103.784
                			c0-34.336,27.934-62.27,62.27-62.27c34.336,0,62.27,27.934,62.27,62.27v262.919c0,11.445-9.312,20.757-20.757,20.757
                			s-20.757-9.311-20.757-20.757V103.784H193.73v262.919c0,34.336,27.934,62.27,62.27,62.27s62.27-27.934,62.27-62.27V103.784
                			C318.27,46.557,271.713,0,214.487,0S110.703,46.557,110.703,103.784v262.919C110.703,446.82,175.883,512,256,512
                			s145.297-65.18,145.297-145.297V103.784H359.784z"/>
                	    </g>
                    </g>
                </svg>
                </button>

                <textarea
                id="autoResizeTextarea"
                placeholder="Escribe un mensaje..."
                oninput="this.getRootNode().host.autoResize(this)"
                onkeydown="this.getRootNode().host.enterKeyAndCtrlEnterKeys(event)"
                ></textarea>

                <button onclick="this.getRootNode().host.sendMessage()">
                <svg viewBox="0 0 20 20" height="20" width="20" preserveAspectRatio="xMidYMid meet" class="" version="1.1" x="0px" y="0px" enable-background="new 0 0 24 24"><title>send</title><path fill="currentColor" d="M1.101,21.757L23.8,12.028L1.101,2.3l0.011,7.912l13.623,1.816L1.112,13.845 L1.101,21.757z"></path></svg>    
                </button>

                <input type="file" id="fileInput" style="display: none;" onchange="this.getRootNode().host.handleFileSelect(event)">
            </div>
        </div>
    `;
    }

    enterKeyAndCtrlEnterKeys(event) {
        const textarea = event.target;
        if (event.key === "Enter" && event.ctrlKey) {
            console.log("Ctrl + Enter");
            event.preventDefault();
            textarea.value += "\n";
        } else if (event.key === "Enter") {
            event.preventDefault();
            this.sendMessage();
            textarea.style.height = "30px";
        }
    }

    limpiarPreview() {
        let preview = this.shadow.querySelector("#preview");
        preview.innerHTML = "";
    }

    autoResize(textarea) {
        textarea.style.height = "auto"; // Reinicia la altura para recalcultar
        let newHeight = textarea.scrollHeight;

        // Obtener el valor de max-height desde los estilos computados
        let computedStyle = window.getComputedStyle(textarea);
        let maxHeight = parseInt(computedStyle.maxHeight, 10);

        if (newHeight > maxHeight) {
            textarea.style.height = maxHeight + "px";
        } else {
            newHeight = newHeight - 20;
            textarea.style.height = newHeight + "px";
        }
    }

    initSocket() {
        const socketSingleton = SocketSingleton.getInstance();
        this.socket = socketSingleton.getSocket();
        console.log(this.socket);
        const codigoAdmin = this.getAttribute("codigoAdministradorBot");

        if (!this.socket) {
            console.error("Socket no está inicializado correctamente.");
            return;
        }

        this.socket.on("connect", () => {
            console.log("Conectado al servidor de Socket.IO");

            // Unirse a la sala específica
            /*this.socket.emit("joinRoom", codigoAdmin);
            console.log(`Unido a la sala: ${codigoAdmin}`);*/
        });

        this.socket.emit("joinRoom", codigoAdmin);

        this.socket.on("connect_error", (error) => {
            console.error("Error de conexión:", error);
        });

        this.socket.on("connect_timeout", () => {
            console.error("Tiempo de conexión agotado");
        });

        this.socket.on("error", (error) => {
            console.error("Error general:", error);
        });

        this.socket.on("reconnect_attempt", (attemptNumber) => {
            console.log(`Intento de reconexión número: ${attemptNumber}`);
        });

        this.socket.on("mensaje", (data) => {
            console.log("Mensaje recibido:", data);
            this.updateConversation(data);
        });

        this.socket.on("disconnect", (reason) => {
            console.log("Desconectado del servidor de Socket.IO. Razón:", reason);
        });
    }

    someFunction() {
        if (!this.socket) {
            console.error("Socket no está inicializado correctamente.");
            return;
        }

        // Asegurarse de que el objeto no sea null antes de llamar a emit
        this.socket.emit("someEvent", someData);
    }

    updateConversation(data) {
        // Verifica que el data tiene la estructura correcta
        if (!data || !data.messageBody) {
            console.error("Datos recibidos no válidos:", data);
            return;
        }

        //si data.userPhone no es igual a userPhone no hacer nada
        if (data.userPhone !== this.getAttribute("userPhone")) {
            return;
        }

        let conversation = this.shadow.querySelector(".conversation");

        //espera a que la conversación esté disponible
        if (!conversation) {
            let interval = setInterval(() => {
                conversation = this.shadow.querySelector(".conversation");
                if (conversation) {
                    clearInterval(interval);
                    this.updateConversation(data);
                }
            }, 1000);
            return;
        }

        const message = document.createElement("div");
        //message.classList.add('message');

        let url = this.mostrarUrl(data.messageBody, "user");

        //si url contiene html añadirlo al mensaje
        if (url.html !== "") {
            message.innerHTML = `
                        <div class="message">
                            ${url.html}
                            <div class="userMensaje">${url.mensaje}</div>
                            <div class="hora_userMensaje">${new Date().toLocaleString()}</div>
                        </div>
                    `;
            conversation.appendChild(message);
        } else {
            message.innerHTML = `
                        <div class="message">
                            <div class="userMensaje">${data.messageBody}</div>
                            <div class="hora_userMensaje">${new Date().toLocaleString()}</div>
                        </div>
                    `;
            conversation.appendChild(message);
        }

        conversation.appendChild(message);

        conversation.scrollTop = conversation.scrollHeight;

        // Desplázate hacia abajo para ver el último mensaje
        window.scrollTo(0, document.body.scrollHeight);

        ////comprobar si en el dom existe el web component whatsapp-chat-user y si existe actuliazar leidos
        let whatsappChatUser = document.querySelector("whatsapp-chat-user");
        if (whatsappChatUser) {
            fetch(`https://wairbothook.wairbot.es/leido?userPhone=${data.userPhone}&codigoAdmin=${data.codigoAdmin}`);
        }
    }

    //enviar mensaje a través de la API
    async sendMessage() {
        //autoResizeTextarea
        const input = this.shadow.getElementById("autoResizeTextarea");
        let message = input.value;

        let file = "";

        //comprobar si existe preview
        const preview = await this.shadow.querySelector("#previewURL");
        if (preview !== null) {
            file = await preview.innerHTML;

            //si message está vacío el mensaje es file y si no el mensaje es message
            if (message.trim() === "") {
                message = file;
            } else {
                message = message + " " + file;
            }
        }

        //si message está vacío, y file también, no hacer nada
        if (message.trim() === "" && file === "") {
            console.log("No se puede enviar un mensaje vacío");
            return;
        }

        input.value = "";

        let url = this.mostrarUrl(message);

        const conversation = this.shadow.querySelector(".conversation");
        const messageElement = document.createElement("div");
        messageElement.classList.add("message");

        //si url contiene html añadirlo al mensaje
        if (url.html !== "") {
            messageElement.innerHTML = `
                        <div class="message">
                            ${url.html}
                            <div class="adminMensaje">${url.mensaje}</div>
                            <div class="hora_adminMensaje">${new Date().toLocaleString()}</div>
                        </div>
                    `;
            conversation.appendChild(messageElement);
        } else {
            messageElement.innerHTML = `
                        <div class="message">
                            <div class="adminMensaje">${message}</div>
                            <div class="hora_adminMensaje">${new Date().toLocaleString()}</div>
                        </div>
                    `;
            conversation.appendChild(messageElement);
        }

        conversation.scrollTop = conversation.scrollHeight;

        const userPhone = this.getAttribute("userPhone");
        const codigoAdministradorBot = this.getAttribute("codigoAdministradorBot");
        const passwordAdministradorBot = this.getAttribute("passwordAdministradorBot");

        //limpiar preview
        let previewBox = this.shadow.querySelector("#preview");
        previewBox.innerHTML = "";

        try {
            const response = await fetch("https://wairbothook.wairbot.es/send", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    userPhone,
                    message,
                    file,
                    codigoAdministradorBot,
                    passwordAdministradorBot,
                }),
            });

            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.statusText}`);
            }

            const data = await response.json();

            ////comprobar si en el dom existe el web component whatsapp-chat-user y si existe actuliazar leidos
            let whatsappChatUser = document.querySelector("whatsapp-chat-user");
            if (whatsappChatUser) {
                const responseLeido = await fetch(`https://wairbothook.wairbot.es/leido?userPhone=${userPhone}&codigoAdmin=${codigoAdministradorBot}`);
                if (!responseLeido.ok) {
                    throw new Error(`Error en la solicitud: ${responseLeido.statusText}`);
                }
            }
        } catch (error) {
            console.error("Error al enviar el mensaje:", error);
        }
    }

    //obtener la conversación a través de la API
    async getConversation(page = 1) {
        const userPhone = this.getAttribute("userPhone");
        const codigoAdmin = this.getAttribute("codigoAdministradorBot");

        try {
            const response = await fetch(`https://wairbothook.wairbot.es/conversacion?userPhone=${userPhone}&codigoAdmin=${codigoAdmin}&page=${page}`);

            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.statusText}`);
            }

            let data = await response.json();

            let currentPage = data.currentPage;
            let totalPages = data.totalPages;
            data = data.messages;

            //invertir el orden de los mensajes
            data.reverse();

            const conversation = this.shadow.querySelector(".conversation");

            //conversacion pagina 1
            let pagina1 = `<div class='conversacionPage conversacionPage_${currentPage}'>
                        <button onclick='this.getRootNode().host.getConversationPage()' class="mensajesAnteriores">Mensajes Anteriores</button>
                    `;

            for (let mensaje of data) {
                let className = "adminMensaje";
                if (mensaje.emitido_por === "user") {
                    className = "userMensaje";
                }

                //formatear timestamp
                let timestamp = new Date(mensaje.timestamp);
                mensaje.timestamp = timestamp.toLocaleString();
                mensaje.timestamp = mensaje.timestamp.slice(0, -3);
                mensaje.timestamp = mensaje.timestamp.replace(",", " -");

                //mostrar la url del mensaje si la contiene
                let url = this.mostrarUrl(mensaje.mensaje, mensaje.emitido_por);

                //si url contiene html añadirlo al mensaje
                if (url.html !== "") {
                    pagina1 += `
                            <div class="message">
                                ${url.html}
                                <div class="${className}">${url.mensaje}</div>
                                <div class="hora_${className}">${mensaje.timestamp}</div>
                            </div>
                        `;
                } else {
                    pagina1 += `
                            <div class="message">
                                <div class="${className}">${mensaje.mensaje}</div>
                                <div class="hora_${className}">${mensaje.timestamp}</div>
                            </div>
                        `;
                }
            }
            pagina1 += "</div>";

            //añadir al principio de la conversación
            conversation.innerHTML = pagina1 + conversation.innerHTML;

            //delay de 1 segundo para que se cargue la conversación
            let delay = 1000;
            setTimeout(() => {
                //ir al final de la conversación
                conversation.scrollTop = conversation.scrollHeight;
            }, delay);

            //ir al final de la conversación
            conversation.scrollTop = conversation.scrollHeight;

            let whatsappChatUser = document.querySelector("whatsapp-chat-user");
            if (whatsappChatUser) {
                //marcar como leídos los mensajes -GET /leido?userPhone=886080860&codigoAdmin=886080853
                const responseLeido = await fetch(`https://wairbothook.wairbot.es/leido?userPhone=${userPhone}&codigoAdmin=${codigoAdmin}`);
                if (!responseLeido.ok) {
                    throw new Error(`Error en la solicitud: ${responseLeido.statusText}`);
                } else {
                    console.log("Mensajes marcados como leídos");
                }
            }

        } catch (error) {
            console.error("Error al obtener la conversación:", error);
        }
    }

    //obtener la página a la anterior
    async getConversationPage() {
        //obtener las páginas por la clase conversacionPage
        let conversacionPages = this.shadow.querySelectorAll(".conversacionPage");

        //obtener los nombres de las clases de conversacionPages que contengan conversacionPage_
        let clases = [];
        for (let conversacionPage of conversacionPages) {
            let clase = conversacionPage.className;
            if (clase.includes("conversacionPage_")) {
                clases.push(clase);
            }
        }

        //obtener un array de números de las clases
        let numeros = [];
        for (let clase of clases) {
            let numero = clase.split("_")[1];
            numero = parseInt(numero);
            numeros.push(numero);
        }

        //obtener el mayor número
        let mayor = Math.max(...numeros);

        //obtener la página siguiente
        let paginaAnterior = mayor + 1;

        //obtener la conversación de la página siguiente
        this.getConversation(paginaAnterior);
    }

    openFileDialog() {
        this.shadow.querySelector("#fileInput").click();
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.previewImage(file);
            this.uploadImage(file);
        }
    }

    previewImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = this.shadow.querySelector("#preview");
            const fileType = file.type;

            if (fileType.startsWith("image/")) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
            } else {
                const fileName = file.name;
                const fileExtension = fileName.split(".").pop();
                preview.innerHTML = `${fileName}`;
            }
        };
        reader.readAsDataURL(file);
    }

    uploadImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const base64String = e.target.result.split(",")[1];
            const data = {
                carpeta: this.getAttribute("codigoAdministradorBot"),
                extension: file.name.split(".").pop(),
                nombreArchivo: file.name,
                archivo: base64String,
                key: "PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU",
            };

            fetch("https://pruebasbot.wairbot.es/base64-a-url/", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            })
                .then((response) => response.json())
                .then((result) => {
                    if (result.success) {
                        //guardar la URL del archivo en la conversación dentro de un div oculto con display: none dendro del div con id preview
                        const preview = this.shadow.querySelector("#preview");
                        const url = document.createElement("div");
                        url.id = "previewURL";
                        url.style.display = "none";
                        url.innerHTML = result.path;
                        preview.appendChild(url);
                    } else {
                        alert("Error al guardar el archivo: " + result.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        };
        reader.readAsDataURL(file);
    }

    mostrarUrl(mensaje, emitidoPor) {
        //comprobar si mensaje contiene una url y xi es así obtener la primera url
        let url = mensaje.match(/(https?:\/\/[^\s]+)/g);
        if (url !== null) {
            url = url[0];
        } else {
            return { mensaje, html: "" };
        }

        //si la url contiene uno de estos formatos jpg,png,gif,txt,pdf,docx,doc,xlsx,xls,pptx,ppt,zip,rar,7z si es jpg o png obtener un html con un div y una imagen, si es otro formato obtener un html con un div y un enlace para descargar, si no es ninguno de estos formatos mostrar devolver un string vacío
        let imageExtensions = ["jpg", "png", "gif"];
        let documentExtensions = ["txt", "pdf", "docx", "doc", "xlsx", "xls", "csv", "pptx", "ppt", "zip", "rar", "7z"];
        let extension = url.split(".").pop();
        let html = "";
        let clase = emitidoPor === "user" ? "userMensajeFile" : "adminMensajeFile";
        if (imageExtensions.includes(extension)) {
            html = `<div class="mensajeFilePreview ${clase}">
                                <a href="${url}" target="_blank">
                                    <img src="${url}" alt="Vista previa">
                                </a>
                            </div>
                            `;
        } else if (documentExtensions.includes(extension)) {
            //obtener el nombre del documento
            let nombre = url.split("/").pop();

            //comunidadescreadas_66c44aa20ea655.53806696.csv

            //eliminar el código unico del nombre del documento pero mantener la extensión
            let extension = nombre.split(".").pop();

            //eliminar el código unico del nombre del documento
            nombre = nombre.split("_")[0] + "." + extension;

            html = `<div class="mensajeFilePreview ${clase}">
                                <a href="${url}" target="_blank">
                                    ${nombre}
                                </a>
                            </div>
                            `;
        }

        //eliminar la url del mensaje
        mensaje = mensaje.replace(url, "");

        return { mensaje, html };
    }

    //obtener estadísticas a través de la API
    async getStatistics() {
        const codigoAdmin = this.getAttribute("codigoAdministradorBot");

        //si las estadísticas ya están desplegadas, cerrarlas
        let statistics = this.shadow.querySelector(".statisticsWhatsappContainer");
        if (statistics) {
            statistics.remove();
            return;
        }

        try {
            const response = await fetch(`https://wairbothook.wairbot.es/estadisticas?codigoAdmin=${codigoAdmin}`);

            if (!response.ok) {
                throw new Error(`Error en la solicitud: ${response.statusText}`);
            }

            const data = await response.json();

            //desplegar las estadisticas debajo del botón de estadísticas
            let statistics = this.shadow.querySelector(".statisticsWhatsappContainer");
            if (!statistics) {
                statistics = document.createElement("div");
                statistics.classList.add("statisticsWhatsappContainer");

                //insertar las estadísticas en el div estadisticasContainer
                let estadisticasContainer = this.shadow.querySelector(".estadisticasContainer");
                estadisticasContainer.appendChild(statistics);
            }

            statistics.innerHTML = "";

            //obtener el primer mes y mostrarlo, los demás meses se mostrarán al pulsar un botón de ver más
            let item = data[0];
            const div = document.createElement("div");

            let mesesArray = {
                "01": "Enero",
                "02": "Febrero",
                "03": "Marzo",
                "04": "Abril",
                "05": "Mayo",
                "06": "Junio",
                "07": "Julio",
                "08": "Agosto",
                "09": "Septiembre",
                10: "Octubre",
                11: "Noviembre",
                12: "Diciembre",
            };

            let year = item.mes.split("-")[0];
            let month = item.mes.split("-")[1];
            month = mesesArray[month];

            let mensajeIniciadoPorAdmin = item.mensajes_iniciales_admin;
            let mensajeIniciadoPorUsuario = item.mensajes_iniciales_usuario;
            let mensajeDeRespuesta = item.mensajes_respuesta_admin;

            div.innerHTML = `
                <div class="estadistica">
                    <div class="estadisticaMes">${month} ${year}</div>
                    <div class="estadisticaInfo">
                        <div class="estadisticaItem">
                            <div class="estadisticaTitulo">Mensajes iniciados por el administrador:</div>
                            <div class="estadisticaValor">${mensajeIniciadoPorAdmin}</div>
                        </div>
                        <div class="estadisticaItem">
                            <div class="estadisticaTitulo">Mensajes iniciados por el usuario:</div>
                            <div class="estadisticaValor">${mensajeIniciadoPorUsuario}</div>
                        </div>
                        <div class="estadisticaItem">
                            <div class="estadisticaTitulo">Mensajes de respuesta del administrador:</div>
                            <div class="estadisticaValor">${mensajeDeRespuesta}</div>
                        </div>
                    </div>
                </div>
            `;

            statistics.appendChild(div);
            //añadir botón de ver más
            const button = document.createElement("button");
            button.textContent = "Ver más";

            button.onclick = () => {
                //eliminar el botón de ver más
                button.remove();
                //mostrar el resto de meses
                for (let i = 1; i < data.length; i++) {
                    let item = data[i];
                    const div = document.createElement("div");
                    div.classList.add("estadistica");

                    let year = item.mes.split("-")[0];
                    let month = item.mes.split("-")[1];
                    month = mesesArray[month];

                    let mensajeIniciadoPorAdmin = item.mensajes_iniciales_admin;
                    let mensajeIniciadoPorUsuario = item.mensajes_iniciales_usuario;
                    let mensajeDeRespuesta = item.mensajes_respuesta_admin;

                    div.innerHTML = `
                        <div class="estadisticaMes">${month} ${year}</div>
                        <div class="estadisticaInfo">
                            <div class="estadisticaItem">
                                <div class="estadisticaTitulo">Mensajes iniciados por el administrador:</div>
                                <div class="estadisticaValor">${mensajeIniciadoPorAdmin}</div>
                            </div>
                            <div class="estadisticaItem">
                                <div class="estadisticaTitulo">Mensajes iniciados por el usuario:</div>
                                <div class="estadisticaValor">${mensajeIniciadoPorUsuario}</div>
                            </div>
                            <div class="estadisticaItem">
                                <div class="estadisticaTitulo">Mensajes de respuesta del administrador:</div>
                                <div class="estadisticaValor">${mensajeDeRespuesta}</div>
                            </div>
                        </div>
                    `;

                    statistics.appendChild(div);
                }
            };

            statistics.appendChild(button);
        } catch (error) {
            console.error("Error al obtener las estadísticas:", error);
        }
    }

    // disconnectedCallback se llama cuando el componente es removido del DOM
    disconnectedCallback() {
        // Desconectar el socket si existe
        if (this.socket) {
            this.socket.emit('leaveRoom', this.getAttribute("codigoAdministradorBot")); // Salir de la sala antes de desconectar
            this.socket.disconnect();
            this.socket = null;
            console.log("Socket desconectado");
        }
    }
}

customElements.define("whatsapp-chat-user", WhatsappChatUser);

class WhatsappChatNotification extends HTMLElement {
    static get observedAttributes() {
        return ["userPhone", "codigoAdmin"];
    }

    constructor() {
        super();
        this.shadow = this.attachShadow({ mode: "open" });
        this.render();
        this.initSocket();
        this.fetchUnreadMessages();
        this.comprobarMensajes();
        this.socket = null;
        this.intervalId = null;
    }

    render() {
        console.log("Render method called");
        const uniqueId = `notification-${this.getAttribute("userPhone")}-${this.getAttribute("codigoAdmin")}`;
        this.shadow.innerHTML = `
            <style>
                .notification {
                    display: flex;
                    align-items: center;
                }

                .notification span {
                    width: 10px;
                    height: 10px;
                    background-color: red;
                    border-radius: 50%;
                    margin-right: 5px;
                    display: none; /* Ocultar por defecto */
                }
            </style>
            <div class="notification">
                <span id="${uniqueId}"></span>
                <slot></slot>
            </div>
        `;
    }

    initSocket() {
        const socketSingleton = SocketSingleton.getInstance();
        this.socket = socketSingleton.getSocket();
        const codigoAdmin = this.getAttribute("codigoAdmin");
        console.log(`User phone: ${codigoAdmin}`);
        this.socket.on("connect", () => {
            console.log("Conectado al servidor de Socket.IO");

            // Unirse a la sala específica
            /*
            socket.emit("joinRoom", codigoAdmin);
            console.log(`Unido a la sala: ${codigoAdmin}`);*/
        });

        this.socket.emit("joinRoom", codigoAdmin);

        this.socket.on("mensaje", (data) => {
            console.log("Mensaje recibido:", data);
            this.showNotification();
        });

        this.socket.on("disconnect", () => {
            console.log("Desconectado del servidor de Socket.IO");
        });
    }

    async fetchUnreadMessages() {
        const userPhone = this.getAttribute("userPhone");
        const codigoAdmin = this.getAttribute("codigoAdmin");
        const url = `https://wairbothook.wairbot.es/sin-leer?codigoAdmin=${codigoAdmin}`;

        try {
            const response = await fetch(url);
            const data = await response.json();
            const unreadPhones = data.map((item) => item.userPhone);
            if (unreadPhones.includes(userPhone)) {
                this.showNotification();
            }

            if (userPhone === "" && unreadPhones.length > 0) {
                this.showNotification();
            }

            if (userPhone === "" && unreadPhones.length === 0) {
                this.hideNotification();
            }

            if (userPhone !== "" && unreadPhones.length === 0) {
                this.hideNotification();
            }

        } catch (error) {
            console.error("Error fetching unread messages:", error);
        }
    }

    //quitar notificación
    hideNotification() {
        const userPhone = this.getAttribute("userPhone");
        const codigoAdmin = this.getAttribute("codigoAdmin");
        const uniqueId = `notification-${userPhone}-${codigoAdmin}`;
        const span = this.shadow.querySelector(`#${uniqueId}`);
        if (span) {
            span.style.display = "none";
        } else {
            console.error("No se encontró el span con el ID:", uniqueId);
        }
    }

    showNotification() {
        const userPhone = this.getAttribute("userPhone");
        const codigoAdmin = this.getAttribute("codigoAdmin");
        const uniqueId = `notification-${userPhone}-${codigoAdmin}`;
        const span = this.shadow.querySelector(`#${uniqueId}`);
        if (span) {
            span.style.display = "block";
        } else {
            console.error("No se encontró el span con el ID:", uniqueId);
        }
    }

    //comprobar cada 10 segundos si hay mensajes sin leer
    comprobarMensajes() {
        this.intervalId = setInterval(() => {
            this.fetchUnreadMessages();
        }, 10000);
    }

    // disconnectedCallback se llama cuando el componente es removido del DOM
    disconnectedCallback() {
        // Desconectar el socket si existe
        if (this.socket) {
            this.socket.emit('leaveRoom', this.getAttribute("codigoAdmin")); // Salir de la sala antes de desconectar
            this.socket.disconnect();
            this.socket = null;
            console.log("Socket desconectado");
        }

        // Limpiar el intervalo si está activo
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
            console.log("Intervalo de comprobación de mensajes limpiado");
        }
    }
}

customElements.define("whatsapp-chat-notification", WhatsappChatNotification);
