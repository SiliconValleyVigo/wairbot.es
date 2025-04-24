const funcionGuardarRemesas = "guardarRemesa";
const rolRemesas = "ADMINISTRADOR";

///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getRemesas(nPagina = 1) {
    console.log("getRemesas");
    let location = "remesas";
    let clase = "Facturas";//php
    let funcionLeer = "listarRemesas";//php
    let funcionAbrir = "openRemesa";//js
    let funcionCrear = "crearRemesa";//js
    let rol = rolRemesas;
    let titulo = "Remesas de Facturas";
    let hayPaginacion = true;
    let camposCard = {
        info1: "fecha_factura",
        info2: "numero_facturas",
        info3: "conformadas",
        info4: "abonadas",
        info5: "",
        id: "id"
    };
    getGrid(location, clase, funcionLeer, funcionAbrir, funcionCrear, rol, titulo, hayPaginacion, camposCard, nPagina)

    //esperar a que se carguen las cards
    await new Promise(r => setTimeout(r, 1000));

    //en card_info_2 poner "Abonadas: " antes del valor
    let abonadas = document.getElementsByClassName("card_info_4");
    for(let abonada of abonadas){
        console.log(abonada.innerText);
        abonada.innerText = "Abonadas: " + abonada.innerText;
    }

    //en card_info_3 poner "Conformadas: " antes del valor
    let conformadas = document.getElementsByClassName("card_info_3");
    for(let conformada of conformadas){
        conformada.innerText = "Conformadas: " + conformada.innerText;
    }

    //en card_info_4 poner "Número de Facturas: " antes del valor
    let numeroFacturas = document.getElementsByClassName("card_info_2");
    for(let numeroFactura of numeroFacturas){
        numeroFactura.innerText = "Número de Facturas: " + numeroFactura.innerText;
    }
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////
async function openRemesa(id) {
    let idOriginal = id;

    //obtener la fecha de la remesa de ese id
    let card = document.getElementById("card_" + id);
    let fecha = card.getElementsByClassName("card_info_1")[0].innerText;

    // OBTENER DATOS //
    let clase = 'Facturas';
    let funcion = 'obtenerFacturasDeRemesa';
    let datos = {
        fecha_factura: fecha
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);
    data = data.data;
    console.log(data);

    // CREAR MODAL//
    
    //botones eliminar, exportar xml, enviar emails, exportar excel, confomar
    let botonesAcciones = `
        <style>
            .botonesAccionesContainer{
                width: 100%;
                display: flex;
                justify-content: center;
            }
            .botonAccion{
                width: 100%;
                max-width: 300px;
                background-color: #f1f1f1;
                border: 1px solid #ccc;
                color: black;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 12px 2px;
                cursor: pointer;
            }
            .botonAccion:hover{
                background-color: #0a4fff;
                color: white;
            }
        </style>
        <div class="botonesAccionesContainer">
            <button class="botonAccion botonConformar" onclick="modalAvisoConformarRemesa('${fecha}', 'conformar')">✅ Conformar Esta Remesa</button>
        </div>
        <div class="botonesAccionesContainer">
            <button class="botonAccion botonEliminar" onclick="procesarRemesas('${fecha}', 'eliminar')">⛔ Cancelar Esta Remesa</button>
        </div>
        <div class="botonesAccionesContainer">
            <button class="botonAccion exportarXML" onclick="procesarRemesas('${fecha}', 'xml')">💰 Exportar Facturas a XML</button>
        </div>
        <div class="botonesAccionesContainer">
            <button class="botonAccion exportarExcel" onclick="procesarRemesas('${fecha}', 'excel')">🧾 Exportar Facturas a Excel</button>
        </div>
        <div class="botonesAccionesContainer">
            <button class="botonAccion enviarEmails" onclick="procesarRemesas('${fecha}', 'enviar_mail')">📧 Enviar Facturas por Email</button>
        </div>
    `;

    let hayPaginacion = false;
    let tituloFacturas = "Facturas de la Remesa";
    let locationFacturas = "facturas";
    let claseFacturas = "Facturas";
    let funcionCrearFacturas = false;
    let funcionLeerFacturas = "listarFacturas";
    let funcionAbrirFacturas = "openFactura";
    let rolFacturas = rolRemesas;
    let camposCardFacturas = {
        info1: "n_factura",
        info2: "nombre_comercial",
        info3: "fecha_factura",
        info4: "conformada",
        info5: "abonada",
        id: "id"
    };

    let cardsFacturas = "";
    for(let factura of data){
        let info1 = factura["n_factura"];
        let info2 = factura["nombre_comercial"];
        let info3 = factura["fecha_factura"];
        let info4 = factura["conformada"];
        let info5 = factura["abonada"];
        let id = factura["id"];

        cardsFacturas += createCard(funcionAbrirFacturas, false, info1, info2, info3, info4, info5, id);
    }

    let gridFacturas = createGrid(cardsFacturas, hayPaginacion, 0, 0, tituloFacturas, locationFacturas, claseFacturas, funcionCrearFacturas, funcionLeerFacturas, funcionAbrirFacturas, rolFacturas, camposCardFacturas);

    //Obtener el modal
    let titulo = "Editar Remesa";
    let contenido = botonesAcciones + gridFacturas;
    let funcionGuardar = funcionGuardarRemesas;
    let usuario = rolRemesas;

    createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

    //quitar el botón de guardar
    let botones = document.querySelectorAll(".modalBoton");
    //obtener el botón con el inerHTML "Guardar"
    let botonGuardar = Array.from(botones).find(boton => boton.innerHTML === "Guardar");
    botonGuardar.remove();

    //obtener todos los elementos con la clase card_info_1 y si innerText es null poner "Pendiente"
    let info1 = document.getElementsByClassName("card_info_1");
    for(let i = 0; i < info1.length; i++){
        if(info1[i].innerText === "null"){
            info1[i].innerText = "Pendiente";
        }
    }

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
}

async function modalAvisoConformarRemesa(fecha, accion) {
    let titulo = "Conformar Remesa";
    let mensaje = "¿Estás seguro de que quieres conformar esta remesa?";
    let funcionAceptar = "procesarRemesas";
    let arrayParametros = [fecha, accion];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function procesarRemesas(fecha, accion) {
    let clase = 'Facturas';
    let funcion = 'procesarRemesas';
    let datos = {
        fecha_factura: fecha,
        tipo_proceso: accion
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    let accionData = {
        "eliminar": {
            titulo: "Remesa Cancelada",
            mensaje: "La remesa ha sido cancelada correctamente",
            cerrarModal: true,
            descargar: false
        },
        "xml": {
            titulo: "Exportar XML",
            mensaje: "El archivo XML ha sido exportado correctamente",
            cerrarModal: false,
            descargar: "https://pruebasbot.wairbot.es/acitui/api/public/remesa_facturas.xml"
        },
        "excel": {
            titulo: "Exportar Excel",
            mensaje: "El archivo Excel ha sido exportado correctamente",
            cerrarModal: false,
            descargar: "https://pruebasbot.wairbot.es/acitui/api/public/remesa_facturas.xlsx"
        },
        "enviar_mail": {
            titulo: "Enviar Emails",
            mensaje: "Los emails han sido enviados correctamente",
            cerrarModal: false
        },
        "conformar": {
            titulo: "Remesa Conformada",
            mensaje: "La remesa ha sido conformada correctamente",
            cerrarModal: false,
            descargar: false
        }
    };

    let titulo = accionData[accion].titulo;
    let mensaje = accionData[accion].mensaje;
    let cerrarModal = accionData[accion].cerrarModal;

    if(data.status === "success"){
        if(cerrarModal){
            getRemesas();
            destruirModal();

            //quitar el spinner
            let spinnerContainer = document.getElementById("spinnerContainer");
            spinnerContainer.style.display = "none";
        }

        if(accionData[accion].descargar){
            let a = document.createElement('a');
            a.href = accionData[accion].descargar;
            a.target = "_blank";
            a.download = "";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        //crear modal aviso de éxito
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL ELIMINAR /////////////////////////////////////////////////
function modalEliminarRemesa(id) {
    let titulo = "Eliminar Remesa";
    let mensaje = "¿Estás seguro de que quieres eliminar este remesa?";
    let funcionAceptar = "eliminarRemesa";
    let arrayParametros = [id];

    createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarRemesa(id) {
    let clase = 'Usuarios';
    let funcion = 'delete';
    let datos = {
        id: id
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getRemesas();
        destruirModalAviso();
        destruirModal();

        //crear modal aviso de éxito
        let titulo = "Remesa Eliminado";
        let mensaje = "El remesa ha sido eliminado correctamente";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }else{
        destruirModalAviso();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible eliminar el remesa pues tiene elementos asociados";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}

///////////////////////////////////////////////// MODAL CREAR /////////////////////////////////////////////////
function crearRemesa() {   
    let campos = crearCamposDeFormulario([
        {
            tipo: "date",
            id: "fecha_remesa",
            label: "Fecha"
        }
    ]);
    let form = createForm(campos, "");

    //crear botón de generar remesa
    let botonGenerarRemesa = `
        <style>
            .botonGenerarRemesaContainer{
                width: 100%;
                display: flex;
                justify-content: center;
            }

            .botonGenerarRemesa{
                background-color: #0a4fff;
                border: none;
                color: white;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
                cursor: pointer;
            }
        </style>
        <div class="botonGenerarRemesaContainer">
            <button class="botonGenerarRemesa" onclick="generarRemesa()">Generar Remesa</button>
        </div>
    `;

    let titulo = "Crear Remesa";
    let contenido = form + botonGenerarRemesa;
    let funcionGuardar = false;
    let usuario = rolRemesas;

    createModal(titulo, contenido, funcionGuardar, usuario);

    //eliminar boton de guardar
    let botones = document.querySelectorAll(".modalBoton");
    //obtener el botón con el inerHTML "Guardar"
    let botonGuardar = Array.from(botones).find(boton => boton.innerHTML === "Guardar");
    botonGuardar.remove();
}

async function generarRemesa() {
    let fecha = document.getElementById("fecha_remesa").value;

    let dia = fecha.split("-")[2];
    let mes = fecha.split("-")[1];
    let year = fecha.split("-")[0];

    let clase = 'Facturas';
    let funcion = 'crearRemesa';
    let datos = {
        dia: dia,
        mes: mes,
        year: year
    };
    let data = await queryAsync(clase, funcion, datos);
    console.log(data);

    if(data.status === "success"){
        getRemesas();
        destruirModal();

        //hacer un intervalo de 1 segundo hasta para obtener el id de la remesa en base a la fecha en la card
        let intervalo = setInterval(async () => {
            let cards = document.querySelectorAll('.card_info_1');
            for (let card of cards) {
                if (card.textContent.includes(fecha)) {
                    let id = card.parentNode.parentNode.parentNode.id.split("_")[1];

                    clearInterval(intervalo);
        
                    //esperar 500 ms para que se cierre el modal
                    await new Promise(r => setTimeout(r, 500));
        
                    openRemesa(id);
                }
            }
        }, 1000);
    }else{
        destruirModal();

        //crear modal aviso de error
        let titulo = "Error";
        let mensaje = "No es posible generar la remesa";
        let funcionAceptar = "destruirModalAviso";
        let arrayParametros = null;

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
    }
}
