///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAgentes(nPagina = 1) {
    navCondensed(false);
    localStorage.setItem("location", "agentes");
    //OBTENER DATOS
    let clase = 'Usuarios';
    let funcion = 'read';
    let datos = {
        nPagina: nPagina,
        rol: 'AGENTE'
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

    data = data.data.data;
    
    //OBTENER CARDS CON LOS DATOS
    let cards = cardsAgente(data);

    let titulo = "Agentes";
    let funcionCrear = "crearAgente";
    let funcionAvanzar = "avanzarAgentes";
    let funcionRetroceder = "retrocederAgentes";

    let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

    let administrador = document.getElementById("administrador");
    administrador.innerHTML = grid;
}

function cardsAgente(data) {
    let cards = "";
    for (let card of data) {
        let funcionApertura = "openAgente";

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

        let info1 = card.nombre;
        let info2 = card.referencia;
        let info3 = card.telefono_contacto;
        let info4 = card.telefono_whatsapp;
        let info5 = card.email;
        let id = card.id;

        cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
    }

    return cards;
}

function avanzarAgentes() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    let totalPaginas = document.getElementById("totalPaginas").innerText;
    if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) { return; }
    getAgentes(parseInt(paginaActual) + 1);
}

function retrocederAgentes() {
    let paginaActual = document.getElementById("paginaActual").innerText;
    if (parseInt(paginaActual) - 1 <= 0) { return; }
    getAgentes(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openAgente(id) {
    let idOriginal = id;

    //OBTENER DATOS
    let clase = 'Usuarios';
    let funcion = 'getById';
    let datos = {
        rol: 'AGENTE',
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
    let eliminar = getBoton(texto = "Eliminar Agente", funcion = "modalEliminarAgente", id = idOriginal);

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
    
    let form = createForm(campos, idOriginal);

    let titulo = "Editar Agente";
    let contenido = form;
    let funcionGuardar = "guardarAgente";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarAgente(id) {
    let titulo = "Eliminar Agente";
    let mensaje = "¿Estás seguro de que quieres eliminar este agente?";
    let funcionAceptar = "eliminarAgente";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarAgente(id) {
    let clase = 'Usuarios';
    let funcion = 'delete';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);

    if(data.status === "success"){
        getAgentes();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Agente Eliminado";
        let mensaje = "El agente ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el agente pues tiene clientes o maquinas asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearAgente() {   
    //obtener los inputs
    let email = getInputText(id = "email", label = "Email", value="");
    let nombre = getInputText(id = "nombre", label = "Nombre", value="");
    let imagen = getInputFile(id = "imagen", label = "Imagen", value="");
    let referencia = getInputText(id = "referencia", label = "Referencia", value="");
    let telefono_contacto = getInputText(id = "telefono_contacto", label = "Telefono Oficina", value="");
    let telefono_whatsapp = getInputText(id = "telefono_whatsapp", label = "Telefono Móvil", value="");
    let direccion = getInputText(id = "direccion", label = "Dirección", value="");
    let password = getInputText(id = "password", label = "Contraseña", value="");

    let campos = [
        imagen,
        nombre,
        referencia,
        email,
        password,
        telefono_contacto,
        telefono_whatsapp,
        direccion,
    ];
    
    let form = createForm(campos, "");

    let titulo = "Crear Agente";
    let contenido = form;
    let funcionGuardar = "guardarAgente";
    let usuario = 'administrador';

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarAgente(id = 0){
    //obtener los datos
    let email = document.getElementById("email").value;
    let nombre = document.getElementById("nombre").value;
    let referencia = document.getElementById("referencia").value;
    let telefono_contacto = document.getElementById("telefono_contacto").value;
    let telefono_whatsapp = document.getElementById("telefono_whatsapp").value;
    let direccion = document.getElementById("direccion").value;
    let password = document.getElementById("password").value;

    let datos = {
        data: {
            email: email,
            nombre: nombre,
            referencia: referencia,
            telefono_contacto: telefono_contacto,
            telefono_whatsapp: telefono_whatsapp,
            direccion: direccion,
            password: password,
            id_agente: 0,
            rol: 'AGENTE'
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
        let nombresDeAgentes = JSON.parse(localStorage.getItem('nombresDeAgentes'));
        nombresDeAgentes[data.data.id] = nombre;
        localStorage.setItem('nombresDeAgentes', JSON.stringify(nombresDeAgentes));
    }

    getAgentes();
    destruirModal();
}

function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}
