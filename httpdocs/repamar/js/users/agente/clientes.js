async function getClientesAgente(nPagina = 1) {
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
    let cards = cardsClienteAgente(data);

    let titulo = "Clientes";
    let funcionCrear = "crearClienteAgente";
    let funcionAvanzar = "avanzarClientesAgente";
    let funcionRetroceder = "retrocedClientesAgente";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("agente");
    administrador.innerHTML = grid;
}

function cardsClienteAgente(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openClienteAgente";

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

        let info1 = card.nombre;
        let info2 = card.referencia;
        let info3 = card.telefono_contacto;
        let info4 = card.email;
        let info5 = "";
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

function avanzarClientesAgente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getClientesAgente(parseInt(paginaActual) + 1);
}

function retrocedClientesAgente() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getClientesAgente(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openClienteAgente(id) {
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
    let eliminar = getBoton(texto = "Eliminar Cliente", funcion = "modalEliminarCliente", id = idOriginal); //funcion en administrador/clientes.js

    let campos = [
        imagen,
        nombre,
        referencia,
        email,
        password,
        telefono_contacto,
        telefono_whatsapp,
        direccion,
        eliminar
    ];

    let gridMotores = await getMotoresCliente(idOriginal); //funcion en administrador/clientes.js
    
    let form = createForm(campos, idOriginal);

    let titulo = "Editar Cliente";
    let contenido = form + gridMotores;
    let funcionGuardar = "guardarClienteAgente";
    let usuario = 'agente';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
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

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearClienteAgente() {   
    //obtener los inputs
    let email = getInputText(id = "email", label = "Email", value="");
    let nombre = getInputText(id = "nombre", label = "Nombre", value="");
    let imagen = getInputFile(id = "imagen", label = "Imagen", value="");
    let referencia = getInputText(id = "referencia", label = "Referencia", value="");
    let telefono_contacto = getInputText(id = "telefono_contacto", label = "Telefono Oficina", value="");
    let direccion = getInputText(id = "direccion", label = "Dirección", value="");
    let password = getInputText(id = "password", label = "Contraseña", value="");

    let campos = [
        imagen,
        nombre,
        referencia,
        email,
        password,
        telefono_contacto,
        direccion,
    ];
    
    let form = createForm(campos, "");

    let titulo = "Crear Cliente";
    let contenido = form;
    let funcionGuardar = "guardarClienteAgente";
    let usuario = 'agente';

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarClienteAgente(id = 0){
    //obtener los datos
    let email = document.getElementById("email").value;
    let nombre = document.getElementById("nombre").value;
    let referencia = document.getElementById("referencia").value;
    let telefono_contacto = document.getElementById("telefono_contacto").value;
    let telefono_whatsapp = document.getElementById("telefono_whatsapp").value;
    let direccion = document.getElementById("direccion").value;
    let password = document.getElementById("password").value;

    let usuario = JSON.parse(localStorage.getItem("usuario"));
    let id_agente = usuario.id;

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

    getClientesAgente();
    destruirModal();
}