/*
MODAL EDICIÓN DATALIST
- Listar el menú en inputs editables
- Botón para añadir otro elemento al menú
- Botón para guardar el menú en un json
*/

async function getDataListCompleto(
    id,
    label,
    value,
    tabla_select,
    nombre_select,
    id_input_relacionado,
    onchangeFunction = ""
) {
    let menuDataListCompleto = await consultarDataListCompleto(nombre_select);

    let options = menuDataListCompleto;

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

    if (value === null || value === "") {
        //compobar si hay un elemento por defecto en el menú que contenga __
        for (let key in options) {
            if (options[key].includes("__")) {
                value = options[key].split("::")[0];
                break;
            }
        }
    }

    let labelHtml = `<div class="modalLabel">${label}</div>`;
    if (label === "") {
        labelHtml = "";
    }

    let dataList = `
    <div class="modalInputGroup">
         ${labelHtml}
         <div class="inputConBoton" style="display: flex; width: 100%;">
            <input
                style="width: 90%;"
                list="${id}_list"
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
        
        <datalist id="${id}_list">
    `;
    for (let key in options) {
        dataList += `<option value="${options[key]}">`;
    }

    dataList += `
        </datalist>
    </div>
    `;

    return dataList;
}

async function consultarDataListCompleto(nombre_select) {
    let clase = 'Selects';
    let funcion = 'getForNombreSelect';
    let datos = {
        nombre: nombre_select
    };

    let data = await queryAsync(clase, funcion, datos);

    let menuDataListCompleto = data.data.menu;

    //eliminar elementos en blanco
    menuDataListCompleto = menuDataListCompleto.filter(element => element !== "");

    return menuDataListCompleto;
}

function addInputMenuDataListCompleto(nombre_select) {
    // Crear el elemento input
    let input = document.createElement('input');
    input.className = 'inputMenuDataListCompletoModificar';
    input.oninput = function () {
        editarMenuDataListCompleto(nombre_select);
    };

    // Crear el elemento div
    let div = document.createElement('div');
    div.className = 'modalInputGroup';
    div.appendChild(input);

    // Obtener el botón y verificar que existe
    let boton = document.getElementsByClassName('modalBoton')
    //buscar el boton de añadir
    let botonAdd = Array.from(boton).find(boton => boton.textContent === 'Añadir');

    botonAdd.before(div);
}

function comprobarSiEsCodigoDataListCompleto(value, id) {
    //comprobar si value está compuesto por números y si son 3
    if (value.length === 3 && /^\d+$/.test(value)) {
        let input = document.getElementById(id);
        let options = input.list.options;
        let option = Array.from(options).find(option => option.value.includes(value));
        if (option) {
            input.value = option.value;
            input.blur();
        }
    }
}

async function mostrarModalEditarMenuDataListCompleto(tabla_select, nombre_select, id) {
    let menuDataListCompleto = await consultarDataListCompleto(nombre_select);
    let form = ``;
    for (let key in menuDataListCompleto) {
        let valueInput = menuDataListCompleto[key];

        form += `
            <div class="modalInputGroup">
                <input 
                    value="${valueInput}"
                    class="inputMenuDataListCompletoModificar"
                    oninput="editarMenuDataListCompleto('${nombre_select}', '${id}')"
                />
            </div>
        `;
    }

    //boton para añadir otro elemento al menú
    form += `
        <div class="modalBoton" onclick="addInputMenuDataListCompleto('${nombre_select}')">Añadir</div>
    `;

    let titulo = "Editar Menú " + tabla_select;
    let contenido = form;
    let funcionGuardar = false;

    //obtener usuario de localStorage
    let user = localStorage.getItem('usuario');
    let rol = JSON.parse(user).rol;

    let usuario =  rol;
    let idOriginal = 'modal';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
    //createModal(titulo, contenido, funcionGuardar, usuario, id = null, funcionCerrar = null, parametrosCerrar = null, idContainer = "")
}

async function editarMenuDataListCompleto($nombre_select, $id) {
    let inputs = document.getElementsByClassName('inputMenuDataListCompletoModificar');
    let menu = [];
    for (let input of inputs) {
        menu.push(input.value);
    }

    let data = {
        nombre: $nombre_select,
        menu: menu
    };

    let clase = 'Selects';
    let funcion = 'update';
    let datos = {
        data: data
    };

    let response = await queryAsync(clase, funcion, datos);

    //cambiar los datalist de los inputs que tengan el mismo nombre
    let inputsRelacionados = document.querySelectorAll(`datalist[id="${$id}_list"]`);

    for (let input of inputsRelacionados) {
        let dataList = await consultarDataListCompleto($nombre_select);
        let options = dataList;

        input.innerHTML = '';
        for (let key in options) {
            input.innerHTML += `<option value="${options[key]}">`;
        }
    }
}

async function resetMenuDataListCompleto(id) {
    let input = document.getElementById(id);
    input.value = "";
}

function cambiarInputRelacionadoDataListCompleto(id_input_relacionado, value, id) {
    if (!value.includes("::")) {
        return;
    }

    let valueArray = value.split("::");
    let valueInputRelacionado = valueArray[1];

    let inputRelacionado = document.getElementById(id_input_relacionado);
    inputRelacionado.value = valueInputRelacionado;

    let input = document.getElementById(id);
    input.value = valueArray[0];
}

/*
estados_parte partes
urgencia_parte partes
tipo_maquina maquinas
tipo_tarea tareas

//dataList de estados_parte
let nombre_select = 'estados_parte';
let id = 'estado_parte';
let label = 'Estado Parte';
let value = '';
let tabla_select = 'partes';
let id_input_relacionado = '';
let onchangeFunction = '';
let estados_parte = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

//dataList de urgencia_parte
nombre_select = 'urgencia_parte';
id = 'urgencia_parte';
label = 'Urgencia Parte';
value = '';
tabla_select = 'partes';
id_input_relacionado = '';
onchangeFunction = '';
let urgencia_parte = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

//dataList de tipo_maquina
nombre_select = 'tipo_maquina';
id = 'tipo_maquina';
label = 'Tipo Máquina';
value = '';
tabla_select = 'maquinas';
id_input_relacionado = '';
onchangeFunction = '';
let tipo_maquina = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

//dataList de tipo_tarea
nombre_select = 'tipo_tarea';
id = 'tipo_tarea';
label = 'Tipo Tarea';
value = '';
tabla_select = 'tareas';
id_input_relacionado = '';
onchangeFunction = '';
let tipo_tarea = await getDataListCompleto(id, label, value, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);
*/