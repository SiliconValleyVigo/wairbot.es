/*
Ejemplo de JSON para crear campos de formulario
{
  "campos": [
    {
      "tipo": "text",
      "id": "nombre",
      "label": "Nombre",
      "defaultValue": "Juan Pérez",
      "disabled": true
    },
    {
      "tipo": "dataList",
      "id": "ciudad",
      "label": "Ciudad",
      "options": ["Madrid", "Barcelona", "Valencia"],
      "onchange": "actualizarCiudad"
    },
    {
      "tipo": "textarea",
      "id": "descripcion",
      "label": "Descripción",
      "defaultValue": "Descripción del producto"
    },
    {
      "tipo": "file",
      "id": "archivo",
      "label": "Subir Archivo"
    },
    {
      "tipo": "select",
      "id": "pais",
      "label": "País",
      "options": {
        "es": "España",
        "fr": "Francia",
        "it": "Italia"
      }
    },
    {
      "tipo": "phone_link",
      "id": "telefono",
      "label": "Teléfono",
      "defaultValue": "123456789"
    },
    {
      "tipo": "date",
      "id": "fecha",
      "label": "Fecha",
      "defaultValue": "2023-01-01"
    },
    {
      "tipo": "dataListApi",
      "id": "producto",
      "label": "Producto",
      "options": "ProductoApi::listarNombreMasId"
    },
    {
      "tipo": "dataListCompleto",
      "id": "categoria",
      "label": "Categoría",
      "options": "categoria::categorias" //nombre_select::tabla_select
    },
    {
      "tipo": "number",
      "id": "cantidad",
      "label": "Cantidad",
      "defaultValue": 1
    },
    {
      "tipo": "time",
      "id": "hora",
      "label": "Hora",
      "defaultValue": "12:00"
    },
    {
      "tipo": "json",
      "id": "json_date", //por el momento solo se puede usar json_date
      "label": "Fechas JSON",
      "defaultValue": "[\"2023-01-01\", \"2023-02-01\"]"
    },
    {
      "tipo": "coin",
      "id": "precio",
      "label": "Precio",
      "defaultValue": "10.00"
    }
  ],
  "botonEliminar": {
    "texto": "Eliminar",
    "funcion": "eliminarCampo",
    "id": "btnEliminar"
  }
}
*/
async function crearCamposDeFormulario(campos, data = null, botonEliminar = null){
    let camposResult = [];
    for(let campo of campos){
        let tipo = campo.tipo;
        let id = campo.id;
        let label = campo.label;
        let value = (data && data.hasOwnProperty(id)) ? data[id] : "";

        //si value es "" comprueba si existe campo.default y lo asigna
        if(value === "" && campo.hasOwnProperty("defaultValue")){
            value = campo.defaultValue;
        }

        //getInputTextArea, getInputFile
        if(tipo === "text"){
            let disabled = campo.hasOwnProperty("disabled") ? campo.disabled : false;
            let input = getInputText(id, label, value, disabled);
            camposResult.push(input);
        } else if(tipo === "dataList"){
            let options = campo.options;

            //comprobar si existe el campo onchange
            if(campo.hasOwnProperty("onchange")){
                let onchange = campo.onchange;
                let input = getSimpleDataList(id, label, options, value, onchange);
                camposResult.push(input);
            } else {
                let input = getSimpleDataList(id, label, options, value)
                camposResult.push(input);
            }
        } else if(tipo === "textarea"){
            //si no existe el campo disabled, se le asigna false si existe se le asigna el valor
            let input = getInputTextArea(id, label, value);
            camposResult.push(input);
        } else if(tipo === "file"){
            let input = getInputFile(id, label, value);
            camposResult.push(input);
        } else if(tipo === "select"){
            let options = campo.options;
            let input = getSelect(id, label, options, value);
            camposResult.push(input);
        } else if(tipo === "phone_link"){
            let input = getPhoneLink(id, label, value);
            camposResult.push(input);
        } else if(tipo === "date"){
            let input = getInputDate(id, label, value)
            camposResult.push(input);
        } else if(tipo === "dataListApi"){
            //sepsarar options por ::
            let optionsArray = campo.options.split("::");
            let apiClass = optionsArray[0];
            let apiFunction = optionsArray[1];
            let input = await getDataListApi(id, label, value, apiClass, apiFunction);
            camposResult.push(input);
        } else if(tipo === "dataListCompleto"){
            let optionsArray = campo.options.split("::");
            let nombre_select = optionsArray[0];
            let tabla_select = optionsArray[1];
            
            let id_input_relacionado = "";
            let onchangeFunction = "";

            if(optionsArray.length === 4){
                id_input_relacionado = optionsArray[2];
                onchangeFunction = optionsArray[3];
            }
        
            let input = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);
            camposResult.push(input);
        } else if(tipo === "number"){
            let input = getInputNumber(id, label, value);
            camposResult.push(input);
        } else if(tipo === "time"){
            let input = getInputTime(id, label, value);
            camposResult.push(input);
        } else if(tipo === "json"){
            let input = getInputJson(id, label, value);
            camposResult.push(input);
        } else if(tipo === "coin"){
            let input = getInputCoin(id, label, value);
            camposResult.push(input);
        } else if(tipo === "botonesCheckListApi"){
            let optionsArray = campo.options.split("::");
            let apiClass = optionsArray[0];
            let apiFunction = optionsArray[1];
            let input = await getBotonesCheckListApi(id, label, value, apiClass, apiFunction);
            camposResult.push(input);
        } else if(tipo === "html"){
            let input = value;
            camposResult.push(input);
        }
    }

    if(botonEliminar !== null){
        let boton = getBoton(botonEliminar.texto, botonEliminar.funcion, botonEliminar.id);
        camposResult.push(boton);
    }

    return camposResult;
}

async function getBotonesCheckListApi(id, label, value, apiClass, apiFunction){
    let clase = apiClass;
    let funcion = apiFunction;
    let data = await queryAsync(clase, funcion, {});

    let options = data.data;

    //crear un input tipo hidden con el valor de value
    let hidden = `<input type="hidden" id="${id}" value='${JSON.stringify(value)}'>`;

    //crear una lista de botones con la etiqueta button y un evento onclick que añade o elimina el id del botón al input hidden
    let botones = `
        <style>
            .botonesCheckList {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .botonCheckList {
                padding: 2px;
                margin: 2px;
                border: 1px solid #ccc;
                cursor: pointer;
                font-size: 0.8rem;
            }
            .botonCheckList.checked {
                background-color: #007bff;
                color: white;
            }
        </style>
        <div class="modalInputGroup">
            <div class="modalLabel">${label}</div>
            ${hidden}
            <div class="botonesCheckList">
    `;

    let botonId = 0;
    for(let key in options){
        let checkedClass = value.includes(key) ? "checked" : "";
        let idBoton = `botonCheckList_${id}_${botonId}`;
        botonId++;
        botones += `
            <div 
                class="botonCheckList ${checkedClass}"
                onclick="checkBoton('${key}', '${options[key]}', '${id}', '${idBoton}')"
                id="${idBoton}"
            >
                ${options[key]}
            </div>
        `;
    }

    botones += `
            </div>
        </div>
    `;

    return botones;
}

function checkBoton(key, value, inputId, botonId){
    console.log({key, value, inputId, botonId});  
    let input = document.getElementById(inputId);
    console.log(input); 
    //si el input no contien "{" se le asigna "{}"
    let json = input.value;
    if(json.includes("{") === false){
        json = {};
    }else{
        //quitar los caracteres de escape
        json = json.replace(/\\/g, "");
        //si el primer y el último caracter son comillas eliminarlos
        if(json[0] === '"'){
            json = json.slice(1);
        }
        if(json[json.length - 1] === '"'){
            json = json.slice(0, -1);
        }
        console.log(json);
        json = JSON.parse(json);
    }

    //añadir key:value al json si no existe, si existe eliminarlo
    if(json[key]){
        delete json[key];
    }else{
        json[key] = value;
    }

    console.log(json);

    //asignar el json al input
    input.value = JSON.stringify(json);

    //añadir o eliminar la clase checked al botón
    let boton = document.getElementById(botonId);
    if(boton.classList.contains("checked")){
        boton.classList.remove("checked");
    } else {
        boton.classList.add("checked");
    }

    //obtener el valor del input
    console.log(input.value);
}

function getInputCoin(id, label, value) {
    let input = `
        <div class="modalInputGroup">
            <div class="modalLabel">${label}</div>
            <input type="text" id="${id}" value="${value}">
        </div>
        <script>
            document.getElementById('${id}').addEventListener('input', function(event) {
                let value = event.target.value;
                // Reemplazar comas por puntos
                value = value.replace(/,/g, '.');
                // Permitir solo números y un punto
                value = value.replace(/[^0-9.]/g, '');
                // Permitir solo un punto
                let parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                event.target.value = value;
            });
        </script>
    `;
    return input;
}

function getInputJson(id, label, value) {
    if (id === "json_date") {
        // Parsear el valor a JSON
        let dates = [];
        //comprobar si value es un json
        if (value !== "" && value !== null) {
            dates = JSON.parse(value);
        }

        // Generar un div con un input tipo date por cada fecha en el array y un botón para generar más inputs tipo date
        let input = `
            <div id="json_date_container" style="display: flex; flex-direction: column; justify-content: center; align-items: center; width: 100%;">
                <label for="${id}" style="margin-bottom: 10px;">${label}</label>
        `;

        if (dates.length === 0) {
            input += `
                <input 
                    type="date"
                    id="${id}_0" 
                    style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white; margin-bottom: 10px;"
                    onchange="saveDatesToLocalStorage()">
            `;
        } else {
            dates.forEach((date, index) => {
                input += `
                    <input 
                        type="date"
                        id="${id}_${index}" 
                        value="${date}"
                        style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white; margin-bottom: 10px;"
                        onchange="saveDatesToLocalStorage()">
                `;
            });
        }

        input += `
                <div id="additional_dates" style="width: 100%;"></div>
                <button 
                    id="add_date_button"
                    onclick="addInputJsonDate()"
                    style="border: none; background-color: #007bff; color: white; padding: 10px; margin-top: 10px; margin-bottom: 10px;">
                    Añadir Fecha
                </button>
                <input type="hidden" id="${id}" value='${JSON.stringify(dates)}'>
            </div>
        `;

        return input;
    }
}

function addInputJsonDate(){
    let div = document.createElement("div");
    div.style.display = "flex";
    div.style.flexDirection = "column";
    div.style.justifyContent = "center";
    div.style.alignItems = "center";
    div.style.width = "100%";

    let input = document.createElement("input");
    input.type = "date";
    input.style.marginBottom = "10px";
    input.style.width = "90%";
    input.style.padding = "10px";
    input.style.border = "1px solid #ccc";
    input.style.borderRadius = "5px";
    input.style.backgroundColor = "white";
    input.onchange = saveDatesToLocalStorage;
    
    div.appendChild(input);

    let additional_dates = document.getElementById("additional_dates");
    additional_dates.appendChild(div);
}

function saveDatesToLocalStorage(){
    let dates = [];
    let inputs = document.querySelectorAll('#json_date_container input[type="date"]');
    inputs.forEach(input => {
        if(input.value) {
            dates.push(input.value);
        }
    });
    localStorage.setItem('json_date', JSON.stringify(dates));
}

async function getDataListApi(id, label, value, apiClass, apiFunction){
    let clase = apiClass;
    let funcion = apiFunction;
    let data = await queryAsync(clase, funcion, {});

    let options = data.data;

    let input = `
        <data-search 
            id="${id}"
            data='${JSON.stringify(options)}'
            default-value="${value}"
            label="${label}"
            style="    
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                width: 100%;
            "
        >
        </data-search>
    `;

    return input;
}

function getDatosFormulario(campos, camposExtra = null){
    let datos = {};
    for(let campo of campos){
        let tipo = campo.tipo;
        let id = campo.id;

        let element = document.getElementById(id);
        if (element) {
            let value = "";
            if(tipo === "dataListApi"){
                value = element.getSelectedKey();
            } else {
                value = element.value;
            }
            datos[id] = value;
        }
    }

    if(camposExtra !== null){
        for(let campo in camposExtra){
            datos[campo] = camposExtra[campo];
        }
    }

    return {data: datos};
}

function getInputText(id, label, value, disabled = false){
    let esDisabled = disabled ? "disabled" : "";

    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="text" id="${id}" value="${value}" ${esDisabled}>
    </div>
    `;
    return input;
}

function getInputNumber(id, label, value){
    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="number" id="${id}" value="${value}" inputmode="numeric">
    </div>
    `;
    return input;
}

function getInputTextArea(id, label, value){
    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <textarea id="${id}" rows="5">${value}</textarea>
    </div>
    `;
    return input;
}

function getInputTime(id, label, value){
    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="time" id="${id}" value="${value}">
    </div>
    `;
    return input;
}

function getInputDate(id, label, value){
    //si value es "" se le asigna la fecha actual
    if(value === ""){
        value = new Date().toISOString().split('T')[0];
    }

    //si label contiene __ y value es "" se le asigna vacío a value
    if(label.includes("__") && (value === "" || value === null)){
        value = "";

        //eliminar __ de label
        label = label.replace("__", "");
    }

    let input = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input type="date" id="${id}" value="${value}">
    </div>
    `;
    return input;
}

function getInputFile(id, label, value){
    let file = '';

    if(value){
        //comprobar si es una imagen
        let extension = value.split('.').pop();
        let esImagen = ['jpg', 'jpeg', 'png', 'gif'].includes(extension);

        if(esImagen){
            file = `
                <div class="imagenPreview" id="imagenPreview">
                    <img src="${value}" alt="Imagen">
                </div>
            `;
        }else{
            file = `
                <div class="imagenPreview" id="imagenPreview">
                    <a href="${value}" download>Descargar Archivo</a>
                </div>
            `;
        }
    }

    let input = `
    <div class="modalInputGroup">
        ${file}
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
        //comprobar si es una imagen
        let extension = file.name.split('.').pop();
        let esImagen = ['jpg', 'jpeg', 'png', 'gif'].includes(extension);

        if(esImagen && preview !== null){
            preview.innerHTML = `<img src="${reader.result}" alt="Imagen">`;
        }
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}

function getDataList(id, label, options, value, onchange = "") {
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

    if(onchange !== ""){
        onchange = `onchange="${onchange}"`;
    }

    let dataList = `
    <div class="modalInputGroup">
        <div class="modalLabel">
            ${label}
        </div>
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

function getSimpleDataList(id, label, options, value, onchange = "") {
    // options = [value1, value2, value3]
    // value = value1

    // Verificar si el valor existe en las opciones, si no existe value = "";
    let valueExists = options.includes(value);
    let valueName = valueExists ? value : "";

    if(onchange !== ""){
        onchange = `onchange="${onchange}()"`;
    }

    let dataList = `
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <input list="${id}_list" id="${id}" value="${valueName}" ${onchange}>
        <datalist id="${id}_list">
            ${options}
        </datalist>
    </div>
    `;
    return dataList;
}

function getBoton(texto, funcion, id){
    //si id no es un número, se le asigna ""
    let boton = "";
    if(isNaN(id)){
        boton = `
            <div class="modalBoton" onclick="${funcion}('${id}')">${texto}</div>
        `;
    }else{
        boton = `
            <div class="modalBoton" onclick="${funcion}(${id})">${texto}</div>
        `;
    }
    
    return boton;
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
        select += `<option value="${key}"`;
        if(key === value){
            select += ` selected`;
        }
        select += `>${options[key]}</option>`;
    }

    //si value es "" se le asigna el valor de la primera opción
    if(value === ""){
        select = `
        <div class="modalInputGroup">
            <div class="modalLabel">${label}</div>
            <select id="${id}">
        `;
        let contar = 0;
        for(let key in options){
            select += `<option value="${key}"`;
            if(contar === 0){
                select += ` selected`;
            }
            select += `>${options[key]}</option>`;
            contar++;
        }   
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
function chatPartes(conversacion, enviado, aprobado, idMaquina, idParte, telefono_whatsapp){
    //si envidado es SI enviado es checked
    enviado = enviado === "SI" ? "checked" : "";

    //si aprobado es PENDIENTE aprobado es "" si no es checked
    aprobado = aprobado === "PENDIENTE" ? "" : "checked";

    let globos = "";
    for(let c of conversacion){
        //decodificar url code
        c.mensaje = decodeURIComponent(c.mensaje.replace(/\+/g, ' '));
        c.mensaje = c.mensaje.replace(/\n/g, '<br>');

        let clase = c.tipo === "enviado" ? "Enviado" : "Contestado";
        globos += `
            <div class="globoContainerChat globoContainerChat${clase}">
                <div class="globoChat globoChat${clase}">
                    <div class="globoMensaje globoMensaje${clase}">
                        <div>${c.mensaje}</div>
                    </div>
                    <div class="globoFecha globoFecha${clase}">
                        ${c.fecha}
                    </div>
                </div>    
            </div>
        `;
    }

    let chat = `
        <div class="chatContainer">
            <div class="chatGlobos">
                ${globos}
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

function getPhoneLink(id, label, value){
    let phoneLink = "";
    //si el value es "" no mostrar el link
    if(value === ""){
        phoneLink = `
        <div class="modalInputGroup">
            <div class="modalLabel">${label}</div>
            <div >Sin Asignar</div><br>
        </div>
        `;
        return phoneLink;
    }

    phoneLink = `
    <style>
        .modalInputGroup a {
            color: #007bff;
            text-decoration: none;
            margin-left: 10px;
            border: 1px solid #007bff;
            padding: 5px;
        }
    </style>
    <div class="modalInputGroup">
        <div class="modalLabel">${label}</div>
        <a id="${id}" href="tel:${value}" target="_blank">LLamar: ${value}</a><br>
        <a id="${id}" href="https://wa.me/${value}" target="_blank">WhatsApp ${label}</a>
    </div>
    `;

    return phoneLink;
}

function desabilitar(id){
    let elemento = document.getElementById(id);
    elemento.disabled = true;
}