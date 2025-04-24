/*
<div class="card">
   <div class="mainCard">
       <div class="cardFoto">
           <img src="./imgs/favicon.png" alt="Foto de card">
       </div>
       <div class="cardInfo">
           <div class="card_info_1">Primera linea de texto tiene que ser larga para que se puedar cortar</div>
           <div class="card_info_2">Segunda linea de texto tiene que ser larga para que se puedar cortar</div>
           <div class="card_info_3">Tercera linea de texto tiene que ser larga para que se puedar cortar</div>
           <div class="card_info_4">Cuarta linea de texto tiene que ser larga para que se puedar cortar</div>
           <div class="card_info_5">Quinta linea de texto tiene que ser larga para que se puedar cortar</div>
       </div>
   </div>
</div>
*/

function createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id, oculto = "") {
    //comprobar si la imagen no es null o false o undefined o ""
    if (!imagen) {
        cardFoto = "";
    }else{
        cardFoto = `<div class="cardFoto">
                        <img src="${imagen}" alt="Foto de card">
                    </div>`;
    }

    let card = `
    <div class="card" id="card_${id}" onclick="${funcionApertura}(${id})">
        <div class="mainCard">
            ${cardFoto}
            <div class="cardInfo">
                <div class="card_info_1">${info1}</div>
                <div class="card_info_2">${info2}</div>
                <div class="card_info_3">${info3}</div>
                <div class="card_info_4">${info4}</div>
                <div class="card_info_5">${info5}</div>
                <div class="card_info_oculto_${id}" style="display: none;">${oculto}</div>
            </div>
        </div>
    </div>
    `;

    return card;
}

function createInputFormCardEditable(campos, id){
    //campos: [{tipo: "text", id: "texto", label: "Texto", value: "Valor", options: null}, {tipo: "select", id: "select", label: "Select", value: "Valor", options: ["Opcion 1", "Opcion 2", "Opcion 3"]}]
    let cardEditableFormInputs = "";
    for(let c of campos){
        let input = "";
        if(c.tipo === "text"){
            input = `<input type="text" name="${c.id}" id="${c.id}_${id}" value="${c.value}">`;
        }else if(c.tipo === "select"){
            let options = "";
            for(let o of c.options){
                let selected = "";
                if(o === c.value){
                    selected = "selected";
                }
                options += `<option value="${o}" ${selected}>${o}</option>`;
            }
            input = `<select name="${c.id}" id="${c.id}_${id}">${options}</select>`;
        }

        cardEditableFormInputs += `
        <div class="cardEditableFormInput">
            <label for="${c.id}">${c.label}</label>
            ${input}
        </div>
        `;
    }

    return cardEditableFormInputs;
}

function createCardEditable(tipo, imagen, archivo, form, id, funcionEliminar, funcionGuardar, comentario = "", funcionActualizar = "actualizarComentario"){
    let cardEditable = "";
    if(tipo === "imagen"){
        let url = window.location.href;
        url = url + "api/" + imagen;

        cardEditable = `
        <div class="cardEditable cardEditableImagen" id="cardEditableDocumento_${id}">
            <div class="cardEditableImagenContainer">
                <img src="${url}" alt="Agentes">
            </div>
            <textarea name="comentario" id="comentario_${id}" cols="30" rows="10" oninput="${funcionActualizar}(this.id)">${comentario}</textarea>
            <div class="cardFooter">
                <div class="cardFooterEliminar" onclick="${funcionEliminar}(${id})">Eliminar</div>
            </div>
        </div>
        `;
    }
    if(tipo === "video"){
        let url = window.location.href;
        url = url + "api/" + archivo;

        cardEditable = `
        <div class="cardEditable cardEditableVideo" id="cardEditableDocumento_${id}">
            <div class="cardEditableVideoContainer">
                <video src="${url}" controls></video>
            </div>
            <textarea name="comentario" id="comentario_${id}" cols="30" rows="10" oninput="${funcionActualizar}(this.id)">${comentario}</textarea>
            <div class="cardFooter">
                <div class="cardFooterEliminar" onclick="${funcionEliminar}(${id})">Eliminar</div>
            </div>
        </div>
        `;
    }
    if(tipo === "archivo"){
        let url = window.location.href;
        url = url + "api/" + archivo;

        let nombre = archivo.split("/");
        nombre = nombre[nombre.length - 1];

        cardEditable = `
        <div class="cardEditable cardEditableArchivo" id="cardEditableDocumento_${id}">
            <div class="cardEditableArchivoContainer">
                <div class="carEditableEnlaceDescarga">
                    <a href="${url}" download>📥</a>
                </div>
                <div class="cardEditableNombreArchivo">${nombre}</div>
            </div>
            <div class="cardFooter">
                <div class="cardFooterEliminar" onclick="${funcionEliminar}(${id})">Eliminar</div>
            </div>
        </div>
        `;
    }
    if(tipo === "form"){
        cardEditable = `
        <div class="cardEditable cardEditableForm" id="cardEditableForm_${id}">
            <div class="cardEditableFormContainer">
                <div class="cardEditableFormInputs">
                    ${form}
                </div>
            </div>
            <div class="cardFooter">
                <div class="cardFooterEliminar" onclick="${funcionEliminar}(${id})">Eliminar</div>
                <div class="cardFooterGuardar" onclick="${funcionGuardar}(${id})">Guardar</div>
            </div>
        </div>
        `;
    }

    return cardEditable;
}

function createGridCardsEditables(titulo, cards, funcionCrear){
    let botonCrear = "";
    if(funcionCrear){
        botonCrear = `<div class="botonGridEditable" onclick="${funcionCrear}('${titulo}')">+</div>`;
    }

    let grid = `
    <div class="gridCardsEditables" id="gridCardsEditables_${titulo}">
        <div class="headerGridEditable">
            ${botonCrear}
            <div class="tituloGridEditable">${titulo}</div>
        </div>
        <div class="gridEditable" id="grid_${titulo}">
            ${cards}
        </div>
    </div>
    `;
    return grid;
}



