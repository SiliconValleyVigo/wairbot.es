/*
n_socio
nombre_comercial
razon_social
cif
forma_pago
iban
importe
concepto
n_factura
fecha_factura
conformada (0, 1)
abonada (0, 1)
pdf
*/
const camposFacturasRectificativas = [
    {
        tipo: "dataList",
        id: "n_socio",
        label: "Socio",
        options: localStorage.getItem('usariosHtml'),
        defaultValue: "",
        onchange: "cargarDatosSocio"
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
        id: "forma_pago",
        label: "Forma de Pago",
    },{
        tipo: "text",
        id: "iban",
        label: "IBAN",
    },{
        tipo: "text",
        id: "importe",
        label: "Importe",
    },{
        tipo: "text",
        id: "concepto",
        label: "Concepto",
    },{
        tipo: "text",
        id: "n_factura",
        label: "Nº Factura",
    },{
        tipo: "date",
        id: "fecha_factura",
        label: "Fecha Factura",
    },{
        tipo: "select",
        id: "conformada",
        label: "Conformada",
        options: {
            0: "Pendiente de Conformar",
            1: "Ya Conformada",
        },
        defaultValue: 0,
    },{
        tipo: "select",
        id: "abonada",
        label: "Abonada",
        options: {
            0: "Pendiente de Abonar",
            1: "Ya Abonada",
        },
        defaultValue: 0,
    },{
        tipo: "file",
        id: "pdf",
        label: "PDF",
        defaultValue: "",
    }
];

const funcionGuardarFacturasRectificativas = "guardarFacturasRectificativas";
const rolFacturasRectificativas = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getFacturasRectificativas(nPagina = 1) {
    console.log("getFacturasRectificativas");
    let location = "facturasRectificativas";
    let clase = "FacturasRectificativas";//php
    let funcionLeer = "listarFacturas";//php
    let funcionAbrir = "openFacturasRectificativas";//js
    let funcionCrear = "crearFacturasRectificativas";//js
    let rol = rolFacturasRectificativas;
    let titulo = "Facturas Rectificativas";
    let hayPaginacion = true;
    let camposCard = {
        info1: "n_factura",
        info2: "nombre_comercial",
        info3: "fecha_factura",
        info4: "conformada",
        info5: "abonada",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)

    //esperar a que se cree el grid
    await new Promise(r => setTimeout(r, 500));

    //si info4 es 0 o "" poner pendiente de conformar y si es 1 poner conformada
    let info4 = document.getElementsByClassName('card_info_4');
    for(let i = 0; i < info4.length; i++){
        if(info4[i].innerText === "0" || info4[i].innerText === ""){
            info4[i].innerText = "Pendiente de Conformar";
        }else{
            info4[i].innerText = "Conformada";
        }
    }

    //si info5 es 0 o "" poner pendiente de abonar y si es 1 poner abonada
    let info5 = document.getElementsByClassName('card_info_5');
    for(let i = 0; i < info5.length; i++){
        if(info5[i].innerText === "0" || info5[i].innerText === ""){
            info5[i].innerText = "Pendiente de Abonar";
        }else{
            info5[i].innerText = "Abonada";
        }
    }

    let info1 = document.getElementsByClassName('card_info_1');
    for(let i = 0; i < info1.length; i++){
        if(info1[i].innerText === ""){
            info1[i].innerText = "Pendiente";
        }
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openFacturasRectificativas(id) {
    let idOriginal = id;

    // OBTENER DATOS //
    let clase = 'FacturasRectificativas';
    let funcion = 'obtenerFacturaPorId';
    let datos = {
        rol: 'SOCIO',
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    data = data.data;

    data.pdf = "https://pruebasbot.wairbot.es/acitui/api/public/" + data.pdf;

    // CREAR MODAL//

    //Crear el formulario
    let botonEliminar = {
        texto: "Eliminar",
        funcion: "modalEliminarFacturasRectificativas",
        id: idOriginal
    }

    let campos = crearCamposDeFormulario(camposFacturasRectificativas, data, botonEliminar);
    let form = createForm(campos, idOriginal);

    //Obtener el modal
    let titulo = "Editar Factura";
    let contenido = form;
    let funcionGuardar = funcionGuardarFacturasRectificativas;
    let usuario = rolFacturasRectificativas;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarFacturasRectificativas(id) {
    let titulo = "Eliminar Factura";
    let mensaje = "¿Estás seguro de que quieres eliminar esta factura?";
    let funcionAceptar = "eliminarFacturasRectificativas";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarFacturasRectificativas(id) {
    let clase = 'FacturasRectificativas';
    let funcion = 'eliminarFactura';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getFacturasRectificativas();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Factura Eliminado";
        let mensaje = "La factura ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar la factura pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }

    //quitar spinner
    let spinnerContainer = document.getElementById('spinnerContainer');
    spinnerContainer.style.display = 'none';
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
async function crearFacturasRectificativas() {   
    let clase = 'Usuarios';
    let funcion = 'obtenerNombresUsuarios';
    let datos = {
        rol: 'SOCIO'
    };
    let data = await queryAsync(clase, funcion, datos);
    let usuarios = data.data;

    console.log(usuarios);

    //poner en formato:  <option value="key">value</option>
    let usuariosHtml = "";
    for(let key in usuarios){
        usuariosHtml += `<option value="${key}">${usuarios[key]}</option>`;
    }

    localStorage.setItem('usariosHtml', usuariosHtml);

    let campos = crearCamposDeFormulario(camposFacturasRectificativas);
    let form = createForm(campos, "");

    let titulo = "Crear Factura";
    let contenido = form;
    let funcionGuardar = funcionGuardarFacturasRectificativas;
    let usuario = rolFacturasRectificativas;

    createModal(titulo, contenido, funcionGuardar, usuario)

    //eliminar los padres de los ids n_factura, conformada, abonada, pdf
    document.getElementById('n_factura').parentNode.remove();
    document.getElementById('conformada').parentNode.remove();
    document.getElementById('abonada').parentNode.remove();
    document.getElementById('pdf').parentNode.remove();
}

async function guardarFacturasRectificativas(id = 0){
    let datosExtra = {};

    let campos = camposFacturasRectificativas;
    //eliminar los campos que no estén en el formulario

    //si no existe el elemento conformada
    if(document.getElementById('conformada') === null){
        console.log("no existe conformada");
        campos = campos.filter(c => c.id !== 'n_factura' && c.id !== 'conformada' && c.id !== 'abonada' && c.id !== 'pdf');
    }

    console.log(campos);
    
    let datos = await getDatosFormulario(campos, datosExtra);

    let clase = 'FacturasRectificativas';

    let funcion = '';
    if(id !== 0){
        datos.id = id;
        funcion = 'editarFactura';
    }else{
        funcion = 'crearFactura';
    }

    await queryAsync(clase, funcion, datos);

    getFacturasRectificativas();
    destruirModal();
}