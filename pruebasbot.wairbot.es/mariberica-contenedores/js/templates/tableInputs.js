async function getTableInput(campo, value, tabla, id) {
    console.log({campo, value, tabla, id});
    let tipo = campo.tipo;
    let columna = campo.id;
    let options = campo.options;

    //comprobar si existe campo.onchange y enviar el valor si existe y si no existe enviar ""
    let onchange = campo.onchange || "";

    let inputId = tabla + "::" + columna + "::" + id;

    let input = "";

    //getInputTextArea, getInputFile
    if (tipo === "text") {
        //comprobar si exite campo.disabled y enviar el valor si existe y si no existe enviar false
        let disabled = campo.disabled || false;
        input = getInputTextTable(inputId, value, disabled);
    } else if (tipo === "textarea") {
        input = getInputTextAreaTable(inputId, value);
    } else if (tipo === "file") {
        input = getInputFileTable(value);
    } else if (tipo === "select") {
        input = getSelectTable(inputId, options, value, onchange);
    } else if (tipo === "phone_link") {
        input = getPhoneLinkTable(inputId, value);
    } else if (tipo === "date") {
        input = getInputDateTable(inputId, value);
    } else if (tipo === "number") {
        input = getInputNumberTable(inputId, value);
    } else if (tipo === "time") {
        input = getInputTimeTable(inputId, value);
    } else if (tipo === "dataListApi") {
        //sepsarar options por ::
        let optionsArray = options.split("::");
        let apiClass = optionsArray[0];
        let apiFunction = optionsArray[1];

        input = await getDataListApiTable(inputId, columna, value, apiClass, apiFunction);

    } else if (tipo === "dataListCompleto") {
        let label = "";
        let optionsArray = options.split("::");
        let nombre_select = optionsArray[0];
        let tabla_select = optionsArray[1];

        let id_input_relacionado = "";
        let onchangeFunction = "";

        if (optionsArray.length === 4) {
            id_input_relacionado = optionsArray[2];
            onchangeFunction = optionsArray[3];
        }

        input = await getDataListCompletoTable(
            inputId,
            columna,
            label,
            value,
            tabla_select,
            nombre_select,
            id_input_relacionado,
            onchangeFunction = ""
        )
    } else if (tipo === "json") {
        input = getInputJsonTable(inputId, value);
    } else if (tipo === "coin") {
        input = getInputCoinTable(inputId, value);
    } else if (tipo === "botonesCheckListApi") {
        let optionsArray = campo.options.split("::");
        let apiClass = optionsArray[0];
        let apiFunction = optionsArray[1];
        input = await getBotonesCheckListApiTable(id, value, apiClass, apiFunction, columna);
    } else if (tipo === "html") {
        input = value;
    }

    return input;
}

async function getBotonesCheckListApiTable(id, value, apiClass, apiFunction, columna) {
    console.log({ id, value, apiClass, apiFunction, columna });
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
            ${hidden}
            <div class="botonesCheckList">
    `;
    let botonId = 0;
    for (let key in options) {
        let checkedClass = value.includes(key) ? "checked" : "";
        let idBoton = `botonCheckList_${id}_${botonId}`;
        botonId++;
        botones += `
            <div
                class="botonCheckList ${checkedClass}"
                onclick="checkBotonListTable('${key}', '${options[key]}', '${id}', '${idBoton}', '${apiClass}', '${apiFunction}', '${columna}')"
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

async function checkBotonListTable(key, value, inputId, botonId, apiClass, apiFunction, columna) {
    console.log({key, value, inputId, botonId, apiClass, apiFunction, columna});
    let input = document.getElementById(inputId);
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

    console.log(input.value);

    //añadir o eliminar la clase checked al botón
    let boton = document.getElementById(botonId);
    if(boton.classList.contains("checked")){
        boton.classList.remove("checked");
    } else {
        boton.classList.add("checked");
    }

    //obtener el valor del input
    console.log(input.value);

    console.log(JSON.parse(input.value), input.id);

    //actualizar el valor del input
    let clase = apiClass;
    let funcion = 'editar';
    let data = {
        id: input.id,
    };

    let datosEditar = {};
    datosEditar[columna] = input.value;

    data['data'] = datosEditar;
    console.log(data);

    await queryAsync(clase, funcion, data);
}

function getInputJsonTable(id, value) {
    //solo es para lectura, no es un input
    let input = `
        <div style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white; margin-bottom: 10px;">
            ${value}
        </div>
    `;
    return input;
}

function getInputCoinTable(id, value) {
    let input = `
        <input
            type="text"
            id="${id}"
            value="${value}"
            oninput="actualizarDatoTabla(this.id, this.value), this.value = this.value.replace(/,/g, '.').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
        >
    `;
    return input;
}

let ultimosDatosDataListCompleto = {};
let dataListColeccion = {};

async function getDataListApiTable(id, columna, value, apiClass, apiFunction) {
    console.log({ id, columna, value, apiClass, apiFunction });

    // Comprobar si la columna ya se encuentra en el objeto dataListColeccion
    let options = {};
    if (!dataListColeccion[columna]) {
        let clase = apiClass;
        let funcion = apiFunction;
        let data = await queryAsync(clase, funcion, {});
        options = data.data;

        console.log({ options });

        ultimosDatosDataListCompleto[columna] = options;
        dataListColeccion[columna] = true;
    } else {
        options = ultimosDatosDataListCompleto[columna];
    }

    // Si options no está vacío, crear el datalist
    let dataList = "";

    if (Object.keys(options).length > 0) {
        dataList = `
            <datalist id="${columna}_list">
        `;

        for (const key in options) {
            dataList += `
                <option value="${key} || ${options[key]}">
            `;
        }

        dataList += `
            </datalist>
        `;
    }

    // Obtener el nombre de ultimosDatosDataListCompleto donde la key sea igual a value (value siempre va a ser el key)
    console.log({ value, ultimosDatosDataListCompleto });
    let nombre = ultimosDatosDataListCompleto[columna][value];

    value = `${value} || ${nombre}`;

    // Si value comienza por 0 mostrar string vacío
    if (value.startsWith("0")) {
        value = "";
    }

    let input = `
        <input
            style="width: 100%; padding: 0px 5px; background-color: transparent; border: none; color: #000;"
            list="${columna}_list"
            id="${id}"
            value="${value}"
            onchange="actualizarDatoTabla(this.id, this.value)"
        />
        ${dataList}
    `;

    return input;
}

async function getDataListCompletoTable(
    id,
    columna,
    label,
    value,
    tabla_select,
    nombre_select,
    id_input_relacionado,
    onchangeFunction = ""
) {
    if (onchangeFunction !== "") {
        onchangeFunction = `onchange="${onchangeFunction}(this.value, this.id)"`;
    }

    if (id_input_relacionado !== "" && id_input_relacionado !== null) {
        if (onchangeFunction !== "") {
            onchangeFunction = `onchange="${onchangeFunction}; cambiarInputRelacionadoDataListCompleto('${id_input_relacionado}', this.value, this.id)"`;
        } else {
            onchangeFunction = `onchange="cambiarInputRelacionadoDataListCompleto('${id_input_relacionado}', this.value, this.id)"`;
        }
    }

    if (value === null) {
        value === "";
    }

    let labelHtml = `<div class="modalLabel">${label}</div>`;
    if (label === "") {
        labelHtml = "";
    }

    let dataList = `
    <div class="modalInputGroup" style="margin-bottom: 0;">
         ${labelHtml}
         <div class="inputConBoton" style="display: flex; width: 100%;">
            <input
                style="width: 90%; padding: 0px 5px; background-color: transparent; border: none; color: #000;"
                list="${columna}_list"
                id="${id}"
                value="${value}"
                ${onchangeFunction}
                onclick="resetMenuDataListCompleto(this.id)"
                oninput="comprobarSiEsCodigoDataListCompleto(this.value, this.id)"
            />
            <button onclick="mostrarModalEditarMenuDataListCompleto('${tabla_select}', '${nombre_select}', '${id}')" style="width: 10%; background-color: white; border: 1px solid #ccc;">
                ✏️
            </button>
         </div>
    `;

    //comprobar si columna_list se encuentra en el array de dataListColeccion
    if (!dataListColeccion.includes(columna)) {
        let menuDataListCompleto = await consultarDataListCompleto(nombre_select);

        let options = menuDataListCompleto;

        dataList += `<datalist id="${columna}_list">`;

        for (let key in options) {
            dataList += `<option value="${options[key]}">`;
        }
    
        dataList += `
            </datalist>
        `;

        dataListColeccion.push(columna);
    }

    dataList += "</div>";

    return dataList;
}

async function actualizarDatoTabla(id, value){
    let idArray = id.split("::");
    let tabla = idArray[0];
    let columna = idArray[1];
    let idRegistro = idArray[2];

    let data = {
        tabla: tabla,
        columna: columna,
        id: idRegistro,
        value: value
    };

    console.log(data);

    let clase = "Utilidades";
    let funcion = "actualizarRegistro";

    await queryAsync(clase, funcion, data);
}

function getInputTextTable(id, value, disabled){
    let esDisabled = "";
    if (disabled) {
        esDisabled = "disabled";
    }

    let input = `
        <input
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            type="text"
            id="${id}"
            value="${value}"
            oninput="actualizarDatoTabla(this.id, this.value)"
            ${esDisabled}
        >
    `;
    return input;
}

function getInputTextAreaTable(id, value){
    let input = `
        <textarea
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            id="${id}"
            oninput="actualizarDatoTabla(this.id, this.value)"
        >${value}</textarea>
    `;
    return input;
}

function getInputFileTable(value){
    let input = `<a href="${value}" download>Descargar Archivo</a>`;

    //si value es null no mostrar nada
    if (value === null || value === "") {
        input = "";
    }

    return input;
}

function getSelectTable(id, options, value, onchange) {
    let additionalOnchange = "";
    if (onchange !== "") {
        additionalOnchange = `, ${onchange}(this.id, this.value)`;
    }

    let select = `
        <select
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            id="${id}"
            onchange="actualizarDatoTabla(this.id, this.value)${additionalOnchange}"
        >`;

    for (const key in options) {
        let selected = "";
        if (options[key] === value) {
            selected = "selected";
        }

        select += `<option value="${key}" ${selected}>${options[key]}</option>`;
    }

    select += "</select>";
    return select;
}

function getPhoneLinkTable(id, value){
    let input = `
        <a
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            href="tel:${value}"
        >${value}</a>
    `;
    return input;
}

function getInputDateTable(id, value){
    let input = `
        <input
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            type="date"
            id="${id}"
            value="${value}"
            oninput="actualizarDatoTabla(this.id, this.value)"
        >
    `;

    return input;
}

function getInputNumberTable(id, value){
    let input = `
        <input
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            type="number"
            id="${id}"
            value="${value}"
            oninput="actualizarDatoTabla(this.id, this.value)"
        >
    `;
    return input;
}

function getInputTimeTable(id, value){
    let input = `
        <input
            title="${value}"
            style="width: 100%; background-color: transparent; border: none; color: #000;"
            type="time"
            id="${id}"
            value="${value}"
            oninput="actualizarDatoTabla(this.id, this.value)"
        >
    `;
    return input;
}

