async function getTiempos() {
    //si location es tiempos se muestra la pantalla de tiempos
    let location = localStorage.getItem("location");
    console.log(location);
    if (location === "tiempos") {
        mostrarPartesTiempos();
    }else{
        let contenido = await tecladoNumerico();
        createModal("Acceso", contenido, "checkTecnico", "administrador");
    }
}

async function tecladoNumerico(id = null, tiempo = null, createModalCheck = false) {;
    let tecnicos = localStorage.getItem("nombresDeTecnicos");
    let user = localStorage.getItem("usuario");
    tecnicos = JSON.parse(tecnicos);
    user = JSON.parse(user);
    tecnicos[user.id] = user.nombre;

    //tecnico options
    let options = "";
    for (let key in tecnicos) {
        options += `<option value="${key}">${tecnicos[key]}</option>`;
    }

    let html = `
     <div class="checkTecnicoModal">
        <div class="selectTecnico">
            <select id="selectTecnicoTeclado">
                ${options}
            </select>
        </div>
        <div class="inputPassword">
            <input type="hidden" id="inputPassword">
            <input type="password" id="fakePassword">
            <div class="error"></div>
            <div class="tecladoNumerico">
                <div class="fila">
                    <button onclick="marcarTecladoNumerico(1, ${id}, '${tiempo}')">1</button>
                    <button onclick="marcarTecladoNumerico(2, ${id}, '${tiempo}')">2</button>
                    <button onclick="marcarTecladoNumerico(3, ${id}, '${tiempo}')">3</button>
                </div>
                <div class="fila">
                    <button onclick="marcarTecladoNumerico(4, ${id}, '${tiempo}')">4</button>
                    <button onclick="marcarTecladoNumerico(5, ${id}, '${tiempo}')">5</button>
                    <button onclick="marcarTecladoNumerico(6, ${id}, '${tiempo}')">6</button>
                </div>
                <div class="fila">
                    <button onclick="marcarTecladoNumerico(7, ${id}, '${tiempo}')">7</button>
                    <button onclick="marcarTecladoNumerico(8, ${id}, '${tiempo}')">8</button>
                    <button onclick="marcarTecladoNumerico(9, ${id}, '${tiempo}')">9</button>
                </div>
                <div class="fila ultimaFila">
                    <button onclick="marcarTecladoNumerico(0, ${id}, '${tiempo}')">0</button>
                    <button onclick="marcarTecladoNumerico('borrar')">Borrar</button>
                </div>
            </div>
        </div>
     </div>
    `;

    if (createModalCheck) {
        createModal("Acceso", html, "checkTecnico", "administrador");
    
        // Hacer Click en el select para que aparezca abierto
        let selectTecnico = document.getElementById("selectTecnicoTeclado");
        if (selectTecnico) {
            selectTecnico.focus();
            // Cambiar el valor del select para abrir el menú desplegable
            selectTecnico.size = selectTecnico.options.length;
        } else {
            console.error("El elemento selectTecnico no existe.");
        }
    } else {
        return html;
    }
        
}

//marcar teclado numerico
async function marcarTecladoNumerico(numero, id = null, tiempo = null) {
    if (numero === "borrar") {
        let inputPassword = document.getElementById("inputPassword");
        let fakePassword = document.getElementById("fakePassword");
        inputPassword.value = "";
        fakePassword.value = "";
        return;
    }

    let error = document.querySelector(".error");
    error.innerHTML = "";

    let inputPassword = document.getElementById("inputPassword");
    let fakePassword = document.getElementById("fakePassword");
    inputPassword.value += numero;
    fakePassword.value = "•".repeat(inputPassword.value.length);

    if (inputPassword.value.length == 4) {      
        let user = localStorage.getItem("usuario");
        user = JSON.parse(user);

        if (id === null && user.nombre == "admin") {
            let id = user.id;
            let password = inputPassword.value;
            
            let check = await checkPassword(id, password);

            if(check){
                let estadoPantallaTiempos = localStorage.getItem("estadoPantallaTiempos");
                if (estadoPantallaTiempos === null) {
                    localStorage.setItem("estadoPantallaTiempos", 0);
                    estadoPantallaTiempos = 0;
                }

                if (estadoPantallaTiempos == 0) {
                    localStorage.setItem("estadoPantallaTiempos", 1);
                    destruirModal();

                    let nav2_menu_item = document.querySelectorAll(".nav2_menu_item");
                    let nav3_menu_item = document.querySelectorAll(".nav3_menu_item");

                    nav2_menu_item.forEach(element => {
                        if (element.innerHTML !== "TIEMPOS") {
                            element.style.display = "none";
                        }
                    });

                    nav3_menu_item.forEach(element => {
                        if (element.innerHTML !== "TIEMPOS") {
                            element.style.display = "none";
                        }
                    });

                    await mostrarPartesTiempos();
                }

                if (estadoPantallaTiempos == 1) {
                    localStorage.setItem("estadoPantallaTiempos", 0);
                    let pantalla = document.getElementById("administrador");
                    pantalla.innerHTML = "";

                    let nav2_menu_item = document.querySelectorAll(".nav2_menu_item");
                    let nav3_menu_item = document.querySelectorAll(".nav3_menu_item");

                    nav2_menu_item.forEach(element => {
                        element.style.display = "block";
                    });

                    nav3_menu_item.forEach(element => {
                        element.style.display = "block";
                    });
                }

            }else{
                let error = document.querySelector(".error");
                error.innerHTML = "Contraseña incorrecta";

                inputPassword.value = "";
                fakePassword.value = "";
            }
        }

        if(id !== null && tiempo === 'null'){
            let password = inputPassword.value;
            let selectTecnico = document.getElementById("idTecnico_" + id);
            let tecnico = selectTecnico.innerHTML;
            let check = await checkPassword(tecnico, password);

            if(check){
                destruirModal();
                await openParte(id);
            }else{
                let error = document.querySelector(".error");
                error.innerHTML = "Contraseña incorrecta";

                inputPassword.value = "";
                fakePassword.value = "";
            }
        }

        if (tiempo !== 'null') {
            let password = inputPassword.value;
            let selectTecnico = document.getElementById("idTecnico_" + id);
            let tecnico = selectTecnico.innerHTML;
            let check = await checkPassword(tecnico, password);

            if(check){
                destruirModal();
                await controlReloj(id);
                await gestionarTiempos(id);
            }else{
                let error = document.querySelector(".error");
                error.innerHTML = "Contraseña incorrecta";

                inputPassword.value = "";
                fakePassword.value = "";
            }
        }
    }
}

async function guardarDescripcionTarea(id, tiempo) {
    let descripcionTarea = document.getElementById("descripcionTareaTiempo").value;
    let tipo_tarea = document.getElementById("tipo_tarea").value;
    
    let parada = `Parada en ${tiempo}`;

    let coste = "0";
    //si tipo tarea contiene :: el coste es el segundo valor
    if (tipo_tarea.includes("::")) {
        let tipo_tarea_array = tipo_tarea.split("::");
        coste = tipo_tarea_array[1];

        tipo_tarea = tipo_tarea_array[0];
    }

    descripcionTarea = `${tipo_tarea} - ${descripcionTarea} - ${parada}`;

    let datos = {
        data: {
          descripcion: descripcionTarea,
          tipo: tipo_tarea,
          referencia: "",
          coste: coste,
          id_parte: id,
        }
    };

    console.log(datos);

    let clase = "Tareas";
    let funcion = "create";

    let data = await queryAsync(clase, funcion, datos);

    destruirModal();
}

async function gestionarTiempos(id) {
    let clase = 'Partes';
    let funcion = 'gestionarTiempos';
    let datos = {
        idParte: id
    };

    let data = await queryAsync(clase, funcion, datos);
}

//detener un reloj por su id
async function controlReloj(id) {
    let reloj = document.getElementById("tiempoTotal_" + id);
    
    //si el reloj esta parado se enciende
    if (reloj.classList.contains("relorParado")) {
        reloj.classList.remove("relorParado");
        reloj.classList.add("relojEncendido");

        //cardTiempos_id poner con fondo verde claro
        let card = document.getElementById("cardTiempos_" + id);
        card.style.backgroundColor = "#d4edda";
    }else{
        reloj.classList.remove("relojEncendido");
        reloj.classList.add("relorParado");

        //cardTiempos_id poner con fondo blanco
        let card = document.getElementById("cardTiempos_" + id);
        card.style.backgroundColor = "white";

        //obtener el tiempo de la card
        let tiempo = document.getElementById("tiempoTotal_" + id).innerHTML;

        nombre_select = 'tipo_tarea';
        idDataList = 'tipo_tarea';
        labelDataList = 'Tipo Tarea';
        valueDataList = '';
        tabla_select = 'tareas';
        id_input_relacionado = '';
        onchangeFunction = '';
        let tipo_tarea = await getDataListCompleto(idDataList, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

        //abrir un modal para introducir una descripción de la tarea realizada en ese tiempo
        let contenido = `
            <div class="descripcionTareaTiempo">
                ${tipo_tarea}
                <textarea class="descripcionTareaTiempoTextarea" id="descripcionTareaTiempo" placeholder="Descripción de la tarea realizada"></textarea>
                <div style="display: flex; flex-direction: row; justify-content: space-between; width: 100%;">
                    <div class="botonGuardarDescripcionTareaTiempo" onclick="guardarDescripcionTareaCrearOtraTarea(${id}, '${tiempo}')">Guardar y Crear Otra Tarea</div>
                    <div class="botonGuardarDescripcionTareaTiempo" onclick="guardarDescripcionTarea(${id}, '${tiempo}')">Guardar y Cerrar</div>
                </div>
            </div>
        `;
        createModal("Descripción de la tarea", contenido, "guardarDescripcionTarea", "administrador", id);
    }

    //apagar relojes
    apagarRelojes();
    encenderRelojes();

    //al id tipo_tarea añadir la propiedad inputmode="numeric"
    let tipo_tarea = document.getElementById("tipo_tarea");
    tipo_tarea.setAttribute("inputmode", "numeric");
}

async function guardarDescripcionTareaCrearOtraTarea(id, tiempo) {
    let descripcionTarea = document.getElementById("descripcionTareaTiempo").value;
    let tipo_tarea = document.getElementById("tipo_tarea").value;
    
    let parada = `Parada en ${tiempo}`;

    let coste = "0";
    //si tipo tarea contiene :: el coste es el segundo valor
    if (tipo_tarea.includes("::")) {
        let tipo_tarea_array = tipo_tarea.split("::");
        coste = tipo_tarea_array[1];

        tipo_tarea = tipo_tarea_array[0];
    }

    descripcionTarea = `${tipo_tarea} - ${descripcionTarea} - ${parada}`;

    let datos = {
        data: {
          descripcion: descripcionTarea,
          tipo: tipo_tarea,
          referencia: "",
          coste: coste,
          id_parte: id,
        }
    };

    console.log(datos);

    let clase = "Tareas";
    let funcion = "create";

    let data = await queryAsync(clase, funcion, datos);

    document.getElementById("descripcionTareaTiempo").value = "";

    //borrar el tipo_tarea
    tipo_tarea = document.getElementById("tipo_tarea");
    tipo_tarea.value = "";
}


// mostrar partes
async function mostrarPartesTiempos() {
    localStorage.setItem("location", "tiempos");
    navCondensed(true);

    //OBTENER DATOS
    let clase = 'Partes';
    let funcion = 'getPartesSinFinalizar';
    let datos = {
        rol: 'ADMINISTRADOR'
    };

    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    console.log(data);

    //obtenter todos los tipos de urgencia de data
    let tiposUrgencia = [];
    for (let parte of data) {
        if (parte.tipo_urgencia) {
            tiposUrgencia.push(parte.tipo_urgencia);
        }
    }

    //eliminar duplicados
    tiposUrgencia = [...new Set(tiposUrgencia)];

    console.log({tiposUrgencia});

    //generar botones de tipos de urgencia
    let botonesTiposUrgencia = "";
    for (let tipoUrgencia of tiposUrgencia) {
        //decodificar url tipo_urgencia
        tipoUrgencia = decodeURIComponent(tipoUrgencia);
        botonesTiposUrgencia += `<button onclick="filtrarTiemposUrgencia('${tipoUrgencia}')">${tipoUrgencia}</button>`;
    }

    //si data está vacio hacer un delay de 1 segundo y volver a llamar a la función
    /*
    if (data.length === 0) {
        await new Promise(resolve => setTimeout(resolve, 1000));
        mostrarPartesTiempos();
        return;
    }*/

    $cards = await getCardsPartesTiempos(data);

    let tecnicosSelect = localStorage.getItem("nombresDeTecnicos");
    tecnicosSelect = JSON.parse(tecnicosSelect);

    /*
    let tecnicos = "<option value='0'>Todos</option>";
    for (let key in tecnicosSelect) {
        tecnicos += `<option value="${key}">${tecnicosSelect[key]}</option>`;
    }

    let html = `
        <div class="tiempos">
            <div class="tiemposHeader">
                <div class="tiemposHeaderTitle">Tiempos</div>
                <div class="tiemposHeaderSelect">
                    <select id="selectTecnico" onchange="filtrarTiempos()">
                        ${tecnicos}
                    </select>
                </div>
            </div>
            <div class="tiemposCards">
                ${$cards}
            </div>
        </div>
    `;*/

    let tecnicos = "<button onclick='filtrarTiempos(0)'>Todos</button>";

    for (let key in tecnicosSelect) {
        tecnicos += `<button onclick="filtrarTiempos(${key})">${tecnicosSelect[key]}</button>`;
    }
    
    let html = `
        <div class="tiempos">
            <div class="tiemposHeader">
                <div class="tiemposHeaderButtons">
                    ${tecnicos}
                </div>
                <div class="tiemposHeaderButtons">
                    ${botonesTiposUrgencia}
                </div>
            </div>
            <div class="tiemposCards">
                ${$cards}
            </div>
        </div>
    `;

    let pantalla = document.getElementById("administrador");
    pantalla.innerHTML = html;

    encenderRelojes();
}

async function filtrarTiemposUrgencia(tipoUrgencia) {
    let cards = document.querySelectorAll(".cardTiempos");
    for (let card of cards) {
        let tipoUrgenciaCard = card.querySelector(".tipoUrgencia").innerHTML;
        if (tipoUrgenciaCard == tipoUrgencia) {
            card.style.display = "flex";
        }else{
            card.style.display = "none";
        }
    }
}

async function filtrarTiempos(id) {
    let tecnico = id

    //ocultar las cards que no sean del tecnico seleccionado
    let cards = document.querySelectorAll(".cardTiempos");
    for (let card of cards) {
        let idTecnico = card.querySelector(".idTecnicoOculto").innerHTML;
        if (tecnico == 0) {
            card.style.display = "flex";
        }else{
            if (idTecnico == tecnico) {
                card.style.display = "flex";
            }else{
                card.style.display = "none";
            }
        }
    }
}

async function getCardsPartesTiempos(data) {
    let cards = "";
    for (let card of data) {
        console.log(card);
        let nombreMaquina = card.maquina.maquina + " " + card.maquina.marca + " " + card.maquina.modelo;
        let codigoMaquina = card.maquina.codigo;
        let nombreCliente = card.cliente.nombre;
        let telefonoWhatsApp = card.cliente.telefono_whatsapp;
        let fecha = card.fecha;
        let notas = card.notas;
        let tecnicoNombre = card.tecnico ? card.tecnico.nombre : "";
        let tecnicoId = card.tecnico ? card.tecnico.id : 0;
        let tipo_urgencia = card.tipo_urgencia ? card.tipo_urgencia : "";

        //decodificar url tipo_urgencia
        tipo_urgencia = decodeURIComponent(tipo_urgencia);

        let tiempos = JSON.parse(card.tiempos);

        console.log(tiempos);

        let tiempoTotalSegundos = 0;
        for (let tiempo of tiempos) {
            tiempoTotalSegundos += convertirASegundos(tiempo.horasTotales);
        }

        let tiempoTotal = convertirAHorasMinutosSegundos(tiempoTotalSegundos);

        let relojParado = 'relorParado';
        let fondoVerdeClaro = '';

        //obtener el ultimo tiempo
        let ultimoTiempo = tiempos[tiempos.length - 1];
        console.log(ultimoTiempo);
        if (ultimoTiempo.fin == 0 && ultimoTiempo.inicio != 0) {
            relojParado = 'relojEncendido';

            //obtener el tiempo actual
            let tiempoActual = new Date();

            //obtener la diferencia de tiempo
            let diferencia = tiempoActual - new Date(ultimoTiempo.inicio);
            diferencia = Math.floor(diferencia / 1000);

            //sumar la diferencia al tiempo total
            tiempoTotalSegundos += diferencia;
            tiempoTotal = convertirAHorasMinutosSegundos(tiempoTotalSegundos);

            fondoVerdeClaro = 'style="background-color: #d4edda;"';
        }

        let notificacionCliente = `
            <div style="display: flex; align-items: center;">
                <b>Cliente:</b>&nbsp;${nombreCliente}&nbsp;
                <whatsapp-chat-notification
                    userPhone="${telefonoWhatsApp}"
                    codigoAdmin="wairbotrepair1_CHAT_"
                ></whatsapp-chat-notification>
            </div>
        `;

        //Si idTecnicoOculto es 0 al hacer click en la card se abre el parte sin contraseña
        let onclickInfoCardTiempos = "";
        let onclickTiempoTotal = "";
        if (tecnicoId == 0) {
            onclickInfoCardTiempos = `onclick="openParte(${card.id})"`;
            onclickTiempoTotal = ``;
        }else{
            onclickInfoCardTiempos = `onclick="tecladoNumerico(${card.id}, null, true)"`;
            onclickTiempoTotal = `onclick="tecladoNumerico(${card.id}, '${tiempoTotal}', true)"`;
        }

        
        cards += `
            <div class="cardTiempos" id="cardTiempos_${card.id}" ${fondoVerdeClaro}>
                <div class="infoCardTiempos" ${onclickInfoCardTiempos}>
                    <div class="nombreMaquina"><b>Maquina:</b>&nbsp;${nombreMaquina}</div>
                    <div class="codigoMaquina"><b>Código:</b>&nbsp;${codigoMaquina}</div>
                    <div class="notas">${notas}</div>
                    <div class="nombreCliente">${notificacionCliente}</div>
                    <div class="tipoUrgencia">${tipo_urgencia}</div>
                </div>
                <div class="infoTiemposCardTiempos">
                    <div class="idTecnicoOculto" style="display: none;" id="idTecnico_${card.id}">${tecnicoId}</div>
                    <div class="fecha">${fecha}</div>
                    <div class="tecnico"><b>Técnico:</b>&nbsp;${tecnicoNombre}</div>
                    <div class="tiempoTotal ${relojParado}" id="tiempoTotal_${card.id}" ${onclickTiempoTotal}>
                        ${tiempoTotal}
                    </div>
                </div>
            </div>
        `;
    }

    return cards;
}

//encender relojes
let intervaloRelojes;
async function encenderRelojes() {
    let cards = document.querySelectorAll(".relojEncendido");
    
    //añadir un segundo a cada reloj cada segundo
    intervaloRelojes = setInterval(() => {
        for (let card of cards) {
            let tiempo = card.innerHTML;
            let segundos = convertirASegundos(tiempo);
            segundos++;
            card.innerHTML = convertirAHorasMinutosSegundos(segundos);
        }
    }, 1000);
}

function apagarRelojes() {
    clearInterval(intervaloRelojes);
}

function convertirAHorasMinutosSegundos(segundos) {
    const horas = Math.floor(segundos / 3600);
    segundos %= 3600;
    const minutos = Math.floor(segundos / 60);
    segundos %= 60;
    return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
}

function convertirASegundos(horasTotales) {
    const [horas, minutos, segundos] = horasTotales.split(':').map(Number);
    return horas * 3600 + minutos * 60 + segundos;
}

async function checkPassword(id, password) {
    let clase = 'Usuarios';
    let funcion = 'checkPassword';
    let datos = {
        id: id,
        password: password
    };

    let data = await queryAsync(clase, funcion, datos);
    
    if (data.status == "success") {
        return true;
    }else{
        return false;
    }
}