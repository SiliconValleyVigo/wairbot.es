///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getVendidos(nPagina = 1) {
  navCondensed(false);
  localStorage.setItem("location", "vendidos");

  //obtener los datos de los agentes
  let clase = "Maquinas";
  let funcion = "read";
  let datos = {
    nPagina: nPagina,
    enStock: false,
  };

  //comprobar si el input buscador esta vacio
  let buscador = document.getElementById("buscador");
  buscador = buscador.value;
  if (buscador !== "" && buscador !== null && buscador !== undefined) {
    datos["search"] = buscador;
  }

  console.log(datos);

  let data = await queryAsync(clase, funcion, datos);

  let paginaActual = data.data.paginaActual;
  let totalPaginas = data.data.paginasTotales;

  data = Object.values(data.data.data);

  //OBTENER CARDS CON LOS DATOS
  let cards = cardsVendidos(data);

  let titulo = "Maquinas de Clientes";
  let funcionCrear = null;
  let funcionAvanzar = "avanzarVendidos";
  let funcionRetroceder = "retrocenderVendidos";

  let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

  let administrador = document.getElementById("administrador");
  administrador.innerHTML = grid;

  //a todas los ids card_info_5 añadir la estiqueta <strong>Cliente:</strong>
  let cardInfo5 = document.querySelectorAll(".card_info_5");
  for (let card of cardInfo5) {
    card.innerHTML = "<strong>" + card.innerHTML + "</strong>";
  }

  //si card_info_4 es "null" ponerlo en blanco
  let cardInfo4 = document.querySelectorAll(".card_info_4");
  for (let card of cardInfo4) {
    if (card.innerHTML === "null") {
      card.innerHTML = "";
    }
  }
}

function cardsVendidos(data) {
  let cards = "";
  for (let card of data) {
    let funcionApertura = "openVendido";

    let imagen = card.imagen;

    //si la imagen no contiene public/ enviar la url/imgs/image.png
    let url = window.location.href;

    if (typeof card.imagen === "string") {
      if (!card.imagen.includes("public/")) {
        imagen = "imgs/image.png";
      } else {
        imagen = url + "api/" + imagen;
      }
    } else {
      imagen = "imgs/image.png";
    }

    let agente = "";
    let cliente = "";

    //comprobar si usuarios existe en card
    if (card.usuarios) {
      //comprobar si id_agente existe en card.usuario
      if (card.usuarios.id_agente) {
        agente = card.usuarios.id_agente.nombre;
      }

      //comprobar si id_cliente existe en card.usuario
      if (card.usuarios.id_cliente) {
        cliente = card.usuarios.id_cliente.nombre;
      }
    }

    let info1 = card.maquina + "-" + card.marca + "-" + card.modelo;
    let info2 = card.codigo;
    let info3 = card.observaciones;
    let info4 = agente;
    let info5 = cliente;
    let id = card.id;

    cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);

    const intervalo = setInterval(() => {
      let cardEstilos = document.getElementById("card_" + card.id);
      if (cardEstilos) {
        clearInterval(intervalo);
        //si card.visto es igual a PENDIENTE, cambiar el color de la card a rojo palido, selecionar la card por el id "card_${card.id}"
        if (card.visto === "PENDIENTE") {
          let cardEstilos = document.getElementById("card_" + card.id);
          cardEstilos.style.backgroundColor = "rgba(255, 0, 0, 0.2)";
        }

        //si card.estado es igual a CONTESTADO, cambiar el color de la card a verde palido, selecionar la card por el id "card_${card.id}"
        if (card.visto === "CONTESTADO" || card.visto === "APROBADO") {
          let cardEstilos = document.getElementById("card_" + card.id);
          cardEstilos.style.backgroundColor = "rgba(0, 255, 0, 0.2)";
        }
      }
    }, 100);
  }

  return cards;
}

function avanzarVendidos() {
  let paginaActual = document.getElementById("paginaActual").innerText;
  let totalPaginas = document.getElementById("totalPaginas").innerText;
  if (parseInt(paginaActual) + 1 > parseInt(totalPaginas)) {
    return;
  }
  getVendidos(parseInt(paginaActual) + 1);
}

function retrocenderVendidos() {
  let paginaActual = document.getElementById("paginaActual").innerText;
  if (parseInt(paginaActual) - 1 <= 0) {
    return;
  }
  getVendidos(parseInt(paginaActual) - 1);
}

///////////////////////////////////////////////// MODAL VER /////////////////////////////////////////////////

async function openVendido(id) {
  let maquina = await getMaquina(id);
  console.log(maquina[0].usuarios.id_cliente);
  let partes = await getPartes(id);

  if (partes === undefined) {
    partes = [];
  }

  let gridPartes = createGridPartes(partes);

  let form = await formVendido(maquina);

  //comprobar si maquina[0].usuarios.id_cliente.nombre existe
  let fichaCliente = "";
  if (maquina[0].usuarios.id_cliente && maquina[0].usuarios.id_cliente.nombre) {
    fichaCliente = `
        <div class="ficha">
            <div class="fichaTitulo">Cliente</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${maquina[0].usuarios.id_cliente.nombre || ""}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${maquina[0].usuarios.id_cliente.direccion || ""}</div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> ${maquina[0].usuarios.id_cliente.email || ""}</div>
                <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> ${maquina[0].usuarios.id_cliente.telefono_contacto || ""}</div>
                <div class="fichaCampo"><strong>Movil:&nbsp;</strong> ${maquina[0].usuarios.id_cliente.telefono_whatsapp || ""}</div>
            </div>
        </div>
    `;
  }

  let fichaAgente = "";
  if (maquina[0].usuarios.id_agente && maquina[0].usuarios.id_agente.nombre) {
    fichaAgente = `
        <div class="ficha">
            <div class="fichaTitulo">Agente</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${maquina[0].usuarios.id_agente.nombre || ""}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${maquina[0].usuarios.id_agente.direccion || ""}</div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> ${maquina[0].usuarios.id_agente.email || ""}</div>
                <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> ${maquina[0].usuarios.id_agente.telefono_contacto || ""}</div>
                <div class="fichaCampo"><strong>Movil:&nbsp;</strong> ${maquina[0].usuarios.id_agente.telefono_whatsapp || ""}</div>
            </div>
        </div>
    `;
  }

  let fichasContainer = `
    <div class="fichasContainer">
        ${fichaCliente}
        ${fichaAgente}
    </div>
  `;

  let titulo = "Maquina Vendida";
  let contenido = gridPartes + form + fichasContainer;
  let funcionGuardar = "guardarVendido";
  let usuario = "administrador";

  createModal(titulo, contenido, funcionGuardar, usuario, id);
}

function procesarImagen(url, propiedad) {
  if (typeof propiedad === "string") {
    if (!propiedad.includes("public/")) {
      return "imgs/image.png";
    } else {
      return url + "api/" + propiedad;
    }
  } else {
    return "imgs/image.png";
  }
}

async function formVendido(maquina) {
  let data = maquina[0];

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

  let imagen = getInputFile((id = "imagen"), (label = "Imagen"), (value = data.imagen));
  let modelo = getInputText((id = "modelo"), (label = "Modelo"), (value = data.modelo));
  let codigo = getInputText((id = "codigo"), (label = "Código"), (value = data.codigo));
  let n_serie = getInputText((id = "n_serie"), (label = "Número de Serie"), (value = data.n_serie));
  let observaciones = getInputText((id = "observaciones"), (label = "Observaciones"), (value = data.observaciones));
  let foto_2 = getInputFile((id = "foto_2"), (label = "Foto 2"), (value = data.foto_2));
  let foto_3 = getInputFile((id = "foto_3"), (label = "Foto 3"), (value = data.foto_3));
  let foto_4 = getInputFile((id = "foto_4"), (label = "Foto 4"), (value = data.foto_4));

  //datalist Cliente
  let labelCliente = "Cliente";
  let optionsCliente = localStorage.getItem("nombresDeClientes");
  optionsCliente = JSON.parse(optionsCliente);
  let valueCliente = data.id_cliente;
  let cliente = getDataList((id = "id_cliente"), (label = labelCliente), (options = optionsCliente), (value = valueCliente));

  //datalist Agente
  let labelAgente = "Agente";
  let optionsAgente = localStorage.getItem("nombresDeAgentes");
  optionsAgente = JSON.parse(optionsAgente);
  let valueAgente = data.id_agente;
  let agente = getDataList((id = "id_agente"), (label = labelAgente), (options = optionsAgente), (value = valueAgente));

  let qr = getBoton(texto = "Descargar QR", funcion = "botonDescargarQr", id = data.codigo);

  nombre_select = 'marca_maquina';
  id = 'marca';
  label = 'Marca';
  value = data.marca;
  tabla_select = 'maquinas';
  id_input_relacionado = '';
  onchangeFunction = '';
  let marca = await getDataListCompleto(id, label, value, tabla_select,  nombre_select, id_input_relacionado, onchangeFunction);

  nombre_select = 'tipo_maquina';
  id = 'tipo_maquina';
  label = 'Tipo Máquina';
  value = data.maquina;
  tabla_select = 'maquinas';
  id_input_relacionado = '';
  onchangeFunction = '';
  let tipo_maquina = await getDataListCompleto(id, label, value, tabla_select,  nombre_select, id_input_relacionado, onchangeFunction);

  let campos = [imagen, tipo_maquina, marca, modelo, codigo, n_serie, observaciones, cliente, agente, foto_2, foto_3, foto_4, qr];

  let form = createForm(campos, "guardarVendido");

  return form;
}

async function getMaquina(id) {
  let clase = "Maquinas";
  let funcion = "getById";
  let datos = {
    id: id,
  };

  let maquina = await queryAsync(clase, funcion, datos);
  maquina = maquina.data;

  return maquina;
}

async function getPartes(id_maquina) {
  let clase = "Partes";
  let funcion = "getPartesByMaquinaId";
  let datos = {
    id_maquina: id_maquina,
  };

  let tareas = await queryAsync(clase, funcion, datos);
  tareas = tareas.data;

  return tareas;
}

async function procesarImagenInput(id) {
  let imagen = document.getElementById(id).value;

  if (imagen === "") {
    return null;
  } else {
    let imagenFile = document.getElementById(id).files[0];
    let nombreImagen = imagenFile.name;
    let base64 = await toBase64(imagenFile);
    base64 = base64.split("base64,")[1];

    return {
      nombre: nombreImagen,
      base64: base64,
    };
  }
}

async function guardarVendido(id = 0) {
  let maquina = document.getElementById("tipo_maquina").value;
  let marca = document.getElementById("marca").value;
  let modelo = document.getElementById("modelo").value;
  let codigo = document.getElementById("codigo").value;
  let n_serie = document.getElementById("n_serie").value;
  let observaciones = document.getElementById("observaciones").value;

  let id_cliente = document.getElementById("id_cliente").value;
  id_cliente = id_cliente.split("|").pop();

  let id_agente = document.getElementById("id_agente").value;
  id_agente = id_agente.split("|").pop();

  let datos = {
    data: {
      maquina: maquina,
      marca: marca,
      modelo: modelo,
      codigo: codigo,
      n_serie: n_serie,
      observaciones: observaciones,
      id_cliente: id_cliente,
      id_agente: id_agente,
    },
  };

  if (id !== 0) {
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

  let clase = "Maquinas";
  let funcion = "update";

  if (id === 0) {
    funcion = "create";
  }

  let data = await queryAsync(clase, funcion, datos);

  getVendidos();
  destruirModal();
}

//######################################################################################################//
///////////////////////////////////////////////// PARTES /////////////////////////////////////////////////
//######################################################################################################//

function createGridPartes(data) {
  let cards = "";
  for (let card of data) {
    let funcionApertura = "openParte";

    //obtener el nombre del tecnico
    let nombreTecnico = "";
    if (card.id_tecnico) {
      let nombresDeTecnicos = localStorage.getItem("nombresDeTecnicos");
      nombresDeTecnicos = JSON.parse(nombresDeTecnicos);

      nombreTecnico = nombresDeTecnicos[card.id_tecnico];
    }

    //decodficar url de estado y tipo_urgencia
    card.estado = decodeURIComponent(card.estado);
    card.tipo_urgencia = decodeURIComponent(card.tipo_urgencia);

    let info1 = card.tipo;
    let info2 = card.tipo_urgencia;
    let info3 = card.notas;
    let info4 = "Técnico: " + nombreTecnico;
    let info5 = card.estado;
    let id = card.id;

    let imagen = null;

    cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
  }

  let titulo = "Partes";
  //createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder)
  console.log('modalCrearParte');
  let grid = createGrid(cards, 1, 1, titulo, "modalCrearParte", "", "");
  return grid;
}

async function openParte(id) {
  let idOriginal = id;
  console.log(id);

  //obtener datos del parte
  let clase = "Partes";
  let funcion = "getById";
  let datos = {
    id: id,
  };

  let parte = await queryAsync(clase, funcion, datos);
  parte = parte.data;

  //decodificar ulr de estado y tipo_urgencia
  parte.estado = decodeURIComponent(parte.estado);
  parte.tipo_urgencia = decodeURIComponent(parte.tipo_urgencia);

  //obtener los datos del maquina asociado
  clase = "Maquinas";
  funcion = "getById";
  datos = {
    id: parte.id_maquina,
  };

  let maquina = await queryAsync(clase, funcion, datos);
  maquina = maquina.data;

  //obtener los documentos asociados
  clase = "Documentos";
  funcion = "getByParteId";
  datos = {
    id_parte: id,
  };

  let documentos = await queryAsync(clase, funcion, datos);
  documentos = documentos.data;

  //obtener las taras asociadas
  clase = "Tareas";
  funcion = "getByParteId";
  datos = {
    id_parte: id,
  };

  let tareas = await queryAsync(clase, funcion, datos);
  tareas = tareas.data;

  console.log({ parte, maquina, documentos, tareas });

  //campos del parte
  /*
  let tipo = "";
  let optionsTipo = localStorage.getItem("tiposDePartes");
  optionsTipo = JSON.parse(optionsTipo);
  let valueTipo = parte.tipo;
  tipo = getDataListSimple((id = "tipo"), (label = "Tipo"), (options = optionsTipo), (value = valueTipo));*/

  let nombre_select = 'tipo_parte';
  let idDatalist = 'tipo';
  let labelDataList = 'Tipo';
  let valueDataList = parte.tipo;
  let tabla_select = 'partes';
  let id_input_relacionado = '';
  let onchangeFunction = '';
  let tipo = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  let fecha = getInputDate((id = "fecha"), (label = "Fecha"), (value = parte.fecha));
  let notas = getInputText((id = "notas"), (label = "Notas"), (value = parte.notas));
  console.log(parte.estado);

  //estado del parte
  //dataList de estados_parte
  nombre_select = 'estados_parte';
  idDatalist = 'estado';
  labelDataList = 'Estado Parte';
  valueDataList = parte.estado;
  tabla_select = 'partes';
  id_input_relacionado = '';
  onchangeFunction = '';
  let estado = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  //dataList de urgencia_parte
  nombre_select = 'urgencia_parte';
  idDatalist = 'tipo_urgencia';
  labelDataList = 'Urgencia Parte';
  valueDataList = parte.tipo_urgencia;
  tabla_select = 'partes';
  id_input_relacionado = '';
  onchangeFunction = '';
  let urgencia_parte = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  //datalist Tecnico
  let tecnico = "";
  let optionsTecnico = localStorage.getItem("nombresDeTecnicos");
  optionsTecnico = JSON.parse(optionsTecnico);
  let valueTecnico = parte.id_tecnico;
  tecnico = getDataList((id = "id_tecnico"), (label = "Técnico"), (options = optionsTecnico), (value = valueTecnico));

  let campos = [tipo, fecha, notas, estado, urgencia_parte, tecnico];

  let form = createForm(campos, "guardarParte");

  //grid con ficha del motro asociado no editable
  let cardsMaquina = cardsVendidos([maquina[0]]);
  let gridMaquina = createGrid(cardsMaquina, 1, 1, "Maquina", "", "", "");

  let cardsTareas = "";
  for (let card of tareas) {
    let inputs = [];

    //datalist tipo
    nombre_select = 'tipo_tarea';
    idDataList = 'tipo_' + card.id;
    labelDataList = '';
    valueDataList = card.tipo;
    tabla_select = 'tareas';
    id_input_relacionado = 'coste_' + card.id;
    onchangeFunction = '';
    let tipo_tarea = await getDataListCompleto(idDataList, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

    inputs.push({ tipo: "html", id: "tipo_tarea", label: "Tipo", value: tipo_tarea });
    inputs.push({ tipo: "text", id: "descripcion", label: "Descripción", value: card.descripcion });
    inputs.push({ tipo: "text", id: "referencia", label: "Referencia", value: card.referencia });
    inputs.push({ tipo: "text", id: "coste", label: "Coste", value: card.coste });


    let InputsForm = createInputFormCardEditable(inputs, card.id);

    let cardTarea = createCardEditable("form", null, null, InputsForm, card.id, "eliminarTarea", "guardarTarea");

    cardsTareas += cardTarea;
  }

  let gridTareas = createGridCardsEditables("Tareas", cardsTareas, "crearTarea");

  //grid documentos
  let cardsArchivos = "";
  let cardsImagenes = "";
  let cardsVideos = "";
  for (let card of documentos) {
    let tipo = card.tipo;

    //si el tipo es DOCUMENTO añadir a cardsArchivos
    if (tipo === "DOCUMENTO") {
      let cardArchivo = createCardEditable("archivo", null, card.url, null, card.id, "eliminarDocumento", "guardarDocumento");
      cardsArchivos += cardArchivo;
    }

    //si el tipo es IMAGEN añadir a cardsImagenes
    if (tipo === "IMAGEN") {
      if (card.comentarios === null) {
        card.comentarios = "";
      }
      let cardImagen = createCardEditable(
        "imagen",
        card.url,
        null,
        null,
        card.id,
        "eliminarDocumento",
        "guardarDocumento",
        card.comentarios,
        "actualizarComentario"
      );
      cardsImagenes += cardImagen;
    }

    //si el tipo es VIDEO añadir a cardsVideos
    if (tipo === "VIDEO") {
      if (card.comentarios === null) {
        card.comentarios = "";
      }
      let cardVideo = createCardEditable(
        "video",
        null,
        card.url,
        null,
        card.id,
        "eliminarDocumento",
        "guardarDocumento",
        card.comentarios,
        "actualizarComentario"
      );
      cardsVideos += cardVideo;
    }
  }

  let gridArchivos = createGridCardsEditables("Archivos", cardsArchivos, "crearDocumento");
  let gridImagenes = createGridCardsEditables("Imagenes", cardsImagenes, "crearDocumento");
  let gridVideos = createGridCardsEditables("Videos", cardsVideos, "crearDocumento");

  //crear chat
  let conversacion = parte.mensajes_cliente;
  //si conversacion es null o vacía crear un array vacío
  if (conversacion === null || conversacion === "") {
    conversacion = [];
  } else {
    conversacion = JSON.parse(conversacion);
  }

  let enviado = parte.enviado_cliente;
  let aprobado = parte.aprobado_cliente;
  let idMaquina = parte.id_maquina;
  let idParte = parte.id;
  let telefono_whatsapp = maquina[0].usuarios.id_cliente.telefono_whatsapp;
  let nombre_cliente = maquina[0].usuarios.id_cliente.nombre;

  let chat = chatPartes(conversacion, enviado, aprobado, idMaquina, idParte, telefono_whatsapp, nombre_cliente);

  //tiempos
  let tiempos = JSON.parse(parte.tiempos);
  //guardar en localStorage
  localStorage.setItem("tiempos", JSON.stringify(tiempos));

  let tiemposHtml = tiemposPartes(tiempos, idParte);
  console.log({ tiemposHtml });

  //firma
  //si parte.firma es null, false o vacío se crea el campo firma
  let firma = "";
  if (parte.firma === null || parte.firma === false || parte.firma === "") {
    firma = firmaPartes(idParte);
  } else {
    let url = window.location.href;

    firma = `
      <div class="firma" style="display: flex; justify-content: center; align-items: center; width: 100%;">
        <img src="${url}api/${parte.firma}" alt="firma" style="width: 250px; height: 250px; border: 1px solid black">
      </div>
    `;
  }

  //boton para imprimir un ticket
  let botonImprimir = `
    <div style="width: 100%; display: flex; justify-content: center; align-items: center; margin-top: 10px;">
      <div class="modalBoton" onclick="imprimirTicket(${id})">Imprimir Ticket</div>
    </div>
  `;

  let botonEliminar = `
    <div style="width: 100%; display: flex; justify-content: center; align-items: center; margin-top: 10px; margin-bottom: 20px;">
      <div class="modalBoton" onclick="modalEliminarParte(${idOriginal})">Eliminar Parte</div>
    </div>
  `;

  //abrir modal
  let titulo = "Parte";
  let contenido = form + gridMaquina + gridTareas + botonImprimir + tiemposHtml + chat + gridArchivos + gridImagenes + gridVideos + botonEliminar + firma;
  let funcionGuardar = "guardarParte";
  let usuario = "administrador";

  createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);

  let chatGlobos = document.querySelector(".chatGlobos");
  console.log(chatGlobos);
  chatGlobos.scrollTop = chatGlobos.scrollHeight;
}

async function modalEliminarParte(id) {
  console.log(id);
  //createModalAviso
  let titulo = "Eliminar Parte"
  let mensaje = "¿Deseas eliminar este parte? Esta acción no se puede deshacer"
  let funcionAceptar = "eliminarParte"
  let arrayParametros = [id]
  createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function eliminarParte(id) {
  let clase = "Partes";
  let funcion = "delete";
  let datos = {
    id: id,
  };

  let data = await queryAsync(clase, funcion, datos);

  getVendidos();
  destruirModal();
}

async function imprimirTicket() {
  let grid_Tareas = document.getElementById("grid_Tareas");

  let cardEditableFormInputsArray = grid_Tareas.getElementsByClassName("cardEditableFormInputs");
  let tareas = [];
  for (let cardEditableFormInputs of cardEditableFormInputsArray) {
    let inputs = cardEditableFormInputs.getElementsByTagName("input");

    let tipo_tarea = inputs[0].value;
    let descripcion = inputs[1].value;
    let referencia = inputs[2].value;
    let coste = inputs[3].value;

    tareas.push({ tipo: tipo_tarea, descripcion: descripcion, referencia: referencia, coste: coste });
  }

  let tareasHtml = "";
  for (let tarea of tareas){
    //si tarea tipo comienza por tres caracteres numéricos borra los tres primeros caracteres y el espacio
    if (tarea.tipo.substring(0, 3).match(/\d/g)) {
      tarea.tipo = tarea.tipo.substring(4);
    }

    //si descripcion comienza por tres caracteres numéricos borra los tres primeros caracteres y el espacio
    if (tarea.descripcion.substring(0, 3).match(/\d/g)) {
      tarea.descripcion = tarea.descripcion.substring(4);
    }

    //si descripcion contiene " - " obtener el segundo valor
    if (tarea.descripcion.includes(" - ")) {
      tarea.descripcion = tarea.descripcion.split(" - ")[1];
    }

    //si coste es 0, vacio o null ponerlo en blanco
    if (tarea.coste === "0" || tarea.coste === "" || tarea.coste === null || tarea.coste === undefined || tarea.coste === 0 || tarea.coste === "0.00") {
      tarea.coste = "";
    }

    tareasHtml += `
      <div style="display: flex; justify-content: space-between;">
        <div>${tarea.tipo}</div>
        <div>${tarea.descripcion}</div>
        <div>${tarea.referencia}</div>
        <div>${tarea.coste}</div>
      </div>
    `;
  }

  //mostrar en otra ventana e imprimir
  let ventana = window.open("", "", "width=800,height=600");
  ventana.document.write(`
    <html>
      <head>
        <style>
          body {
            font-family: Arial, sans-serif;
          }
          .ticket {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
          }
          .ticket div {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
          }
        </style>
      </head>
      <body>
        <div class="ticket">
          <div style="font-weight: bold;">Tareas</div>
          ${tareasHtml}
        </div>
      </body>
    </html>
  `);

  ventana.print();
}

//obtener tipos de partes y precios
async function getTiposDePartes() {
  let clase = "Partes";
  let funcion = "getTiposDePartes";
  let datos = {};

  let data = await queryAsync(clase, funcion, datos);
  data = data.data;

  //guardar en localStorage
  localStorage.setItem("tiposDePartes", JSON.stringify(data.tipos));
  localStorage.setItem("preciosDePartes", JSON.stringify(data.precios));
}

//actualizarComentario
async function actualizarComentario(id) {
  let comentario = document.getElementById(id);
  comentario = comentario.value;

  id = id.split("_").pop();

  let datos = {
    data: {
      comentarios: comentario,
    },
    id: id,
  };

  let clase = "Documentos";
  let funcion = "update";
  let data = await queryAsync(clase, funcion, datos);
}

async function modalCrearParte() {
  console.log("crear parte");
  let fechaHoy = new Date().toISOString().split("T")[0];

  let nombre_select = 'tipo_parte';
  let idDatalist = 'tipo';
  let labelDataList = 'Tipo';
  let valueDataList = "";
  let tabla_select = 'partes';
  let id_input_relacionado = '';
  let onchangeFunction = '';
  let tipo = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  let fecha = getInputDate((id = "fecha"), (label = "Fecha"), (value = fechaHoy));
  let notas = getInputText((id = "notas"), (label = "Notas"), (value = ""));
  
  //dataList de estados_parte
  nombre_select = 'estados_parte';
  idDatalist = 'estado';
  labelDataList = 'Estado Parte';
  valueDataList = '';
  tabla_select = 'partes';
  id_input_relacionado = '';
  onchangeFunction = '';
  let estado = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  //dataList de urgencia_parte
  nombre_select = 'urgencia_parte';
  idDatalist = 'tipo_urgencia';
  labelDataList = 'Urgencia Parte';
  valueDataList = '';
  tabla_select = 'partes';
  id_input_relacionado = '';
  onchangeFunction = '';
  let urgencia_parte = await getDataListCompleto(idDatalist, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  //datalist Tecnico
  let tecnico = "";
  let optionsTecnico = localStorage.getItem("nombresDeTecnicos");
  optionsTecnico = JSON.parse(optionsTecnico);
  let valueTecnico = "";
  tecnico = getDataList((id = "id_tecnico"), (label = "Técnico"), (options = optionsTecnico), (value = valueTecnico));

  let campos = [tipo, fecha, notas, estado, urgencia_parte, tecnico];

  let form = createForm(campos, "guardarParte");

  let titulo = "Crear Parte";
  let contenido = form;
  let funcionGuardar = "guardarParte";
  let usuario = "administrador";

  createModal(titulo, contenido, funcionGuardar, usuario);
}

async function guardarParte(id = 0) {
  let tipo = document.getElementById("tipo").value;
  console.log(tipo);
  if (tipo === "0") {
    tipo = "INSTALACION";
  }

  let fecha = document.getElementById("fecha").value;
  let notas = document.getElementById("notas").value;

  let estado = document.getElementById("estado").value;
  let tipo_urgencia = document.getElementById("tipo_urgencia").value;

  let id_tecnico = document.getElementById("id_tecnico").value;
  console.log(id_tecnico);

  if (id_tecnico === "0" || id_tecnico === "" || id_tecnico === null || id_tecnico === undefined || id_tecnico === 1 || id_tecnico === 0) {
    id_tecnico = "0";
  }else{
    id_tecnico = id_tecnico.split("|").pop();
  }

  //obtener id_maquina
  //obtener todos los elementos de la clase modalTitulo
  let modalTitulo = document.getElementsByClassName("modalTitulo");
  let id_maquina = modalTitulo[0].id;

  //codificar url el estado y tipo_urgencia
  estado = encodeURIComponent(estado);
  tipo_urgencia = encodeURIComponent(tipo_urgencia);

  let datos = {
    data: {
      tipo: tipo,
      fecha: fecha,
      notas: notas,
      estado: estado,
      id_tecnico: id_tecnico,
      id_maquina: id_maquina,
      tipo_urgencia: tipo_urgencia,
    },
  };

  //si estado es CERRADO actualizarEstadoAlCerrarParte(idParte, idMaquina)
  if (estado === "CERRADO") {
    await actualizarEstadoAlCerrarParte(id, id_maquina);
  }

  if (id !== 0) {
    datos.id = id;

    //eliminar id_maquina de datos
    delete datos.data.id_maquina;
  }

  let clase = "Partes";
  let funcion = "update";

  if (id === 0) {
    funcion = "create";
  }

  //si el tipo no existe en localStorage, añadirlo a localstorage
  let tiposDePartes = localStorage.getItem("tiposDePartes");
  tiposDePartes = JSON.parse(tiposDePartes);

  if (!tiposDePartes.includes(tipo)) {
    tiposDePartes.push(tipo);
    localStorage.setItem("tiposDePartes", JSON.stringify(tiposDePartes));
  }

  let data = await queryAsync(clase, funcion, datos);

  destruirModal();
  destruirModal();

  //si está en tiempos, actualizar grid de tiempos y si está en vendidos openVendido
  let location = localStorage.getItem("location");
  console.log(location);

  if (location === "tiempos") {
    mostrarPartesTiempos();
  } else {
    openVendido(id_maquina);
  }
}

async function guardarTarea(id = 0) {
  let descripcion = document.getElementById("descripcion" + "_" + id).value;
  let tipo = document.getElementById("tipo" + "_" + id).value;
  let referencia = document.getElementById("referencia" + "_" + id).value;
  let coste = document.getElementById("coste" + "_" + id).value;

  //obtener id_parte
  //obtener todos los elementos de la clase modalTitulo
  let modalTitulo = document.getElementsByClassName("modalTitulo");
  let id_parte = modalTitulo[1].id;

  let datos = {
    data: {
      descripcion: descripcion,
      tipo: tipo,
      referencia: referencia,
      coste: coste,
      id_parte: id_parte,
    },
  };

  if (id !== 0) {
    datos.id = id;
  }

  let clase = "Tareas";
  let funcion = "update";

  if (id === 0) {
    funcion = "create";
  }

  let data = await queryAsync(clase, funcion, datos);

  //si coste es diferente de "" actualizarEstadoAlInsertarTarea(idParte, idMaquina)
  if (id !== 0 && coste !== "") {
    //obtener id_maquina
    let cards = document.querySelectorAll(".card");

    //obtener el id de la ultima card
    let idMaquina = cards[cards.length - 1].id.split("_").pop();

    console.log(idMaquina);

    await actualizarEstadoAlInsertarTarea(id_parte, idMaquina);
  }

  if (id === 0) {
    //destruir el modal
    destruirModal();
    //volver a cargar las tareas
    openParte(id_parte);

    //navegar dentro del modalBodyContenido hasta el medio
    let modalBodyContenido = document.getElementsByClassName("modalBodyContenido");
    modalBodyContenido[0].scrollTop = modalBodyContenido[0].scrollHeight / 2;
  }
}

//crearTarea
async function crearTarea() {
  //datalist tipo
  nombre_select = 'tipo_tarea';
  idDataList = 'tipo_0';
  labelDataList = '';
  valueDataList = '';
  tabla_select = 'tareas';
  id_input_relacionado = 'coste_0';
  onchangeFunction = '';
  let tipo_tarea = await getDataListCompleto(idDataList, labelDataList, valueDataList, tabla_select, nombre_select, id_input_relacionado, onchangeFunction);

  let inputs = [
    { tipo: "html", id: "tipo_tarea", label: "Tipo", value: tipo_tarea },
    { tipo: "text", id: "descripcion", label: "Descripción", value: "" },
    { tipo: "text", id: "referencia", label: "Referencia", value: "" },
    { tipo: "text", id: "coste", label: "Coste", value: "" }
  ];

  let InputsForm = createInputFormCardEditable(inputs, 0);

  let card = createCardEditable("form", null, null, InputsForm, 0, "eliminarTarea", "guardarTarea");

  //gridCardsEditables_${titulo}
  let grid = document.getElementById("gridCardsEditables_Tareas");
  grid.innerHTML = card + grid.innerHTML;
}

async function eliminarTarea(id) {
  let datos = {
    id: id,
  };

  let clase = "Tareas";
  let funcion = "delete";
  let data = await queryAsync(clase, funcion, datos);

  //eliminar la card //cardEditableForm_${id}
  let card = document.getElementById("cardEditableForm_" + id);
  card.remove();
}

function crearDocumento(tipo) {
  console.log(tipo);

  if (tipo === "Archivos") {
    tipo = "DOCUMENTO";
  } else if (tipo === "Imagenes") {
    tipo = "IMAGEN";
  } else if (tipo === "Videos") {
    tipo = "VIDEO";
  }

  //abrir un modal de aviso para seleccionar el archivo
  let titulo = "Seleccionar Archivo";
  let mensaje = `
        <input type="file" id="documentoParaSubir">
    `;
  let funcionAceptar = "subirDocumento";
  let arrayParametros = [tipo];

  createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
}

async function subirDocumento(tipo) {
  let documentoParaSubir = document.getElementById("documentoParaSubir").files[0];

  let nombre = documentoParaSubir.name;
  let base64 = await toBase64(documentoParaSubir);
  base64 = base64.split("base64,")[1];

  let modalTitulo = document.getElementsByClassName("modalTitulo");
  let id_parte = modalTitulo[1].id;

  let datos = {
    data: {
      id_parte: id_parte,
      tipo: tipo,
      documento: {
        nombre: nombre,
        base64: base64,
      },
    },
  };

  let clase = "Documentos";
  let funcion = "create";
  let data = await queryAsync(clase, funcion, datos);

  //destruir el modal de aviso
  destruirModalAviso();

  //destruir el modal
  destruirModal();

  //volver a cargar el modal
  openParte(id_parte);
}

async function eliminarDocumento(id) {
  let datos = {
    id: id,
  };

  let clase = "Documentos";
  let funcion = "delete";
  let data = await queryAsync(clase, funcion, datos);

  //eliminar la card //cardEditableForm_${id}
  let card = document.getElementById("cardEditableDocumento_" + id);
  card.remove();
}

async function enviarPresupuesto(idMaquina, idParte, telefono_whatsapp) {
  let cardMotor = document.getElementById("card_" + idMaquina);
  let cardInfo = cardMotor.querySelector(".card_info_1").innerText;

  let datos = {
    telefono: telefono_whatsapp,
    nombre_motor: cardInfo,
    id_motor: idMaquina,
    id_parte: idParte
  }

  console.log(datos);

  let tareas = document.getElementById("gridCardsEditables_Tareas").querySelectorAll(".cardEditableForm");

  let tareasArray = [];
  for (let tarea of tareas) {
    console.log(tarea);
    //obtener el id de la tarea
    let idTarea = tarea.id.split("_").pop();
    let idDescripcion = "tipo_" + idTarea;
    let idCoste = "coste_" + idTarea;

    let descripcion = document.getElementById(idDescripcion).value;

    //en la descripción hay que quitar los tres primeros caracteres y el espacio
    if (descripcion.substring(0, 3).match(/\d/g)) {
      descripcion = descripcion.substring(4);
    }

    let coste = document.getElementById(idCoste).value;

    let tareaObj = {
      descripcion: descripcion,
      coste: coste
    }

    tareasArray.push(tareaObj);
  }

  //crear un presupuesto en un string:
  let presupuesto = "*Presupuesto:*\n\n";
  //nombre del motor
  presupuesto += `${datos.nombre_motor}\n\n`;
  for (let tarea of tareasArray) {
    presupuesto += `* ${tarea.descripcion}\n`;
  }

  //obtener el coste total
  let costeTotal = 0;
  for (let tarea of tareasArray) {
    //si el coste no es un número, añadir 0
    if (isNaN(tarea.coste)) {
      tarea.coste = 0;
    }

    costeTotal += parseFloat(tarea.coste);
  }

  presupuesto += `\n*Coste Total: ${costeTotal}€*`;

  //obtener el tiempo total por el texto de la clase tiempoTotal
  let tiempoTotal = document.getElementsByClassName("tiempoTotal")[0].innerText;
  presupuesto += `\nTiempo Total: ${tiempoTotal}`;

  console.log(presupuesto);

  //marcar como enviado
  await enviadoCliente(idParte, idMaquina, true);

  //obtener del web component whatsapp-chat-user el textarea con id autoResizeTextarea y el boton <button onclick="this.getRootNode().host.sendMessage()">
  //obtener el web component
  let whatsappChatUser = document.querySelector("whatsapp-chat-user");
  //obtener el textarea
  let textarea = whatsappChatUser.shadowRoot.getElementById("autoResizeTextarea");
  //obtener el boton
  let boton = whatsappChatUser.shadowRoot.querySelectorAll("button");
  //obtener el boton de enviar donde el onclick es sendMessage
  
  let botonEnviar = "";
  for (let button of boton) {
    if (button.getAttribute("onclick") === "this.getRootNode().host.sendMessage()") {
      botonEnviar = button;
    }
  }

  //rellenar el textarea
  textarea.value = presupuesto;

  //enviar el mensaje
  
  botonEnviar.click();
}

async function enviadoCliente(idParte, idMaquina, value) {
  console.log(value);
  let clase = "Partes";
  let funcion = "updateEstados";

  if (value) {
    value = "SI";
  } else {
    value = "NO";
  }

  let datos = {
    idParte: idParte,
    idMaquina: idMaquina,
    tareasEnviadoCliente: value,
    tareasAprobadoCliente: "PENDIENTE",
    maquinasVisto: "PENDIENTE",
  }

  let data = await queryAsync(clase, funcion, datos);

  console.log(data);
}

async function aprobarCliente(idParte, idMaquina, value) {
  let clase = "Partes";
  let funcion = "updateEstados";

  if (value) {
    value = "APROBADO";
  } else {
    value = "PENDIENTE";
  }

  let datos = {
    idParte: idParte,
    idMaquina: idMaquina,
    tareasEnviadoCliente: "SI",
    tareasAprobadoCliente: value,
    maquinasVisto: value
  }

  let data = await queryAsync(clase, funcion, datos);

  console.log(data);
}

async function actualizarEstadoAlCerrarParte(idParte, idMaquina) {
  let clase = "Partes";
  let funcion = "updateEstados";

  let datos = {
    idParte: idParte,
    idMaquina: idMaquina,
    tareasEnviadoCliente: "SI",
    tareasAprobadoCliente: "APROBADO",
    maquinasVisto: "",
  }

  let data = await queryAsync(clase, funcion, datos);
}

async function actualizarEstadoAlInsertarTarea(idParte, idMaquina) {
  let clase = "Partes";
  let funcion = "updateEstados";

  let datos = {
    idParte: idParte,
    idMaquina: idMaquina,
    tareasEnviadoCliente: "NO",
    tareasAprobadoCliente: "PENDIENTE",
    maquinasVisto: "PENDIENTE",
  }

  let data = await queryAsync(clase, funcion, datos);

}

async function actualizarTiempo(idParte, position, tiempo) {
  let tiempos = JSON.parse(localStorage.getItem("tiempos"));

  //actualizar horasTotales de la posición del array
  tiempos[position].horasTotales = tiempo;

  //actualizar localStorage
  localStorage.setItem("tiempos", JSON.stringify(tiempos));

  //actualizar en la base de datos
  let clase = "Partes";
  let funcion = "update";
  let datos = {
    id: idParte,
    data: {
      tiempos: JSON.stringify(tiempos)
    }
  }

  let data = await queryAsync(clase, funcion, datos);

  console.log(data);
}

async function gestionarTiempos(idParte) {
  let clase = "Partes";
  let funcion = "gestionarTiempos";
  let data = {
    idParte: idParte
  }

  let response = await queryAsync(clase, funcion, data);

  //eliminar el modal
  destruirModal();

  //volver a cargar el parte
  openParte(idParte);
}

async function enviarFirma(idParte) {
  obtenerContextoCanvas(); // Asegurarse de que ctx esté inicializado.
  const canvas = document.getElementById('canvasFirma');

  // Rellenar el fondo con blanco antes de exportar a JPEG
  const ctx = canvas.getContext('2d');
  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = 'white';
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.putImageData(imageData, 0, 0);

  // Especificar el formato JPG al obtener la dataURL
  const dataURL = canvas.toDataURL('image/jpeg');
  console.log(dataURL);

  // Obtener la firma en jpg base64 sin el prefijo
  let firma = dataURL.split("base64,")[1];

  console.log(firma);

  //actualizar parte
  let clase = "Partes";
  let funcion = "update";
  let datos = {
    id: idParte,
    data: {
      estado: "CERRADO",
      firma: firma
    }
  }

  let data = await queryAsync(clase, funcion, datos);
}