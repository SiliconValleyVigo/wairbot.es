/*
imagen
marca
modelo
codigo
fecha_instalacion //no leer siempre es 0000-00-00 al crear
fecha_proxima_revision//no leer siempre es 0000-00-00 al crear
fecha_ultima_revision //no leer siempre es 0000-00-00 al crear
observaciones
id_agente //siempre es 0 al leer
id_cliente //siempre es 0 al leer
*/

async function getStockAgente(nPagina = 1) {
    localStorage.setItem("location", "stock");

    //obtener los datos de los agentes
    let clase = 'Motores';
    let funcion = 'read';
    let datos = {
        nPagina: nPagina,
        enStock: true
    };

    //comprobar si el input buscador esta vacio
    let buscador = document.getElementById("buscador");
    buscador = buscador.value;
    if (buscador !== "" && buscador !== null && buscador !== undefined) {
        datos['search'] = buscador;
    }
    
    let data = await queryAsync(clase, funcion, datos);

    let paginaActual = data.data.paginaActual;
    let totalPaginas = data.data.paginasTotales;

    data = data.data.data;

    //obtener las cards con los datos
    let cards = cardsStockAgente(data);

    let titulo = "Stock";
    let funcionCrear = "crearStockAgente";
    let funcionAvanzar = "avanzarStockAgente";
    let funcionRetroceder = "retrocedStockAgente";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("agente");
    administrador.innerHTML = grid;

    //eliminar el objeto con la clase botonGrid
    let botonGrid = document.getElementsByClassName("botonGrid");
    for (let boton of botonGrid) {
        boton.remove();
    }
}

function cardsStockAgente(data) {
    console.log(data);
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openStockAgente";

        let imagen = card.imagen;
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

        let info1 = card.marca;
        let info2 = card.modelo;
        let info3 = card.codigo;
        let info4 = card.observaciones;
        let info5 = "";
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

function avanzarStockAgente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getStockAgente(parseInt(paginaActual) + 1);
}

function retrocedStockAgente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getStockAgente(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openStockAgente(id) {
    let idOriginal = id;

    //OBTENER DATOS
    let clase = 'Motores';
    let funcion = 'getById';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data[0];

    let url = window.location.href;

    console.log(data);

    if(typeof data.imagen === 'string'){
        if (!data.imagen.includes("public/")) {
            data.imagen = "imgs/image.png";
        }else{
            data.imagen = url + "api/" + data.imagen;
        }
    }else{
        data.imagen = "imgs/image.png";
    }

    //CREAR MODAL
    let imagen = getInputFile(id = "imagen", label = "Imagen", value=data.imagen);
    let marca = getInputText(id = "marca", label = "Marca", value=data.marca);
    let modelo = getInputText(id = "modelo", label = "Modelo", value=data.modelo);
    let codigo = getInputText(id = "codigo", label = "Nº de Serie", value=data.codigo);
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value=data.observaciones);
    let eliminar = getBoton(texto = "Solicitar Motor", funcion = "solicitarMotorAgente", id = idOriginal);

    let campos = [
        imagen,
        marca,
        modelo,
        codigo,
        observaciones,
        eliminar
    ];

    let form = createForm(campos, idOriginal);

    let titulo = "Editar Stock";
    let contenido = form;
    let funcionGuardar = "destruirModal";
    let usuario = 'agente';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

async function solicitarMotorAgente(id) {
    let clase = 'Solicitudes';
    let funcion = 'create';
    let datos = {
        id_motor: id,
        user_id: JSON.parse(localStorage.getItem('usuario')).id
    };

    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    //modal de avisar que se ha solicitado el motor createModalAviso(titulo, mensaje, funcionAceptar = null, arrayParametros = null)
    let titulo = "Solicitud Realizada";
    let mensaje = "Hemos recibido tu solicitud, en breve nos pondremos en contacto contigo";

    createModalAviso(titulo, mensaje);
    
}