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

async function getStock(nPagina = 1) {
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
    let cards = cardsStock(data);

    let titulo = "Stock";
    let funcionCrear = "crearStock";
    let funcionAvanzar = "avanzarStock";
    let funcionRetroceder = "retrocedStock";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("administrador");
    administrador.innerHTML = grid;
}

function cardsStock(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openStock";

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

function avanzarStock() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getStock(parseInt(paginaActual) + 1);
}

function retrocedStock() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getStock(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openStock(id) {
    let idOriginal = id;

    //OBTENER DATOS
    let clase = 'Motores';
    let funcion = 'getById';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data[0];

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

    //CREAR MODAL
    let imagen = getInputFile(id = "imagen", label = "Imagen", value=data.imagen);
    let marca = getInputText(id = "marca", label = "Marca", value=data.marca);
    let modelo = getInputText(id = "modelo", label = "Modelo", value=data.modelo);
    let codigo = getInputText(id = "codigo", label = "Nº de Serie", value=data.codigo);
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value=data.observaciones);
    let eliminar = getBoton(texto = "Eliminar Stock", funcion = "modalEliminarStock", id = idOriginal);

    //datalist Agente
    let labelAgente = "Agente";
    let optionsAgente = localStorage.getItem("nombresDeAgentes");
    optionsAgente = JSON.parse(optionsAgente);
    let valueAgente = data.id_agente;
    let agente = getDataList(id = "id_agente", label = labelAgente, options = optionsAgente, value = valueAgente);

    //datalist Cliente
    let labelCliente = "Cliente";
    let optionsCliente = localStorage.getItem("nombresDeClientes");
    optionsCliente = JSON.parse(optionsCliente);
    let valueCliente = data.id_cliente;
    let cliente = getDataList(id = "id_cliente", label = labelCliente, options = optionsCliente, value = valueCliente);

    let campos = [
        imagen,
        marca,
        modelo,
        codigo,
        observaciones,
        agente,
        cliente,
        eliminar
    ];

    let form = createForm(campos, idOriginal);

    let titulo = "Editar Stock";
    let contenido = form;
    let funcionGuardar = "guardarStock";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarStock(id) {
    let titulo = "Eliminar Stock";
    let mensaje = "¿Estás seguro de que quieres eliminar este stock?";
    let funcionAceptar = "eliminarStock";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarStock(id) {
    let clase = 'Motores';
    let funcion = 'delete';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if(data.status === "success"){
        getStock();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Stock Eliminado";
        let mensaje = "El stock ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el stock";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearStock() {   
    //obtener los inputs
    let imagen = getInputFile(id = "imagen", label = "Imagen", value="");
    let marca = getInputText(id = "marca", label = "Marca", value="");
    let modelo = getInputText(id = "modelo", label = "Modelo", value="");
    let codigo = getInputText(id = "codigo", label = "Nº de Serie", value="");
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value="");

    //datalist Agente
    let labelAgente = "Agente";
    let optionsAgente = localStorage.getItem("nombresDeAgentes");
    optionsAgente = JSON.parse(optionsAgente);
    let valueAgente = "";
    let agente = getDataList(id = "id_agente", label = labelAgente, options = optionsAgente, value = valueAgente);

    //datalist Cliente
    let labelCliente = "Cliente";
    let optionsCliente = localStorage.getItem("nombresDeClientes");
    optionsCliente = JSON.parse(optionsCliente);
    let valueCliente = "";
    let cliente = getDataList(id = "id_cliente", label = labelCliente, options = optionsCliente, value = valueCliente);

    let campos = [
        imagen,
        marca,
        modelo,
        codigo,
        observaciones,
        agente,
        cliente
    ];

    let form = createForm(campos, "");

    let titulo = "Crear Stock";
    let contenido = form;
    let funcionGuardar = "guardarStock";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarStock(id = 0){
    let marca = document.getElementById("marca").value;
    let modelo = document.getElementById("modelo").value;
    let codigo = document.getElementById("codigo").value;
    let observaciones = document.getElementById("observaciones").value;

    let id_agente = document.getElementById("id_agente").value;
    if(id_agente === ""){
        id_agente = "0";
    }else{
        id_agente = id_agente.split("|")[1].trim();
    }

    let id_cliente = document.getElementById("id_cliente").value;
    if(id_cliente === ""){
        id_cliente = "0";
    }else{
        id_cliente = id_cliente.split("|")[1].trim();
    }

    let datos = {
        data: {
            marca: marca,
            modelo: modelo,
            codigo: codigo,
            observaciones: observaciones,
            id_agente: id_agente,
            id_cliente: id_cliente
        }
    };

    //si id no es 0 añadir campo id a datos
    if(id !== 0){
        datos.id = id;
    }

    let imagen = document.getElementById("imagen").value;

    if(imagen === ""){
        //no se ha subido una nueva imagen
        imagen = null;
    }else{
        //obtener el nombre del archivo y el base64
        let imagenFile = document.getElementById("imagen").files[0];

        let nombreImagen = imagenFile.name
        let base64 = await toBase64(imagenFile);
        //eliminar el prefijo
        base64 = base64.split("base64,")[1];

        imagen = {
            nombre: nombreImagen,
            base64: base64
        };
    }

    if(imagen !== null){
        datos.data.imagen = imagen;
    }

    let clase = 'Motores';
    let funcion = 'update';

    if(id === 0){
        funcion = 'create';
    }

    let data = await queryAsync(clase, funcion, datos);

    getStock();
    destruirModal();
}