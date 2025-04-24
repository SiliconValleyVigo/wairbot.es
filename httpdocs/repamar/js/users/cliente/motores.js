///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getVendidosCliente(nPagina = 1) {
    localStorage.setItem("location", "vendidos");

    //obtener los datos de los agentes
    let clase = 'Motores';
    let funcion = 'read';
    let datos = {
        nPagina: nPagina,
        enStock: false
    };

    //comprobar si el input buscador esta vacio
    let buscador = document.getElementById("buscador");
    buscador = buscador.value;
    if (buscador !== "" && buscador !== null && buscador !== undefined) {
        datos['search'] = buscador;
    }

    let data = await queryAsync(clase, funcion, datos);

    console.log(data);

    let paginaActual = data.data.paginaActual;
    let totalPaginas = data.data.paginasTotales;

    data = Object.values(data.data.data);

    //OBTENER CARDS CON LOS DATOS
    let cards = cardsVendidosCliente(data);

    let titulo = "Motores";
    let funcionCrear = null;
    let funcionAvanzar = "avanzarVendidosCliente";
    let funcionRetroceder = "retrocenderVendidosCliente";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);
    
    let administrador = document.getElementById("cliente");
    administrador.innerHTML = grid;
}

function cardsVendidosCliente(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openVendidoCliente";

        let imagen = card.imagen;

        //si la imagen no contiene public/ enviar la url/imgs/image.png
        let url = window.location.href;

        if(typeof card.imagen === 'string'){
            if (!card.imagen.includes("public/")) {
                imagen = "imgs/image.png";
            }else{
                imagen = url + "api/" + imagen;
            }
        }else{
            imagen = "imgs/image.png";
        }
        

        let info1 = card.marca + " " + card.modelo;
        let info2 = card.codigo;
        let info3 = card.observaciones;
        let info4 = "";
        let info5 = "";
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

function avanzarVendidosCliente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getVendidos(parseInt(paginaActual) + 1);
}

function retrocenderVendidosCliente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getVendidos(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////

async function openVendidoCliente(id) {
    let motor = await getMotor(id);
    let partes = await getPartes(id);

    if(partes === undefined){
        partes = [];
    }

    let gridPartes = createGridPartesClientes(partes);

    let form = formVendidoMotor(motor);

    let titulo = "Motor Vendido";
    let contenido = form + gridPartes;
    let funcionGuardar = null;
    let usuario = 'cliente';

    createModal(titulo, contenido, funcionGuardar, usuario, id);

    //eliminar el div con la clase modalBoton donde el texto sea Guardar
    let modalBoton = document.querySelectorAll(".modalBoton");
    for (let boton of modalBoton) {
        if (boton.innerText === "Guardar") {
            boton.remove();
        }
    }

    //los inputs del form no deben ser editables
    let inputs = document.querySelectorAll("input");
    for (let input of inputs) {
        input.disabled = true;
    }

    let selects = document.querySelectorAll("select");
    for (let select of selects) {
        select.disabled = true;
    }

    let textareas = document.querySelectorAll("textarea");
    for (let textarea of textareas) {
        textarea.disabled = true;
    }
}

function formVendidoMotor(motor) {
    let data = motor[0];

    let url = window.location.href;

    if(typeof data.imagen === 'string'){
        if (!data.imagen.includes("public/")) {
            data.imagen = "imgs/image.png";
        }else{
            data.imagen = url + "api/" + data.imagen;
        }
    }else{
        data.imagen = "imgs/image.png";
    }

    let imagen = getInputFile(id = "imagen", label = "Imagen", value=data.imagen);
    let marca = getInputText(id = "marca", label = "Marca", value=data.marca);
    let modelo = getInputText(id = "modelo", label = "Modelo", value=data.modelo);
    let codigo = getInputText(id = "codigo", label = "Código", value=data.codigo);
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value=data.observaciones);
    let fecha_instalacion = getInputDate(id = "fecha_instalacion", label = "Fecha Instalación", value=data.fecha_instalacion);
    let fecha_ultima_revision = getInputDate(id = "fecha_ultima_revision", label = "Fecha Ultima Revisión", value=data.fecha_ultima_revision);
    let fecha_proxima_revision = getInputDate(id = "fecha_proxima_revision", label = "Fecha Próxima Revisión", value=data.fecha_proxima_revision);

    let campos = [
        imagen,
        marca,
        modelo,
        codigo,
        observaciones,
        fecha_instalacion,
        fecha_ultima_revision,
        fecha_proxima_revision,
    ];

    let form = createForm(campos, null);

    return form;
}

//######################################################################################################//
///////////////////////////////////////////////// PARTES /////////////////////////////////////////////////
//######################################################################################################//

function createGridPartesClientes(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openParteMotores";

        let info1 = card.tipo;
        let info2 = card.fecha;
        let info3 = card.notas;
        let info4 = "";
        let info5 = "";
        let id = card.id;

        let imagen = null;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    let titulo = "Partes"
    //createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder)
    let grid = createGrid(cards, 1, 1, titulo, null, "", "");
    return grid;
}

async function openParteMotores(id) {
    let idOriginal = id;
    console.log(id);

    //obtener datos del parte
    let clase = 'Partes';
    let funcion = 'getById';
    let datos = {
        id: id
    };

    let parte = await queryAsync(clase, funcion, datos);
    parte = parte.data;

    //obtener los datos del motor asociado
    clase = 'Motores';
    funcion = 'getById';
    datos = {
        id: parte.id_motor
    };

    let motor = await queryAsync(clase, funcion, datos);
    motor = motor.data;

    //obtener los documentos asociados
    clase = 'Documentos';
    funcion = 'getByParteId';
    datos = {
        id_parte: id
    };

    let documentos = await queryAsync(clase, funcion, datos);
    documentos = documentos.data;

    //obtener las taras asociadas
    clase = 'Tareas';
    funcion = 'getByParteId';
    datos = {
        id_parte: id
    };

    let tareas = await queryAsync(clase, funcion, datos);
    tareas = tareas.data;

    console.log({parte, motor, documentos, tareas});

    //campos del parte
    //getSelect(id, label, options, value)  INSTALACION, REPARACION, REVISION
    let tipo = getSelect(id = "tipo", label = "Tipo", options = ["", "INSTALACION", "REPARACION", "REVISION"], value = parte.tipo);
    let fecha = getInputDate(id = "fecha", label = "Fecha", value = parte.fecha);
    let notas = getInputText(id = "notas", label = "Notas", value = parte.notas);
    console.log(parte.estado);
    let estado = getSelect(id = "estado", label = "Estado", options = ["ABIERTO", "CERRADO"], value = parte.estado);

    //datalist Tecnico
    let tecnico = "";
    let optionsTecnico = localStorage.getItem("nombresDeTecnicos");
    optionsTecnico = JSON.parse(optionsTecnico);
    let valueTecnico = parte.id_tecnico;
    tecnico = getDataList(id = "id_tecnico", label = "Técnico", options = optionsTecnico, value = valueTecnico);

    let campos = [
        tipo,
        fecha,
        notas,
        estado,
        tecnico
    ];

    let form = createForm(campos, "guardarParte");

    //grid con ficha del motro asociado no editable
    let cardsMotor = cardsVendidos([motor[0]]);
    let gridMotor = createGrid(cardsMotor, 1, 1, "Motor", "", "", "");

    let cardsTareas = "";
    for (let card of tareas) {
        let inputs = [
            {tipo: "text", id: "descripcion", label: "Descripción", value: card.descripcion},
            {tipo: "select", id: "tipo", label: "Tipo", value: card.tipo, options: ["", "REPARACION", "REVISION", "SUSTITUCION"]},
            {tipo: "text", id: "referencia", label: "Referencia", value: card.referencia}
        ];

        let InputsForm = createInputFormCardEditable(inputs, card.id);

        let cardTarea = createCardEditable("form", null, null, InputsForm, card.id, "eliminarTarea", "guardarTarea");

        cardsTareas += cardTarea;
    }

    let gridTareas = createGridCardsEditables("Tareas", cardsTareas, "crearTarea");

    //grid documentos
    let cardsArchivos = "";
    let cardsImagenes = "";
    let cardsVideos = "";
    for (let card of documentos) {
        let tipo = card.tipo;

        //si el tipo es DOCUMENTO añadir a cardsArchivos
        if (tipo === "DOCUMENTO") {
            let cardArchivo = createCardEditable("archivo", null, card.url, null, card.id, "eliminarDocumento", "guardarDocumento");
            cardsArchivos += cardArchivo;
        }

        //si el tipo es IMAGEN añadir a cardsImagenes
        if (tipo === "IMAGEN") {
            if(card.comentarios === null){
                card.comentarios = "";
            }
            let cardImagen = createCardEditable("imagen", card.url, null, null, card.id, "eliminarDocumento", "guardarDocumento", card.comentarios, "actualizarComentario");
            cardsImagenes += cardImagen;
        }

        //si el tipo es VIDEO añadir a cardsVideos
        if (tipo === "VIDEO") {
            if(card.comentarios === null){
                card.comentarios = "";
            }
            let cardVideo = createCardEditable("video", null, card.url, null, card.id, "eliminarDocumento", "guardarDocumento", card.comentarios, "actualizarComentario");
            cardsVideos += cardVideo;
        }
    }

    let gridArchivos = createGridCardsEditables("Archivos", cardsArchivos, "crearDocumento");
    let gridImagenes = createGridCardsEditables("Imagenes", cardsImagenes, "crearDocumento");
    let gridVideos = createGridCardsEditables("Videos", cardsVideos, "crearDocumento");

    //abrir modal
    let titulo = "Parte";
    let contenido = form + gridMotor + gridTareas + gridArchivos + gridImagenes + gridVideos;
    let funcionGuardar = null;
    let usuario = 'cliente';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //todos los inputs del form deben estar deshabilitados
    let inputs = document.querySelectorAll("input");
    for (let input of inputs) {
        input.disabled = true;
    }

    let selects = document.querySelectorAll("select");
    for (let select of selects) {
        select.disabled = true;
    }

    let textareas = document.querySelectorAll("textarea");
    for (let textarea of textareas) {
        textarea.disabled = true;
    }

    //eliminar los elementos con la clase botonGridEditable
    let botones = document.querySelectorAll(".botonGridEditable");
    for (let boton of botones) {
        boton.remove();
    }

    //eliminar los elementos con la clase cardFooter
    let cardFooters = document.querySelectorAll(".cardFooter");
    for (let cardFooter of cardFooters) {
        cardFooter.remove();
    }

    //eliminar modalBoton con el texto Guardar
    let modalBoton = document.querySelectorAll(".modalBoton");
    for (let boton of modalBoton) {
        if (boton.innerText === "Guardar") {
            boton.remove();
        }
    }
} 