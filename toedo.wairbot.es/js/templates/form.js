function getInputText(id, label, value){
    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="text" id="${id}" value="${value}">
    </div>
    `;
    return input;
}

function getInputDate(id, label, value){
    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="date" id="${id}" value="${value}">
    </div>
    `;
    return input;
}

function getInputFile(id, label, value){
    let imagen = '';

    if(value){
        imagen = `<img src="${value}" alt="Imagen">`;
    }

    let input = `
    <div class="modalInputGroup">
        <div class="imagenPreview" id="imagenPreview">
            ${imagen}
        </div>
        <div class="modalInputGroup" style="flex-direction: column; align-items:flex-start;">
            <div class="modalLabel">${label}</div>
            <input type="file" id="${id}" onchange="previewFile(this)"  style=" width:-webkit-fill-available;">
        </div>
    </div>
    `;
    return input;
}

function previewFile(input) {
    let preview = document.getElementById('imagenPreview');
    let file    = input.files[0];
    let reader  = new FileReader();

    reader.onloadend = function () {
        preview.innerHTML = `<img src="${reader.result}" alt="Imagen">`;
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

function getDataList(id, label, options, value){
    console.log({id, label, options, value});
    // options = {id1: value1, id2: value2, id3: value3}
    // value = id1
    
    //optener el value por el id en options, si no existe value = "";
    let valueName = '';
    for(let key in options){
        if(key === value){
            valueName = options[key];
        }
    }

    if(valueName !== ""){
        valueName = valueName + " | " + value;
    }

    let dataList = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input list="${id}_list" id="${id}" value="${valueName}">
        <datalist id="${id}_list">
    `;
    for(let key in options){
        dataList += `<option value="${options[key]} | ${key}">`;
    }
    dataList += `
        </datalist>
    </div>
    `;
    return dataList;
}

function getDataListSimple(id, label, options, value, onchangeFunction = "") {
    if (onchangeFunction !== "") {
        onchangeFunction = `onchange="${onchangeFunction}(this.value, this.id)"`;
    }

    let dataList = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input list="${id}_list" id="${id}" value="${value}" ${onchangeFunction}>
        <datalist id="${id}_list">
    `;
    for(let key in options){
        dataList += `<option value="${options[key]}">`;
    }

    dataList += `
        </datalist>
    </div>
    `;

    return dataList;
}

function getBoton(texto, funcion, id){
    let idEsNumero = true;
    
    if(isNaN(id)){
        idEsNumero = false;
    }

    if(idEsNumero){
        let boton = `
        <div class="modalBoton" onclick="${funcion}(${id})">${texto}</div>
        `;
        return boton;
    }else{
        let boton = `
        <div class="modalBoton" onclick="${funcion}('${id}')">${texto}</div>
        `;
        return boton;
    }
}

function createForm(campos, titulo){
    let form = `
    <div class="modalElemento modalFormulario">
    `;
    for(let c of campos){
        form += c;
    }
    form += `
    </div>
    `;

    return form;
}
    
function getSelect(id, label, options, value){
    let select = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <select id="${id}">
    `;
    for(let key in options){
        select += `<option value="${options[key]}"`;
        if(options[key] === value){
            select += ` selected`;
        }
        select += `>${options[key]}</option>`;
    }
    select += `
        </select>
    </div>
    `;
    return select;
}

/*
$mensajes[] = [
            "tipo" => "contestado",
            "mensaje" => $mensaje,
            "fecha" => date("Y-m-d H:i:s")
        ];
$mensajes[] = [
            "tipo" => "enviado",
            "mensaje" => $mensaje,
            "fecha" => date("Y-m-d H:i:s")
        ];
Ejemplo en JSON:
[{"tipo": "contestado","mensaje": "Hola, ¿en qué puedo ayudarte?","fecha": "2021-05-31 10:00:00"},{"tipo": "enviado","mensaje": "Quiero información sobre un producto","fecha": "2021-05-31 10:01:00"},{"tipo": "contestado","mensaje": "Claro, ¿qué producto es?","fecha": "2021-05-31 10:02:00"},{"tipo": "enviado",    "mensaje": "Es un producto de limpieza","fecha": "2021-05-31 10:03:00"}]
*/
function chatPartes(conversacion, enviado, aprobado, idMaquina, idParte, telefono_whatsapp, nombre_cliente){
    console.log({conversacion, enviado, aprobado, idMaquina, idParte, telefono_whatsapp});
    //si envidado es SI enviado es checked
    enviado = enviado === "SI" ? "checked" : "";

    //si aprobado es PENDIENTE aprobado es "" si no es checked
    aprobado = aprobado === "PENDIENTE" ? "" : "checked";

    let chat = `
        <div class="chatContainer">

            <div style="width: 100%; height: 500px;">
                <whatsapp-chat-user
                    userName="${nombre_cliente}"
                    userPhone="${telefono_whatsapp}"
                    codigoAdministradorBot="wairbotrepair1_CHAT_"
                    passwordAdministradorBot="15e2b0d3c33891ebb0f1ef609ec419420c20e320ce94c65fbc8c3312448eb225"
                ></whatsapp-chat-user>
            </div>

            <div class="chatInputs">
                <div class="inptutChatsCheckContainer">
                    <div class="inptutChatsCheck">
                        <input type="checkbox" id="enviado" ${enviado} onchange="enviadoCliente(${idParte}, ${idMaquina}, this.checked)">
                        <label for="enviado">Enviado</label>
                    </div>
                    <div class="inptutChatsCheck">
                        <input type="checkbox" id="aprobado" ${aprobado} onchange="aprobarCliente(${idParte}, ${idMaquina}, this.checked)">
                        <label for="aprobado">Aprobado</label>
                    </div>
                </div>
                <button class="chatEnviar" onclick="enviarPresupuesto(${idMaquina}, ${idParte}, '${telefono_whatsapp}')">Enviar Presupuesto</button>
            </div>
        </div>
    `;

    return chat;
}


function tiemposPartes(tiempos, idParte){
    let tiemposHtml = "";
    let count = 0;

    for(let t of tiempos){
        let inicio = "";
        let fin = "";
        let claseExtra = "";
        if(t.inicio === 0){
            inicio = `<div class="tiempoBoton" onclick="gestionarTiempos(${idParte})">Iniciar</div>`;
            claseExtra = "tiempoFormGroupConBoton";
        }
        if(t.fin === 0 && t.inicio !== 0){
            fin = `<div class="tiempoBoton" onclick="gestionarTiempos(${idParte})">Finalizar</div>`;
            claseExtra = "tiempoFormGroupConBoton";
        }

        //un input que muestre y permita editar el tiempo en formato HH:MM:SS
        let tHtml = `
            <div class="tiempoFormGroup ${claseExtra}">
                <div class="tiempoLabel">${t.usuario}</div>
                <input type="time" id="tiempo_${count}" value="${t.horasTotales}" onchange="actualizarTiempo(${idParte}, ${count}, this.value)">
                ${inicio}
                ${fin}
            </div>
        `;

        tiemposHtml += tHtml;
        count++;
    }

    //sumar tiempos
    let horasTotales = 0;
    let minutosTotales = 0;
    let segundosTotales = 0;
    for(let t of tiempos){
        let tiempo = t.horasTotales.split(":");
        horasTotales += parseInt(tiempo[0]);
        minutosTotales += parseInt(tiempo[1]);
        segundosTotales += parseInt(tiempo[2]);
    }

    //pasar los segundos a minutos y horas
    let minutos = Math.floor(segundosTotales / 60);
    segundosTotales = segundosTotales % 60;
    minutosTotales += minutos;

    //pasar los minutos a horas
    let horas = Math.floor(minutosTotales / 60);
    minutosTotales = minutosTotales % 60;
    horasTotales += horas;

    //formatear los tiempos
    horasTotales = horasTotales < 10 ? "0" + horasTotales : horasTotales;
    minutosTotales = minutosTotales < 10 ? "0" + minutosTotales : minutosTotales;
    segundosTotales = segundosTotales < 10 ? "0" + segundosTotales : segundosTotales;

    let tiempoTotal = `${horasTotales}:${minutosTotales}:${segundosTotales}`;

    let tiemposContainer = `
        <div class="tiemposContainer">
            <div class="tituloTiempos">
                Tiempos
            </div>
            ${tiemposHtml}
            <div class="tiempoFormGroup">
                <div class="tiempoTotal">Total: ${tiempoTotal}</div>
            </div>
        </div>
    `;

    return tiemposContainer;
}


let pintando = false;
let ctx; // Se declara aquí para tener un alcance más amplio.

function obtenerContextoCanvas() {
    if (!ctx) { // Solo inicializar si ctx aún no ha sido inicializado.
        const canvas = document.getElementById('canvasFirma');
        ctx = canvas.getContext('2d'); // Actualiza la variable de ámbito más amplio.

        // Solo rellenar el fondo si es necesario (por ejemplo, al inicializar)
        if (!canvas.hasAttribute('data-fondo-establecido')) {
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            canvas.setAttribute('data-fondo-establecido', 'true');
        }
    }
}

function iniciarDibujo(e) {
    e.preventDefault(); // Prevenir comportamiento por defecto para eventos táctiles.
    obtenerContextoCanvas(); // Asegurarse de que ctx esté inicializado.
    pintando = true;
    dibujar(e);
}

function terminarDibujo(e) {
    if (e) e.preventDefault(); // Solo llamar a preventDefault si el evento existe.
    if (!ctx) obtenerContextoCanvas(); // Asegurarse de que ctx esté inicializado.
    pintando = false;
    ctx.beginPath();
}

function dibujar(e) {
    e.preventDefault(); // Prevenir comportamiento por defecto para eventos táctiles.
    if (!ctx) obtenerContextoCanvas(); // Asegurarse de que ctx esté inicializado.
    if (!pintando) return;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    const rect = ctx.canvas.getBoundingClientRect(); // Usar ctx.canvas para obtener el canvas.
    // Ajustar para eventos táctiles.
    const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
    const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;

    ctx.lineTo(x, y); // Dibuja una línea hasta la posición actual del toque.
    ctx.stroke(); // Hace visible la línea.
    ctx.beginPath(); // Comienza un nuevo camino.
    ctx.moveTo(x, y); // Mueve el camino a la posición actual del toque para el próximo movimiento.
}

// Modificar el HTML para usar eventos táctiles además de los eventos de mouse.
function firmaPartes(idParte) {
    let html = `
        <style>
            #contenedorFirma {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            #canvasFirma {
                border: 1px solid black;
            }

            #botonEnviar {
                margin-top: 10px;
                padding: 10px;
                border: none;
                border-radius: 5px;
                background-color: #007bff;
                cursor: pointer;
                color: white;
            }
        </style>
        <div id="contenedorFirma">
            <canvas id="canvasFirma" width="250" height="250"
                onmousedown="iniciarDibujo(event)"
                onmousemove="dibujar(event)"
                onmouseup="terminarDibujo(event)"
                onmouseout="terminarDibujo(event)"
                ontouchstart="iniciarDibujo(event)"
                ontouchmove="dibujar(event)"
                ontouchend="terminarDibujo(event)"></canvas>
            <button id="botonEnviar" onclick="enviarFirma(${idParte})">Enviar Firma</button>
        </div>
    `;

    return html;
}

async function botonDescargarQr(codigo){
    let url = window.location.href || window.location.pathname;
    let index = url.indexOf('?');
    if(index !== -1){
        url = url.substring(0, index);
    }
    url = url + `?codigo=${codigo}`;

    await generarYDescargarQR(url);
}

async function generarYDescargarQR(url) {
    // Obtener tipo_maquina
    let empresa = "TOEDO™";
    let tipo_maquina = document.getElementById('tipo_maquina').value;

    // Genera el QR en un canvas temporal
    const qrCanvas = document.createElement('canvas');
    await QRCode.toCanvas(qrCanvas, url, {
        width: 500,
        margin: 2.5,
    });

    // Crear un nuevo canvas para combinar el QR y el texto
    const combinedCanvas = document.createElement('canvas');
    const context = combinedCanvas.getContext('2d');
    const qrHeight = qrCanvas.height;
    const textHeight = 30; // Altura del área de texto
    combinedCanvas.width = qrCanvas.width;
    combinedCanvas.height = qrHeight + textHeight;

    // Dibujar el QR en el nuevo canvas
    context.drawImage(qrCanvas, 0, 0);

    // Añadir el texto en la misma línea debajo del QR
    context.font = 'bold 25px Arial';
    context.textAlign = 'center';
    context.fillText(`${empresa} - ${tipo_maquina}`, combinedCanvas.width / 2, qrHeight + 25);

    // Convertir el nuevo canvas a una imagen PNG
    const imagenUrl = combinedCanvas.toDataURL("image/png").replace("image/png", "image/octet-stream");

    // Crear un enlace para descargar
    const descargarLink = document.createElement('a');
    descargarLink.href = imagenUrl;
    descargarLink.download = 'QR.png';

    // Simular un clic en el enlace para descargar
    document.body.appendChild(descargarLink);
    descargarLink.click();
    document.body.removeChild(descargarLink);
}