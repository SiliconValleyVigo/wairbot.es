async function getClientes(nPagina = 1) {
    localStorage.setItem("location", "clientes");
    //obtener los datos de los agentes
    let clase = 'Usuarios';
    let funcion = 'read';
    let datos = {
        nPagina: nPagina,
        rol: 'CLIENTE'
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
    let cards = cardsCliente(data);

    let titulo = "Clientes";
    let funcionCrear = "crearCliente";
    let funcionAvanzar = "avanzarClientes";
    let funcionRetroceder = "retrocedClientes";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("administrador");
    administrador.innerHTML = grid;
}

function cardsCliente(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openCliente";

        let imagen = card.imagen;
        console.log(imagen);
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

        let agente = "";

        //comprobar si usuarios existe en card
        if (card.usuarios) {
            //comprobar si id_agente existe en card.usuario
            if (card.usuarios.id_agente) {
                agente = card.usuarios.id_agente.nombre;
            }
        }

        let info1 = card.nombre;
        let info2 = card.referencia;
        let info3 = card.telefono_contacto;
        let info4 = card.email;
        let info5 = agente;
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

function avanzarClientes() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getClientes(parseInt(paginaActual) + 1);
}

function retrocedClientes() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getClientes(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openCliente(id) {
    let idOriginal = id;

    //OBTENER DATOS
    let clase = 'Usuarios';
    let funcion = 'getById';
    let datos = {
        rol: 'CLIENTE',
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
    let email = getInputText(id = "email", label = "Email", value=data.email);
    let nombre = getInputText(id = "nombre", label = "Nombre", value=data.nombre);
    let imagen = getInputFile(id = "imagen", label = "Imagen", value=data.imagen);
    let referencia = getInputText(id = "referencia", label = "Referencia", value=data.referencia);
    let telefono_contacto = getInputText(id = "telefono_contacto", label = "Telefono Oficina", value=data.telefono_contacto);
    let telefono_whatsapp = getInputText(id = "telefono_whatsapp", label = "Telefono Móvil", value=data.telefono_whatsapp);
    let direccion = getInputText(id = "direccion", label = "Dirección", value=data.direccion);
    let password = getInputText(id = "password", label = "Contraseña", value=data.password);
    let eliminar = getBoton(texto = "Eliminar Cliente", funcion = "modalEliminarCliente", id = idOriginal);

    //datalist Agente
    let labelAgente = "Agente";
    let optionsAgente = localStorage.getItem("nombresDeAgentes");
    optionsAgente = JSON.parse(optionsAgente);
    let valueAgente = data.id_agente;
    let agente = getDataList(id = "id_agente", label = labelAgente, options = optionsAgente, value = valueAgente);

    let campos = [
        imagen,
        nombre,
        referencia,
        email,
        password,
        telefono_contacto,
        telefono_whatsapp,
        direccion,
        agente,
        eliminar
    ];

    let gridMotores = await getMotoresCliente(idOriginal);
    
    let form = createForm(campos, idOriginal);

    let titulo = "Editar Cliente";
    let contenido = form + gridMotores;
    let funcionGuardar = "guardarCliente";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

//obtener los motores de un cliente y mostrarlos en una grid
async function getMotoresCliente(idCliente) {
    //obtener los datos de los motores
    let clase = 'Motores';
    let funcion = 'getByClienteIdRequest';
    let datos = {
        cliente_id: idCliente
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    //obtener las cards con los datos
    let cards = cardsMotores(data);

    let titulo = "Motores";
    let funcionCrear = "crearMotor";
    let funcionAvanzar = "avanzarMotores";
    let funcionRetroceder = "retrocedMotores";

    let grid = createGrid(cards, 1, 1, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    return grid;
}

function cardsMotores(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openVendido";

        let imagen = card.imagen;
        let url = window.location.href;

        if (!card.imagen.includes("public/")) {
            imagen = "imgs/image.png";
        }else{
            imagen = url + "api/" + imagen;
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

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarCliente(id) {
    let titulo = "Eliminar Cliente";
    let mensaje = "¿Estás seguro de que quieres eliminar este cliente?";
    let funcionAceptar = "eliminarCliente";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarCliente(id) {
    let clase = 'Usuarios';
    let funcion = 'delete';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if(data.status === "success"){
        getClientes();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Cliente Eliminado";
        let mensaje = "El cliente ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el cliente pues tiene Partes asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearCliente() {   
    //obtener los inputs
    let email = getInputText(id = "email", label = "Email", value="");
    let nombre = getInputText(id = "nombre", label = "Nombre", value="");
    let imagen = getInputFile(id = "imagen", label = "Imagen", value="");
    let referencia = getInputText(id = "referencia", label = "Referencia", value="");
    let telefono_contacto = getInputText(id = "telefono_contacto", label = "Telefono Oficina", value="");
    let telefono_whatsapp = getInputText(id = "telefono_whatsapp", label = "Telefono Móvil", value="");
    let direccion = getInputText(id = "direccion", label = "Dirección", value="");
    let password = getInputText(id = "password", label = "Contraseña", value="");

    //datalist Agente
    let labelAgente = "Agente";
    let optionsAgente = localStorage.getItem("nombresDeAgentes");
    optionsAgente = JSON.parse(optionsAgente);
    let valueAgente = "";
    let agente = getDataList(id = "id_agente", label = labelAgente, options = optionsAgente, value = valueAgente);

    let campos = [
        imagen,
        nombre,
        referencia,
        email,
        password,
        telefono_contacto,
        telefono_whatsapp,
        direccion,
        agente
    ];
    
    let form = createForm(campos, "");

    let titulo = "Crear Cliente";
    let contenido = form;
    let funcionGuardar = "guardarCliente";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarCliente(id = 0){
    //obtener los datos
    let email = document.getElementById("email").value;
    let nombre = document.getElementById("nombre").value;
    let referencia = document.getElementById("referencia").value;
    let telefono_contacto = document.getElementById("telefono_contacto").value;
    let telefono_whatsapp = document.getElementById("telefono_whatsapp").value;
    let direccion = document.getElementById("direccion").value;
    let password = document.getElementById("password").value;

    let id_agente = document.getElementById("id_agente").value;
    id_agente = id_agente.split("|")[1].trim();

    let datos = {
        data: {
            email: email,
            nombre: nombre,
            referencia: referencia,
            telefono_contacto: telefono_contacto,
            telefono_whatsapp: telefono_whatsapp,
            direccion: direccion,
            password: password,
            id_agente: id_agente,
            rol: 'CLIENTE'
        }
    };

    //si id no es 0 añadir campo id a datos
    if(id !== 0){
        datos.id = id;
    }

    let imagen = document.getElementById("imagen").value;

    //comprobar si se ha subido una nueva imagen
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
    
    let clase = 'Usuarios';
    let funcion = 'update';

    if(id === 0){
        funcion = 'create';
    }

    let data = await queryAsync(clase, funcion, datos);

    if(id === 0){
        console.log(data.data.id);
        //añadir a localStorage nombresDeAgentes id:nombre
        let nombresDeClientes = JSON.parse(localStorage.getItem('nombresDeClientes'));
        nombresDeClientes[data.data.id] = nombre;
        localStorage.setItem('nombresDeClientes', JSON.stringify(nombresDeClientes));
    }

    getClientes();
    destruirModal();
}