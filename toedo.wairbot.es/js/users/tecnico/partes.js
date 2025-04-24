///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getPartesTecnico(nPagina = 1) {
    localStorage.setItem("location", "vendidos");

    //obtener los datos de los agentes
    let clase = 'Partes';
    let funcion = 'getPartesByTecnicoId';
    let datos = {
        nPagina: nPagina,
    };

    let data = await queryAsync(clase, funcion, datos);

    console.log(data);

    data = Object.values(data.data);

    //OBTENER CARDS CON LOS DATOS
    let cards = cardsPartesTecnico(data);

    let paginaActual = null;
    let totalPaginas = null;

    let titulo = "Partes Asignados";
    let funcionCrear = null;
    let funcionAvanzar = null;
    let funcionRetroceder = null;

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);
    
    let administrador = document.getElementById("tecnico");
    administrador.innerHTML = grid;
}

function cardsPartesTecnico(data) {
    let cards = "";
    for (let card of data) {
        console.log(card);
        let funcionApertura = "openParteTecnico";

        let cliente = card.cliente;
        let nombreCliente = cliente.nombre;

        let info1 = card.tipo;
        let info2 = card.fecha;
        let info3 = "Maquina: " + card.maquina.marca + " " + card.maquina.modelo;
        let info4 = "Cliente: " + nombreCliente;
        let info5 = card.estado;
        let id = card.id;

        let imagen = null;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

async function openParteTecnico(id) {
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

    //obtener los datos del maquina asociado
    clase = 'Maquinas';
    funcion = 'getById';
    datos = {
        id: parte.id_maquina
    };

    let maquina = await queryAsync(clase, funcion, datos);
    maquina = maquina.data;

    console.log(maquina);

    let cliente_id = maquina[0].id_cliente;

    //obtener los datos del cliente asociado
    clase = 'Usuarios';
    funcion = 'getById';
    datos = {
        id: cliente_id,
        rol: 'CLIENTE'
    };

    let cliente = await queryAsync(clase, funcion, datos);

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

    //campos del parte
    //getSelect(id, label, options, value)  INSTALACION, REPARACION, REVISION
    let tipo = getSelect(id = "tipo", label = "Tipo", options = ["", "INSTALACION", "REPARACION", "REVISION"], value = parte.tipo);
    let fecha = getInputDate(id = "fecha", label = "Fecha", value = parte.fecha);
    let notas = getInputText(id = "notas", label = "Notas", value = parte.notas);
    console.log(parte.estado);
    let estado = getSelect(id = "estado", label = "Estado", options = ["ABIERTO", "CERRADO"], value = parte.estado);

    let campos = [
        tipo,
        fecha,
        notas,
        estado,
    ];

    let form = createForm(campos, "guardarParteTecnico");

    cliente =cliente.data[0];
    maquina = maquina[0];

    //grid con ficha del motro asociado no editable
    let fichaCliente = `
        <div class="ficha">
            <div class="fichaTitulo">Cliente</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${cliente.nombre || ''}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${cliente.direccion || ''}</div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> ${cliente.email || ''}</div>
                <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> ${cliente.telefono_contacto || ''}</div>
                <div class="fichaCampo"><strong>Movil:&nbsp;</strong> ${cliente.telefono_whatsapp || ''}</div>
            </div>
        </div>
    `;

    let fichaMaquina = `
        <div class="ficha">
            <div class="fichaTitulo">Maquina</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Marca:&nbsp;</strong> ${maquina.marca || ''}</div>
                <div class="fichaCampo"><strong>Modelo:&nbsp;</strong> ${maquina.modelo || ''}</div>
                <div class="fichaCampo"><strong>Codigo:&nbsp;</strong> ${maquina.codigo || ''}</div>
                <div class="fichaCampo"><strong>Fecha de instalación:&nbsp;</strong> ${maquina.fecha_instalacion || ''}</div>
                <div class="fichaCampo"><strong>Observaciones:&nbsp;</strong> ${maquina.observaciones || ''}</div>
            </div>
        </div>        
    `;

    let fichasContainer = `
        <div class="fichasContainer">
            ${fichaCliente}
            ${fichaMaquina}
        </div>
    `;

    let cardsTareas = "";
    for (let card of tareas) {
        let inputs = [
            {tipo: "text", id: "descripcion", label: "Descripción", value: card.descripcion},
            {tipo: "select", id: "tipo", label: "Tipo", value: card.tipo, options: ["", "REPARACION", "REVISION", "SUSTITUCION"]},
            {tipo: "text", id: "referencia", label: "Referencia", value: card.referencia}
        ];

        let InputsForm = createInputFormCardEditable(inputs, card.id);

        let cardTarea = createCardEditable("form", null, null, InputsForm, card.id, "eliminarTarea", "guardarTareaTecnico");

        cardsTareas += cardTarea;
    }

    let gridTareas = createGridCardsEditables("Tareas", cardsTareas, "crearTareaTecnico");

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
    let contenido = form + fichasContainer + gridTareas + gridArchivos + gridImagenes + gridVideos;
    let funcionGuardar = "guardarParteTecnico";
    let usuario = 'tecnico';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

async function guardarParteTecnico(id = 0) {
    let tipo = document.getElementById("tipo").value;
    console.log(tipo);
    if(tipo === "0"){
        tipo = "INSTALACION";
    }

    let fecha = document.getElementById("fecha").value;
    let notas = document.getElementById("notas").value;

    let estado = document.getElementById("estado").value;
    if(estado === "0"){
        estado = "ABIERTO";
    }

    let id_tecnico = JSON.parse(localStorage.getItem("usuario")).id;

    //obtener id_maquina
    //obtener todos los elementos de la clase modalTitulo
    let modalTitulo = document.getElementsByClassName('modalTitulo');
    let id_maquina = modalTitulo[0].id;

    let datos = {
        data: {
            tipo: tipo,
            fecha: fecha,
            notas: notas,
            estado: estado,
            id_tecnico: id_tecnico,
            id_maquina: id_maquina
        }
    };

    if (id !== 0) {
        datos.id = id;

        //eliminar id_maquina de datos
        delete datos.data.id_maquina;
    }

    let clase = 'Partes';
    let funcion = 'update';

    if (id === 0) {
        funcion = 'create';
    }

    let data = await queryAsync(clase, funcion, datos);

    destruirModal();

    getPartesTecnico();
}

async function guardarTareaTecnico(id = 0){
    let descripcion = document.getElementById("descripcion" + "_" + id).value;
    let tipo = document.getElementById("tipo" + "_" + id).value;
    let referencia = document.getElementById("referencia" + "_" + id).value;

    //obtener id_parte
    //obtener todos los elementos de la clase modalTitulo
    let modalTitulo = document.getElementsByClassName('modalTitulo');
    console.log(modalTitulo);
    let id_parte = modalTitulo[0].id;

    let datos = {
        data: {
            descripcion: descripcion,
            tipo: tipo,
            referencia: referencia,
            id_parte: id_parte
        }
    };

    if (id !== 0) {
        datos.id = id;
    }

    let clase = 'Tareas';
    let funcion = 'update';

    if (id === 0) {
        funcion = 'create';
    }

    let data = await queryAsync(clase, funcion, datos);

    if(id === 0){
        //destruir el modal
        destruirModal();
        //volver a cargar las tareas
        openParteTecnico(id_parte);

        //navegar dentro del modalBodyContenido hasta el medio
        let modalBodyContenido = document.querySelector('.modalBodyContenido');
        //intentar modalBodyContenido.scrollTop = modalBodyContenido.scrollHeight / 2; hasta que .modalBodyContenido exista
        let intervalo = setInterval(() => {
            modalBodyContenido = document.querySelector('.modalBodyContenido');
            if(modalBodyContenido){
                modalBodyContenido.scrollTop = modalBodyContenido.scrollHeight / 2;
                clearInterval(intervalo);
            }
        }, 100);
    }
}

//crearTarea
function crearTareaTecnico(){
    let inputs = [
        {tipo: "text", id: "descripcion", label: "Descripción", value: ""},
        {tipo: "select", id: "tipo", label: "Tipo", value: "", options: ["", "REPARACION", "REVISION", "SUSTITUCION"]},
        {tipo: "text", id: "referencia", label: "Referencia", value: ""}
    ];

    let InputsForm = createInputFormCardEditable(inputs, 0);

    let card = createCardEditable("form", null, null, InputsForm, 0, "eliminarTarea", "guardarTareaTecnico");

    //gridCardsEditables_${titulo}
    let grid = document.getElementById("gridCardsEditables_Tareas");
    grid.innerHTML = card + grid.innerHTML;
}

async function subirDocumento(tipo){
    let documentoParaSubir = document.getElementById("documentoParaSubir").files[0];

    let nombre = documentoParaSubir.name;
    let base64 = await toBase64(documentoParaSubir);
    base64 = base64.split("base64,")[1];

    let modalTitulo = document.getElementsByClassName('modalTitulo');
    let id_parte = modalTitulo[0].id;

    let datos = {
        data: {
            id_parte: id_parte,
            tipo: tipo,
            documento: {
                nombre: nombre,
                base64: base64
            }
        }
    };

    let clase = 'Documentos';
    let funcion = 'create';
    let data = await queryAsync(clase, funcion, datos);

    //destruir el modal de aviso
    destruirModalAviso();

    //destruir el modal
    destruirModal();

    //volver a cargar el modal
    openParteTecnico(id_parte);
}