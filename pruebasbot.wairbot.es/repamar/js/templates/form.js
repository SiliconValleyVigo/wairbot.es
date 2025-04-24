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

function getBoton(texto, funcion, id){
    let boton = `
    <div class="modalBoton" onclick="${funcion}(${id})">${texto}</div>
    `;
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