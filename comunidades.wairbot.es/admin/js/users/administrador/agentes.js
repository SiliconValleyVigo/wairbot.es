//email, password, limite
const camposAgentes = [
    {
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "text",
        id: "password",
        label: "Contraseña",
    },{
        tipo: "text",
        id: "limite",
        label: "Limite de comunidades",
    }
];

const funcionGuardarAgente = "guardarAgente";
const rolAgente = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getAgentes(nPagina = 1) {
    let location = "agentes";
    let clase = "Usuarios";//php
    let funcionLeer = "read";//php
    let funcionAbrir = "openAgente";//js
    let funcionCrear = "crearAgente";//js
    let rol = rolAgente;
    let titulo = "Agentes";
    let hayPaginacion = true;
    let camposCard = {
        info1: "email",
        info2: "password",
        info3: "limite",
        info4: "",
        info5: "",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openAgente(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'Usuarios';
    let funcion = 'getById';
    let datos = {
        rol: 'AGENTE',
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data;
    console.log(data);

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarAgente",
        id: idOriginal
    }

    let campos = crearCamposDeFormulario(camposAgentes, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Agente";
    let contenido = form;
    let funcionGuardar = funcionGuardarAgente;
    let usuario = rolAgente;

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
    console.log(data);

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
        let mensaje = "No es posible eliminar el agente pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    await new Promise((resolve) => setTimeout(resolve, 1000));

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearAgente() {   
    let campos = crearCamposDeFormulario(camposAgentes);
    let form = createForm(campos, "");

    let titulo = "Crear Agente";
    let contenido = form;
    let funcionGuardar = funcionGuardarAgente;
    let usuario = rolAgente;

    createModal(titulo, contenido, funcionGuardar, usuario)
}

async function guardarAgente(id = 0){
    let datosExtra = {
        rol: 'AGENTE'
    };
    
    let datos = getDatosFormulario(camposAgentes, datosExtra);

    let clase = 'Usuarios';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'update';
    }else{
        funcion = 'create';
    }

    await queryAsync(clase, funcion, datos);

    getAgentes();
    destruirModal();
}
