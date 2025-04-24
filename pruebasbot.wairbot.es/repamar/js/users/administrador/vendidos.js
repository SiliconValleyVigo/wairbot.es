///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getVendidos(nPagina = 1) {
  localStorage.setItem("location", "vendidos");

  //obtener los datos de los agentes
  let clase = "Motores";
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

  let titulo = "Motores Vendidos";
  let funcionCrear = null;
  let funcionAvanzar = "avanzarVendidos";
  let funcionRetroceder = "retrocenderVendidos";

  let grid = createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder);

  let administrador = document.getElementById("administrador");
  administrador.innerHTML = grid;
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

    let info1 = card.marca + " " + card.modelo;
    let info2 = card.codigo;
    let info3 = card.observaciones;
    let info4 = agente;
    let info5 = cliente;
    let id = card.id;

    cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
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
  let motor = await getMotor(id);
  console.log(motor[0].usuarios.id_agente);
  let partes = await getPartes(id);

  if (partes === undefined) {
    partes = [];
  }

  let gridPartes = createGridPartes(partes);

  let form = formVendido(motor);

  //comprobar si motor[0].usuarios.id_cliente.nombre existe
  let fichaCliente = "";
  if (motor[0].usuarios.id_cliente) {
    fichaCliente = `
        <div class="ficha">
            <div class="fichaTitulo">Cliente</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${motor[0].usuarios.id_cliente.nombre || ""}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${motor[0].usuarios.id_cliente.direccion || ""}</div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> ${motor[0].usuarios.id_cliente.email || ""}</div>
                <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> ${motor[0].usuarios.id_cliente.telefono_contacto || ""}</div>
                <div class="fichaCampo"><strong>Movil:&nbsp;</strong> ${motor[0].usuarios.id_cliente.telefono_whatsapp || ""}</div>
            </div>
        </div>
    `;
  }

  let fichaAgente = "";
  if (motor[0].usuarios.id_agente) {
    fichaAgente = `
        <div class="ficha">
            <div class="fichaTitulo">Agente</div>
            <div class="fichaContenido">
                <div class="fichaCampo"><strong>Nombre:&nbsp;</strong> ${motor[0].usuarios.id_agente.nombre || ""}</div>
                <div class="fichaCampo"><strong>Dirección:&nbsp;</strong> ${motor[0].usuarios.id_agente.direccion || ""}</div>
                <div class="fichaCampo"><strong>Email:&nbsp;</strong> ${motor[0].usuarios.id_agente.email || ""}</div>
                <div class="fichaCampo"><strong>Teléfono:&nbsp;</strong> ${motor[0].usuarios.id_agente.telefono_contacto || ""}</div>
                <div class="fichaCampo"><strong>Movil:&nbsp;</strong> ${motor[0].usuarios.id_agente.telefono_whatsapp || ""}</div>
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

    console.log(fichasContainer);

  let titulo = "Motor Vendido";
  let contenido = form + gridPartes + fichasContainer;
  let funcionGuardar = "guardarVendido";
  let usuario = "administrador";

  createModal(titulo, contenido, funcionGuardar, usuario, id);
}

function formVendido(motor) {
  let data = motor[0];

  let url = window.location.href;

  if (typeof data.imagen === "string") {
    if (!data.imagen.includes("public/")) {
      data.imagen = "imgs/image.png";
    } else {
      data.imagen = url + "api/" + data.imagen;
    }
  } else {
    data.imagen = "imgs/image.png";
  }

  let imagen = getInputFile((id = "imagen"), (label = "Imagen"), (value = data.imagen));
  let marca = getInputText((id = "marca"), (label = "Marca"), (value = data.marca));
  let modelo = getInputText((id = "modelo"), (label = "Modelo"), (value = data.modelo));
  let codigo = getInputText((id = "codigo"), (label = "Nº de Serie"), (value = data.codigo));
  let observaciones = getInputText((id = "observaciones"), (label = "Observaciones"), (value = data.observaciones));
  let fecha_instalacion = getInputDate((id = "fecha_instalacion"), (label = "Fecha Instalación"), (value = data.fecha_instalacion));
  let fecha_ultima_revision = getInputDate((id = "fecha_ultima_revision"), (label = "Fecha Ultima Revisión"), (value = data.fecha_ultima_revision));
  let fecha_proxima_revision = getInputDate(
    (id = "fecha_proxima_revision"),
    (label = "Fecha Próxima Revisión"),
    (value = data.fecha_proxima_revision)
  );

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

  let campos = [imagen, marca, modelo, codigo, observaciones, fecha_instalacion, fecha_ultima_revision, fecha_proxima_revision, cliente, agente];

  let form = createForm(campos, "guardarVendido");

  return form;
}

async function getMotor(id) {
  let clase = "Motores";
  let funcion = "getById";
  let datos = {
    id: id,
  };

  let motor = await queryAsync(clase, funcion, datos);
  motor = motor.data;

  return motor;
}

async function getPartes(id_motor) {
  let clase = "Partes";
  let funcion = "getPartesByMotorId";
  let datos = {
    id_motor: id_motor,
  };

  let tareas = await queryAsync(clase, funcion, datos);
  tareas = tareas.data;

  return tareas;
}

async function guardarVendido(id = 0) {
  let marca = document.getElementById("marca").value;
  let modelo = document.getElementById("modelo").value;
  let codigo = document.getElementById("codigo").value;
  let observaciones = document.getElementById("observaciones").value;
  let fecha_instalacion = document.getElementById("fecha_instalacion").value;
  let fecha_ultima_revision = document.getElementById("fecha_ultima_revision").value;
  let fecha_proxima_revision = document.getElementById("fecha_proxima_revision").value;

  let id_cliente = document.getElementById("id_cliente").value;
  id_cliente = id_cliente.split("|").pop();

  let id_agente = document.getElementById("id_agente").value;
  id_agente = id_agente.split("|").pop();

  let datos = {
    data: {
      marca: marca,
      modelo: modelo,
      codigo: codigo,
      observaciones: observaciones,
      fecha_instalacion: fecha_instalacion,
      fecha_ultima_revision: fecha_ultima_revision,
      fecha_proxima_revision: fecha_proxima_revision,
      id_cliente: id_cliente,
      id_agente: id_agente,
    },
  };

  if (id !== 0) {
    datos.id = id;
  }

  let imagen = document.getElementById("imagen").value;

  if (imagen === "") {
    imagen = null;
  } else {
    //obtener el nombre del archivo y el base64
    let imagenFile = document.getElementById("imagen").files[0];

    let nombreImagen = imagenFile.name;
    let base64 = await toBase64(imagenFile);
    //eliminar el prefijo
    base64 = base64.split("base64,")[1];

    imagen = {
      nombre: nombreImagen,
      base64: base64,
    };
  }

  if (imagen !== null) {
    datos.data.imagen = imagen;
  }

  let clase = "Motores";
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
    console.log(card);
    let funcionApertura = "openParte";

    //obtener el nombre del tecnico
    let nombreTecnico = "";
    if (card.id_tecnico) {
      let nombresDeTecnicos = localStorage.getItem("nombresDeTecnicos");
      nombresDeTecnicos = JSON.parse(nombresDeTecnicos);

      nombreTecnico = nombresDeTecnicos[card.id_tecnico];
    }

    let info1 = card.tipo;
    let info2 = card.fecha;
    let info3 = card.notas;
    let info4 = "Técnico: " + nombreTecnico;
    let info5 = card.estado;
    let id = card.id;

    let imagen = null;

    cards += createCard(funcionApertura, imagen, info1, info2, info3, info4, info5, id);
  }

  let titulo = "Partes";
  //createGrid(cards, paginaActual, totalPaginas, titulo, funcionCrear, funcionAvanzar, funcionRetroceder)
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

  //obtener los datos del motor asociado
  clase = "Motores";
  funcion = "getById";
  datos = {
    id: parte.id_motor,
  };

  let motor = await queryAsync(clase, funcion, datos);
  motor = motor.data;

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
  console.log(tareas);
  tareas = tareas.data;

  console.log({ parte, motor, documentos, tareas });

  //campos del parte
  //getSelect(id, label, options, value)  INSTALACION, REPARACION, REVISION
  let tipo = getSelect((id = "tipo"), (label = "Tipo"), (options = ["", "INSTALACION", "REPARACION", "REVISION"]), (value = parte.tipo));
  let fecha = getInputDate((id = "fecha"), (label = "Fecha"), (value = parte.fecha));
  let notas = getInputText((id = "notas"), (label = "Notas"), (value = parte.notas));
  console.log(parte.estado);
  let estado = getSelect((id = "estado"), (label = "Estado"), (options = ["ABIERTO", "CERRADO"]), (value = parte.estado));

  //datalist Tecnico
  let tecnico = "";
  let optionsTecnico = localStorage.getItem("nombresDeTecnicos");
  optionsTecnico = JSON.parse(optionsTecnico);
  let valueTecnico = parte.id_tecnico;
  tecnico = getDataList((id = "id_tecnico"), (label = "Técnico"), (options = optionsTecnico), (value = valueTecnico));

  let campos = [tipo, fecha, notas, estado, tecnico];

  let form = createForm(campos, "guardarParte");

  //grid con ficha del motro asociado no editable
  let cardsMotor = cardsVendidos([motor[0]]);
  let gridMotor = createGrid(cardsMotor, 1, 1, "Motor", "", "", "");

  let cardsTareas = "";
  for (let card of tareas) {
    let inputs = [
      { tipo: "text", id: "descripcion", label: "Descripción", value: card.descripcion },
      { tipo: "select", id: "tipo", label: "Tipo", value: card.tipo, options: ["", "REPARACION", "REVISION", "SUSTITUCION"] },
      { tipo: "text", id: "referencia", label: "Notas", value: card.referencia },
    ];

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

  //abrir modal
  let titulo = "Parte";
  let contenido = form + gridMotor + gridTareas + gridArchivos + gridImagenes + gridVideos;
  let funcionGuardar = "guardarParte";
  let usuario = "administrador";

  createModal(titulo, contenido, funcionGuardar, usuario, idOriginal);
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

function modalCrearParte() {
  let fechaHoy = new Date().toISOString().split("T")[0];

  let tipo = getSelect((id = "tipo"), (label = "Tipo"), (options = ["", "INSTALACION", "REPARACION", "REVISION"]), (value = ""));
  let fecha = getInputDate((id = "fecha"), (label = "Fecha"), (value = fechaHoy));
  let notas = getInputText((id = "notas"), (label = "Notas"), (value = ""));
  let estado = getSelect((id = "estado"), (label = "Estado"), (options = ["ABIERTO", "CERRADO"]), (value = "ABIERTO"));

  //datalist Tecnico
  let tecnico = "";
  let optionsTecnico = localStorage.getItem("nombresDeTecnicos");
  optionsTecnico = JSON.parse(optionsTecnico);
  let valueTecnico = "";
  tecnico = getDataList((id = "id_tecnico"), (label = "Técnico"), (options = optionsTecnico), (value = valueTecnico));

  let campos = [tipo, fecha, notas, estado, tecnico];

  let form = createForm(campos, "guardarParte");

  console.log(form);

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
  if (estado === "0") {
    estado = "ABIERTO";
  }

  let id_tecnico = document.getElementById("id_tecnico").value;
  console.log(id_tecnico);
  id_tecnico = id_tecnico.split("|").pop();

  //obtener id_motor
  //obtener todos los elementos de la clase modalTitulo
  let modalTitulo = document.getElementsByClassName("modalTitulo");
  let id_motor = modalTitulo[0].id;

  let datos = {
    data: {
      tipo: tipo,
      fecha: fecha,
      notas: notas,
      estado: estado,
      id_tecnico: id_tecnico,
      id_motor: id_motor,
    },
  };

  if (id !== 0) {
    datos.id = id;

    //eliminar id_motor de datos
    delete datos.data.id_motor;
  }

  let clase = "Partes";
  let funcion = "update";

  if (id === 0) {
    funcion = "create";
  }

  let data = await queryAsync(clase, funcion, datos);

  //destruirModal();
  //destruirModal();

  //volver a cargar el motor
  //openVendido(id_motor);
}

async function guardarTarea(id = 0) {
  let descripcion = document.getElementById("descripcion" + "_" + id).value;
  let tipo = document.getElementById("tipo" + "_" + id).value;
  let referencia = document.getElementById("referencia" + "_" + id).value;

  //obtener id_parte
  //obtener todos los elementos de la clase modalTitulo
  let modalTitulo = document.getElementsByClassName("modalTitulo");
  let id_parte = modalTitulo[1].id;

  let datos = {
    data: {
      descripcion: descripcion,
      tipo: tipo,
      referencia: referencia,
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
function crearTarea() {
  let inputs = [
    { tipo: "text", id: "descripcion", label: "Descripción", value: "" },
    { tipo: "select", id: "tipo", label: "Tipo", value: "", options: ["", "REPARACION", "REVISION", "SUSTITUCION"] },
    { tipo: "text", id: "referencia", label: "Notas", value: "" },
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
