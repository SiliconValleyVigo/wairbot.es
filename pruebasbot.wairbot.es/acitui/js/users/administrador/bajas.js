//email, password, limite
const camposBajas = [
    {
        tipo: "text",
        id: "n_socio",
        label: "Nº Socio",
    },{
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "select",
        id: "estado",
        label: "Estado",
        options: {
            "PREINSCRITO": "Preinscrito",
            "INSCRITO": "Inscrito",
            "BAJA": "Baja",
        },
        defaultValue: "INSCRITO",
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
        tipo: "select",
        id: "solicitud_baja",
        label: "Solicitud de Baja",
        options: {
            "0": "No solicitada",
            "1": "Solicitada",
        },
    },{
        tipo: "text",
        id: "motivo_baja",
        label: "Motivo de Baja",
    }
];

const funcionGuardarBajas = "guardarBaja";
const rolBajas = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getBajas(nPagina = 1) {
    console.log("getBajas");
    let location = "bajas";
    let clase = "Usuarios";//php
    let funcionLeer = "listarBajas";//php
    let funcionAbrir = "openBaja";//js
    let funcionCrear = false;
    let rol = rolBajas;
    let titulo = "Bajas";
    let hayPaginacion = true;
    let camposCard = {
        info1: "nombre_comercial",
        info2: "n_socio",
        info3: "email",
        info4: "estado",
        info5: "motivo_baja",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openBaja(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'Usuarios';
    let funcion = 'obtenerPorId';
    let datos = {
        rol: 'SOCIO',
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data;
    console.log(data);

    // CREAR MODAL//
    let campos = crearCamposDeFormulario(camposBajas, data);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Baja";
    let contenido = form;
    let funcionGuardar = funcionGuardarBajas;
    let usuario = rolBajas;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

async function guardarBaja(id = 0){
    let datosExtra = {
        rol: 'SOCIO'
    };
    
    let datos = await getDatosFormulario(camposBajas, datosExtra);

    let clase = 'Usuarios';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'editar';
    }else{
        funcion = 'preinscripcion';
    }

    await queryAsync(clase, funcion, datos);

    getBajas();
    destruirModal();
}
