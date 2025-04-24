const camposSolicitudes = [
    {
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "text",
        id: "n_socio",
        label: "Nº Socio Sugerido",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {
            "PREINSCRITO": "Preinscrito",
            "INSCRITO": "Inscrito",
            "BAJA": "Baja",
        },
        defaultValue: "PREINSCRITO",
    },{
        tipo: "text",
        id: "nombre_comercial",
        label: "Nombre Comercial",
    },{
        tipo: "text",
        id: "razon_social",
        label: "Razón Social",
    },{
        tipo: "text",
        id: "cif",
        label: "CIF",
    },{
        tipo: "text",
        id: "titular",
        label: "Titular",
    },{
        tipo: "text",
        id: "dni",
        label: "DNI",
    },{
        tipo: "text",
        id: "denominacion_actividad",
        label: "Denominación Actividad",
    },{
        tipo: "text",
        id: "iae",
        label: "IAE",
    },{
        tipo: "text",
        id: "antiguedad",
        label: "Antigüedad",
    },{
        tipo: "text",
        id: "n_trabajadores_autonomos",
        label: "Nº Trabajadores Autónomos",
    },{
        tipo: "text",
        id: "n_trabajadores_cuenta_ajena",
        label: "Nº Trabajadores Cuenta Ajena",
    },{
        tipo: "text",
        id: "direccion",
        label: "Dirección",
    },{
        tipo: "text",
        id: "numero_direccion",
        label: "Número Dirección",
    },{
        tipo: "text",
        id: "poblacion",
        label: "Población",
    },{
        tipo: "text",
        id: "codigo_postal",
        label: "Código Postal",
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
    },{
        tipo: "text",
        id: "fax",
        label: "Fax",
    },{
        tipo: "text",
        id: "movil",
        label: "Móvil",
    },{
        tipo: "text",
        id: "nombre_banco",
        label: "Nombre Banco",
    },{
        tipo: "text",
        id: "localidad_banco",
        label: "Localidad Banco",
    },{
        tipo: "text",
        id: "titular_banco",
        label: "Titular Banco",
    },{
        tipo: "text",
        id: "iban",
        label: "IBAN",
    },{
        tipo: "file",
        id: "documento",
        label: "Documento de Inscripción",
    }
];

const funcionGuardarSolicitudes = "guardarSolicitud";
const rolSolicitudes = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getSolicitudes(nPagina = 1) {
    console.log("getSolicitudes");
    let location = "solicitudes";
    let clase = "Usuarios";//php
    let funcionLeer = "listarPreinscritos";//php
    let funcionAbrir = "openSolicitud";//js
    let funcionCrear = "crearSolicitud";//js
    let rol = rolSolicitudes;
    let titulo = "Solicitudes";
    let hayPaginacion = true;
    let camposCard = {
        info1: "razon_social",
        info2: "nombre_comercial",
        info3: "email",
        info4: "titular",
        info5: "",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openSolicitud(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'Usuarios';
    let funcion = 'obtenerPorId';
    let datos = {
        rol: 'SOCIO',
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;
    console.log(data);

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarSolicitud",
        id: idOriginal
    }

    let campos = crearCamposDeFormulario(camposSolicitudes, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Solicitud";
    let contenido = form;
    let funcionGuardar = funcionGuardarSolicitudes;
    let usuario = rolSolicitudes;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //obtenerNumeroSocioMasAlto
    obtenerNumeroSocioMasAlto();
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarSolicitud(id) {
    let titulo = "Eliminar Solicitud";
    let mensaje = "¿Estás seguro de que quieres eliminar esta Solicitud?";
    let funcionAceptar = "eliminarSolicitud";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarSolicitud(id) {
    let clase = 'Usuarios';
    let funcion = 'eliminar';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getSolicitudes();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Solicitud Eliminada";
        let mensaje = "La solicitud ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar la solicitud pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';    
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearSolicitud() {   
    let campos = crearCamposDeFormulario(camposSolicitudes);
    let form = createForm(campos, "");

    let titulo = "Crear Solicitud";
    let contenido = form;
    let funcionGuardar = funcionGuardarSolicitudes;
    let usuario = rolSolicitudes;

    createModal(titulo, contenido, funcionGuardar, usuario)

    obtenerNumeroSocioMasAlto();
}

async function guardarSolicitud(id = 0){
    let datosExtra = {
        rol: 'SOCIO'
    };
    
    let datos = await getDatosFormulario(camposSolicitudes, datosExtra);

    let clase = 'Usuarios';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'editar';
    }else{
        funcion = 'preinscripcion';
    }

    await queryAsync(clase, funcion, datos);

    getSolicitudes();
    destruirModal();
}

///////////////////////////////////////////////// FUNCIONES AUXILIARES /////////////////////////////////////////////////
async function obtenerNumeroSocioMasAlto(){
    let clase2 = 'Usuarios';
    let funcion2 = 'obtenerNumeroSocioMasAlto';
    let datos2 = {};
    let data2 = await queryAsync(clase2, funcion2, datos2);
    data2 = data2.data.n_socio;
    console.log(data2);

    let letras = "";
    let numeros = 0;

    //si data2 está vacio o es null data2 = "0"
    if(data2 === "" || data2 === null){
        data2 = "0";
    }

    //comprobar que data2 es un string numerico o un string con letras y números
    if(/^\d+$/.test(data2) || /^[a-zA-Z0-9]+$/.test(data2)){
        //si data2 es un string numerico
        if(/^\d+$/.test(data2)){
            numeros = parseInt(data2);
        }else{
            //si data2 es un string con letras y números
            let array = data2.match(/[a-zA-Z]+|[0-9]+/g);
            letras = array[0];
            numeros = parseInt(array[1]);
        }
    }

    //incrementar el número
    numeros++;

    let n_socio = letras + numeros;
    document.getElementById('n_socio').value = n_socio;
}
