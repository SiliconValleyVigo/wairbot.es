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
    navCondensed(false);
    localStorage.setItem("location", "stock");

    //obtener los datos de los agentes
    let clase = 'Maquinas';
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

    let titulo = "Alta de Máquinas";
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

        let info1 = card.maquina + "-" + card.marca + "-" + card.modelo;
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
    let clase = 'Maquinas';
    let funcion = 'getById';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data[0];

    //si algún valor es null, ponerlo en blanco
    for (let key in data) {
      if (data[key] === null) {
        data[key] = "";
      }
    }

    let url = window.location.href;

    data.imagen = procesarImagen(url, data.imagen);
    data.foto_2 = procesarImagen(url, data.foto_2);
    data.foto_3 = procesarImagen(url, data.foto_3);
    data.foto_4 = procesarImagen(url, data.foto_4);

    //CREAR MODAL
    let imagen = getInputFile(id = "imagen", label = "Imagen", value=data.imagen);
    let modelo = getInputText(id = "modelo", label = "Modelo", value=data.modelo);
    let codigo = getInputText(id = "codigo", label = "Código", value=data.codigo);
    let n_serie = getInputText((id = "n_serie"), (label = "Número de Serie"), (value = data.n_serie));
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value=data.observaciones);
    let foto_2 = getInputFile(id = "foto_2", label = "Foto 2", value=data.foto_2);
    let foto_3 = getInputFile(id = "foto_3", label = "Foto 3", value=data.foto_3);
    let foto_4 = getInputFile(id = "foto_4", label = "Foto 4", value=data.foto_4);
    let eliminar = getBoton(texto = "Eliminar Stock", funcion = "modalEliminarStock", id = idOriginal);
    let qr = getBoton(texto = "Descargar QR", funcion = "botonDescargarQr", id = data.codigo);

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

    let nombre_selectMarca = 'marca_maquina';
    let idMarca = 'marca';
    let labelMarca = 'Marca';
    let valueMarca = data.marca;
    let tabla_selectMarca = 'maquinas';
    let id_input_relacionadoMarca = '';
    let onchangeFunctionMarca = '';
    let marca = await getDataListCompleto(idMarca, labelMarca, valueMarca, tabla_selectMarca,  nombre_selectMarca, id_input_relacionadoMarca, onchangeFunctionMarca);

    //maquina
    nombre_select = 'tipo_maquina';
    id = 'maquina';
    label = 'Tipo Máquina';
    value = data.maquina;
    tabla_select = 'maquinas';
    id_input_relacionado = '';
    onchangeFunction = '';
    let maquina = await getDataListCompleto(id, label, value, tabla_select,  nombre_select, id_input_relacionado, onchangeFunction);

    let campos = [
        imagen,
        maquina,
        marca,
        modelo,
        codigo,
        n_serie,
        observaciones,
        agente,
        cliente,
        eliminar,
        foto_2,
        foto_3,
        foto_4,
        qr
    ];

    let form = createForm(campos, idOriginal);

    let boton = getBoton(texto = "Crear Cliente", funcion = "crearCliente", id = "");

    let titulo = "Editar Stock";
    let contenido = form + boton;
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
    let clase = 'Maquinas';
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
async function crearStock() {   
    //obtener los inputs
    let imagen = getInputFile(id = "imagen", label = "Imagen", value="");
    let modelo = getInputText(id = "modelo", label = "Modelo", value="");
    let codigo = getInputText(id = "codigo", label = "Código", value="");
    let n_serie = getInputText((id = "n_serie"), (label = "Número de Serie"), (value = ""));
    let observaciones = getInputText(id = "observaciones", label = "Observaciones", value="");
    let foto_2 = getInputFile(id = "foto_2", label = "Foto 2", value="");
    let foto_3 = getInputFile(id = "foto_3", label = "Foto 3", value="");
    let foto_4 = getInputFile(id = "foto_4", label = "Foto 4", value="");

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

    let nombre_selectMarca = 'marca_maquina';
    let idMarca = 'marca';
    let labelMarca = 'Marca';
    let valueMarca = "";
    let tabla_selectMarca = 'maquinas';
    let id_input_relacionadoMarca = '';
    let onchangeFunctionMarca = '';
    let marca = await getDataListCompleto(idMarca, labelMarca, valueMarca, tabla_selectMarca,  nombre_selectMarca, id_input_relacionadoMarca, onchangeFunctionMarca);

    //obtener todos los elementos con la clase modalTitulo
    let modalTitulo = document.getElementsByClassName("modalTitulo");

    //si existe alguno con el innerText "Editar Cliente" se obtiene el id de modalTitulo y este es el valueCliente
    for (let titulo of modalTitulo) {
        console.log(titulo.innerText);
        if (titulo.innerText === "Editar Cliente") {
            valueCliente = titulo.id;
        }
    }

    let cliente = getDataList(id = "id_cliente", label = labelCliente, options = optionsCliente, value = valueCliente);

    //maquina
    nombre_select = 'tipo_maquina';
    id = 'maquina';
    label = 'Tipo Máquina';
    value = "";
    tabla_select = 'maquinas';
    id_input_relacionado = '';
    onchangeFunction = '';
    let maquina = await getDataListCompleto(id, label, value, tabla_select,  nombre_select, id_input_relacionado, onchangeFunction);

    let campos = [
        imagen,
        maquina,
        marca,
        modelo,
        codigo,
        n_serie,
        observaciones,
        foto_2,
        foto_3,
        foto_4,
        agente,
        cliente
    ];

    let form = createForm(campos, "");

    //crear un botón para añadir un cliente (crearCliente())
    let boton = getBoton(texto = "Crear Cliente", funcion = "crearCliente", id = "");

    let titulo = "Crear Stock";
    let contenido = form + boton;
    let funcionGuardar = "guardarStock";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarStock(id = 0){
    let maquina = document.getElementById("maquina").value;
    let marca = document.getElementById("marca").value;
    let modelo = document.getElementById("modelo").value;
    let codigo = document.getElementById("codigo").value;
    let n_serie = document.getElementById("n_serie").value;
    let observaciones = document.getElementById("observaciones").value;

    let id_agente = document.getElementById("id_agente").value;
    if(id_agente === "" || id_agente === null || id_agente === undefined){
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
            maquina: maquina,
            marca: marca,
            modelo: modelo,
            codigo: codigo,
            n_serie: n_serie,
            observaciones: observaciones,
            id_agente: id_agente,
            id_cliente: id_cliente
        }
    };

    //si id no es 0 añadir campo id a datos
    if(id !== 0){
        datos.id = id;
    }

    let imagen = await procesarImagenInput("imagen");
    let foto_2 = await procesarImagenInput("foto_2");
    let foto_3 = await procesarImagenInput("foto_3");
    let foto_4 = await procesarImagenInput("foto_4");
  
    if (imagen !== null) {
      datos.data.imagen = imagen;
    }
    if (foto_2 !== null) {
      datos.data.foto_2 = foto_2;
    }
    if (foto_3 !== null) {
      datos.data.foto_3 = foto_3;
    }
    if (foto_4 !== null) {
      datos.data.foto_4 = foto_4;
    }

    let clase = 'Maquinas';
    let funcion = 'update';

    if(id === 0){
        funcion = 'create';
    }

    let data = await queryAsync(clase, funcion, datos);

    getStock();
    destruirModal();
}