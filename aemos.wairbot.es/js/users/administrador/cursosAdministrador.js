/*
Ejemplo de JSON para crear campos de formulario
{
  "campos": [
    {
      "tipo": "text",
      "id": "nombre",
      "label": "Nombre",
      "defaultValue": "Juan Pérez"
    },
    {
      "tipo": "dataList",
      "id": "ciudad",
      "label": "Ciudad",
      "options": ["Madrid", "Barcelona", "Valencia"],
      "onchange": "actualizarCiudad"
    },
    {
      "tipo": "textarea",
      "id": "descripcion",
      "label": "Descripción",
      "defaultValue": "Descripción del producto"
    },
    {
      "tipo": "file",
      "id": "archivo",
      "label": "Subir Archivo"
    },
    {
      "tipo": "select",
      "id": "pais",
      "label": "País",
      "options": {
        "es": "España",
        "fr": "Francia",
        "it": "Italia"
      }
    },
    {
      "tipo": "phone_link",
      "id": "telefono",
      "label": "Teléfono",
      "defaultValue": "123456789"
    },
    {
      "tipo": "date",
      "id": "fecha",
      "label": "Fecha",
      "defaultValue": "2023-01-01"
    },
    {
      "tipo": "dataListApi",
      "id": "producto",
      "label": "Producto",
      "options": "ProductoApi::listarNombreMasId"
    },
    {
      "tipo": "dataListCompleto",
      "id": "categoria",
      "label": "Categoría",
      "options": "categoria::categorias" //nombre_select::tabla_select
    },
    {
      "tipo": "number",
      "id": "cantidad",
      "label": "Cantidad",
      "defaultValue": 1
    },
    {
      "tipo": "time",
      "id": "hora",
      "label": "Hora",
      "defaultValue": "12:00"
    },
    {
      "tipo": "json",
      "id": "json_date", //por el momento solo se puede usar json_date
      "label": "Fechas JSON",
      "defaultValue": "[\"2023-01-01\", \"2023-02-01\"]"
    },
    {
      "tipo": "coin",
      "id": "precio",
      "label": "Precio",
      "defaultValue": "10.00"
    }
  ],
  "botonEliminar": {
    "texto": "Eliminar",
    "funcion": "eliminarCampo",
    "id": "btnEliminar"
  }
}
*/

const camposCursosAdministrador = [
    {
        tipo: "text",
        id: "nombre",
        label: "Nombre",
    },{
        tipo: "number",
        id: "horas",
        label: "Horas",
    },{
        tipo: "select",
        id: "financiacion",
        label: "Financiación",
        options: {
            NORMAL: "Normal",
            SEPE: "SEPE",
        }
    },{
        tipo: "number",
        id: "previsto",
        label: "Previsto",
    },{
        tipo: "number",
        id: "asistencias",
        label: "Asistencias",
    },{
        tipo: "number",
        id: "ausencias",
        label: "Ausencias",
    },{
        tipo: "number",
        id: "horas_formacion",
        label: "Horas de Formación",
    },{
        tipo: "number",
        id: "balance",
        label: "Balance",
    },{
        tipo: "number",
        id: "numero_clases",
        label: "Número de Clases",
    },{
        tipo: "text",
        id: "fechas",
        label: "Fechas",
    },{
        tipo: "text",
        id: "horario",
        label: "Horario",
    },{
        tipo: "text",
        id: "lugar",
        label: "Lugar",
    },{
        tipo: "textarea",
        id: "observaciones_publicas",
        label: "Observaciones Públicas",
    },{
        tipo: "textarea",
        id: "observaciones_internas",
        label: "Observaciones Internas",
    },{
        tipo: "textarea",
        id: "descripcion",
        label: "Descripción",
    },{
        tipo: "text",
        id: "referencia",
        label: "Referencia",
    },{
        tipo: "date",
        id: "fecha_inicio",
        label: "Fecha de Inicio",
    }
];

//cursos: id, nombre (unico), horas, financiacion(NORMAL, SEPE), previsto, asistencias, ausencias, horas_formacion, balance, numero_clases, fechas, horario, lugar, observaciones_publicas, observaciones_internas, descripcion, referencia, fecha_inicio, timestamp

const configCursosAdministrador = {
    location: "cursosAdministrador",
    titulo: "Cursos",
    campos: camposCursosAdministrador,
    clase: "Cursos",
    nombreFuncion: "Cursos",
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "nombre",
        info2: "fecha",
        info3: "horario",
        info4: "financiacion",
        info5: "referencia",
        id: "id",
        //oculto: "id_alumno&id_curso" en datosExtraOpen se va a obtener los valores de estos campos para pasar como dato extra
    },
    mensajes: {
        eliminarTitulo: "Eliminar Curso",
        eliminarMensaje: "¿Estas Seguro de eliminar este Curso? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Curso Eliminado",
        eliminarExitoMensaje: "El Curso ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar el Curso pues tiene elementos asociados",
    },
    datosExtra: {},
    datosExtraOpen: [],//Array simple, datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
    funcionesExternas: {
        get: function() { console.log("Función externa ejecutada después de get");},
        modalEliminar: function() { console.log("Función externa ejecutada después de modalEliminar");},
        eliminar: function(data) { console.log("Función externa ejecutada después de eliminar", data);},
        open: function(data) { console.log("Función externa ejecutada después de open", data);},
        crear: function() { console.log("Función externa ejecutada después de crear"); },
        guardar: function(data) { console.log("Función externa ejecutada después de guardar", data);}
    },
    funcionesContenidoOpen: [
      async function(id, data) {
        //acciones formativas por curso
        let clase = "Acciones";
        let funcion = "obtenerAccionesFormativasPorCurso";
        let dataQuery = {id_curso: id};
        let acciones = await queryAsync(clase, funcion, dataQuery);
        acciones = acciones.data;
        console.log(acciones);

        //si acciones es un array vacío
        if (Object.keys(acciones).length === 0) {
          //botón para generar acciones formativas
          return botonGenerarAccionesFormativas(id);
        }else{
          return controladorAccionesFormativas(acciones);
        }
      },
      async function(id, data) {
          //inscripciones por alumno
          let configInscripcionesPorCurso = configInscripcionesAdministrador;
          configInscripcionesPorCurso.location = "inscripcionesAdministrador";
          configInscripcionesPorCurso.funcionLeer = "listarPorCurso";
          configInscripcionesPorCurso.returnHtml = true;
          configInscripcionesPorCurso.dataFuncionLeerHtml = {id_curso: id};
          configInscripcionesPorCurso.campos = [
            {
              "tipo": "dataListApi",
              "id": "id_alumno",
              "label": "Alumno",
              "options": "Alumnos::listarNombreMasId"
            },{
              "tipo": "text",
              "id": "ALU_apellidos",
              "label": "Apellidos",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_dni",
              "label": "DNI",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_telefono",
              "label": "Teléfono",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_email",
              "label": "Email",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_sexo",
              "label": "Sexo",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_colectivo",
              "label": "Colectivo",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_grupo",
              "label": "Grupo",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_lugar",
              "label": "Lugar",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_trabajo",
              "label": "Trabajo",
              "disabled": true
            },{
              "tipo": "textarea",
              "id": "ALU_observaciones",
              "label": "Observaciones",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_carnet_conducir",
              "label": "Carnet de Conducir",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_vehiculo",
              "label": "Vehículo",
              "disabled": true
            },{
              "tipo": "text",
              "id": "ALU_referencia",
              "label": "Referencia",
              "disabled": true
            },{
              "tipo": "dataListApi",
              "id": "id_curso",
              "label": "Curso",
              "options": "Cursos::listarNombreMasId"
            },{
              "tipo": "select",
              "id": "estado",
              "label": "Estado",
              "options": {
                "PROPUESTO": "PROPUESTO",
                "SOLICITADO": "SOLICITADO",
                "ACEPTADO": "ACEPTADO",
                "CURSANDO": "CURSANDO",
                "RECHAZADO": "RECHAZADO",
                "FINALIZADO": "FINALIZADO"
              }
            }
          ];

          const paginaInscripcionesPorCurso = new Pagina(configInscripcionesPorCurso);
          return await paginaInscripcionesPorCurso.get();
      },
      async function(id, data) {
          //clases por curso
          let configClasesPorCurso = configClasesAdministrador;
          configClasesPorCurso.location = "clasesAdministrador";
          configClasesPorCurso.funcionLeer = "listarPorCurso";
          configClasesPorCurso.returnHtml = true;
          configClasesPorCurso.dataFuncionLeerHtml = {id_curso: id};
          const paginaClasesPorCurso = new Pagina(configClasesPorCurso);
          return await paginaClasesPorCurso.get();
      }
    ]
};

const paginaCursosAdministrador = new Pagina(configCursosAdministrador);

async function getCursosAdministrador() {
  await paginaCursosAdministrador.get();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function controladorAccionesFormativas(acciones){
  /*
  {
    "1": {
      "id_clase": 1,
      "nombre": "Curso 9",
      "fecha": "2024-11-13",
      "hora_inicio": "11:00:00",
      "hora_fin": "12:00:00",
      "lugar": "Lugar Curso 9",
      "observaciones": "Observaciones clase 1 curso 9",
      "acciones": [
        {
          "id": 2,
          "id_alumno": 14,
          "nombre": "nombre 2",
          "apellidos": "Apellido 1 ",
          "asistio": 0,
          "notas": "",
          "curso_igualdad_previo": "NO",
          "entrega_de_material": "NO",
          "alumno_trabajando": "NO"
        }
      ]
    }
  }
  */
  let accionesHtml = "";
  for (let id_clase in acciones) {
    let clase = acciones[id_clase];
    let accionesClase = clase.acciones;
    
    //cabecera de la tabla de acciones
    let cabeceraHtml = `
      <style>
        .tablaAccionesFormativas {
          font-family: Arial, sans-serif;
          font-size: 0.8rem;
          border-collapse: collapse;
          width: 100%;
        }
        .tablaAccionesFormativas th, .tablaAccionesFormativas td {
          border: 1px solid #dddddd;
          text-align: left;
          padding: 8px;
        }
        .tablaAccionesFormativas th {
          background-color: #0a4fff;
          color: white;
          text-align: center;
        }
        .tablaAccionesFormativas tr:nth-child(even) {
          background-color: #f2f2f2;
        }
        .tablaAccionesFormativas tr:nth-child(odd) {
          background-color: #ffffff;
        }
        .tablaAccionesFormativas tr:hover {
          background-color: #dddddd;
        }
        .tablaAccionesFormativas th.nombre {
          width: 20%;
        }
        .tablaAccionesFormativas th.apellidos {
          width: 20%;
        }
        .tablaAccionesFormativas th.asistio {
          width: 8%;
        }
        .tablaAccionesFormativas th.curso_igualdad_previo {
          width: 8%;
        }
        .tablaAccionesFormativas th.entrega_de_material {
          width: 8%;
        }
        .tablaAccionesFormativas th.alumno_trabajando {
          width: 8%;
        }
        .tablaAccionesFormativas th.notas {
          width: 28%;
        }
      </style>
      <table class="tablaAccionesFormativas">
        <tr>
          <th class="nombre">Nombre</th>
          <th class="apellidos">Apellidos</th>
          <th class="asistio">Asistió</th>
          <th class="curso_igualdad_previo">Curso Igualdad</th>
          <th class="entrega_de_material">Entrega Material</th>
          <th class="alumno_trabajando">Alumno Trabajando</th>
          <th class="notas">Notas</th>
        </tr>
    `;

    let cuerpoHtml = "";
    for (let i = 0; i < accionesClase.length; i++) {
      let accion = accionesClase[i];
      let id = accion.id;
      let nombre = accion.nombre;
      let apellidos = accion.apellidos;
      let asistio = accion.asistio;
      let notas = accion.notas;
      let curso_igualdad_previo = accion.curso_igualdad_previo;
      let entrega_de_material = accion.entrega_de_material;
      let alumno_trabajando = accion.alumno_trabajando;

      //cuerpo de la tabla de acciones
      let filaHtml = `
        <tr>
          <td class="nombre">${nombre}</td>
          <td class="apellidos">${apellidos}</td>
          <td class="asistio">${botonSiNoHtml(this, "asistio", asistio, id)}</td>
          <td class="curso_igualdad_previo">${botonSiNoHtml(this, "curso_igualdad_previo", curso_igualdad_previo, id)}</td>
          <td class="entrega_de_material">${botonSiNoHtml(this, "entrega_de_material", entrega_de_material, id)}</td>
          <td class="alumno_trabajando">${botonSiNoHtml(this, "alumno_trabajando", alumno_trabajando, id)}</td>
          <td class="notas" contenteditable="true" onblur="actualizarNotas(${id}, this.innerHTML)">${notas}</td>
        </tr>
      `;
      cuerpoHtml += filaHtml;
    }

    //pie de la tabla de acciones
    let pieHtml = `
      </table>
    `;

    let accionesClaseHtml = cabeceraHtml + cuerpoHtml + pieHtml;

    let claseHtml = `
      <style>
        .claseControl {
          width: calc(100% - 22px);
          margin-top: 10px;
          background-color: white;
          border: 1px solid #dddddd;
          border-radius: 5px;
          padding: 10px;
        }
        .claseControl summary {
          font-family: Arial, sans-serif;
          color: black;
          font-weight: bold;
          cursor: pointer;
        }
        .claseControlInfo {
          font-family: Arial, sans-serif;
          color: black;
          margin-top: 10px;
          margin-left: 10px;
        }
        .claseControlAcciones {
          margin-top: 10px;
        }
        .claseControlObservaciones {
          width: 100%;
          height: 100px;
          margin-top: 10px;
        }
        .claseControl .botonGenerarParteAsistencia {
          width: 100%;
          margin-top: 10px;
          padding: 10px;
          background-color: #0a4fff;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
        }
      </style>
      <details class="claseControl">
        <summary>${clase.nombre}</summary>
        <div class="claseControlInfo">
          ${clase.fecha}  |  <b>Horario:</b> ${clase.hora_inicio} - ${clase.hora_fin}  |  <b>Lugar:</b> ${clase.lugar}
        </div>
        <div class="claseControlAcciones">
          ${accionesClaseHtml}
        </div>
        <textarea class="claseControlObservaciones" id="observaciones_clase_${id_clase}" onblur="actualizarObservacionesClase(${id_clase}, this.value)">${clase.observaciones}</textarea>
        <button class="botonGenerarParteAsistencia" onclick="generarParteAsistencia(${id_clase})">Generar Parte de Asistencia</button>
      </details>
    `;

    accionesHtml += claseHtml;
  }

  let titulo = `
    <div class="tituloGrid">Acciones Formativas</div>
  `;

  return titulo + accionesHtml;
}

function botonGenerarAccionesFormativas(id){
  let botonHtml = `
    <style>
      .botonAccionesFormativasContainer {
        margin-top: 20px;
        width: 100%;
        display: flex;
        justify-content: center;
      }
      .botonAccionesFormativas {
        background-color: #4CAF50;
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

      .botonAccionesFormativas:hover {
        background-color: #45a049;
      }
    </style>
    <div class="botonAccionesFormativasContainer">
      <button class="botonAccionesFormativas" onclick="generarAccionesFormativas(${id})">Generar Acciones Formativas</button>
    </div>
  `;

  return botonHtml;
}

async function generarAccionesFormativas(id_curso) {
  let clase = "Acciones";
  let funcion = "crearAccionesFormativas";
  let data = {id_curso: id_curso};

  await queryAsync(clase, funcion, data);

  //obtener acciones formativas
  let acciones = await queryAsync(clase, "obtenerAccionesFormativasPorCurso", {id_curso: id_curso});

  //cambiar el botón por las acciones formativas por las acciones formativas
  let accionesHtml = controladorAccionesFormativas(acciones.data);
  let divAcciones = document.querySelector(".botonAccionesFormativasContainer");
  divAcciones.replaceWith(document.createRange().createContextualFragment(accionesHtml));

  getCursosAdministrador();
}

function botonSiNoHtml(thisElement, key, valor, id_accion) {
  let color = "red";
  let texto = "NO";
  if (valor == 1 || valor == "SI") {
    color = "green";
    texto = "SI";
  }

  let botonHtml = `
    <style>
      .botonSiNo {
        border: none;
        color: white;
        padding: 5px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 1rem;
        margin: 2px;
        cursor: pointer;
      }
    </style>
    <button 
      class="botonSiNo"
      onclick="actualizarValorBotonSiNo(event, '${key}', '${valor}', ${id_accion})"
      style="background-color: ${color};"
    >
      ${texto}
    </button>
  `;
  return botonHtml;
}

async function actualizarValorBotonSiNo(e, key, value, id_accion) {
  let newValue = 0;
  if (value == 0) {
    newValue = 1;
  }else if (value == 1) {
    newValue = 0;
  }else if (value == "SI") {
    newValue = "NO";
  }else if (value == "NO") {
    newValue = "SI";
  }

  let colorNew = "red";
  let textoNew = "NO";
  if (newValue == 1 || newValue == "SI") {
    colorNew = "green";
    textoNew = "SI";
  }

  e.target.style.backgroundColor = colorNew;
  e.target.innerHTML = textoNew;

  let clase = "Acciones";
  let funcion = "editar";
  
  //hacer key: value, id: id_accion
  let data = {
    id: id_accion,
    data: {[key]: newValue}
  };
  let res = await queryAsync(clase, funcion, data);
  console.log(res);
}

//actualizar notas
async function actualizarNotas(id_accion, notas) {
  let clase = "Acciones";
  let funcion = "editar";
  let data = {
    id: id_accion,
    data: {notas: notas}
  };
  let res = await queryAsync(clase, funcion, data);
  console.log(res);
}

//actualizar observaciones de clase
async function actualizarObservacionesClase(id_clase, observaciones) {
  let clase = "Clases";
  let funcion = "editar";
  let data = {
    id: id_clase,
    data: {observaciones: observaciones}
  };
  let res = await queryAsync(clase, funcion, data);
  console.log(res);
}

//generar parte de asistencia
async function generarParteAsistencia(id_clase) {
  console.log("Generar parte de asistencia", id_clase);

  let clase = "PartesAsistencia";
  let funcion = "getPdfBase64";

  let data = {id: id_clase};

  let res = await queryAsync(clase, funcion, data);

  let pdfBase64 = res.pdfBase64;

  // Convertir base64 a array de bytes
  let byteCharacters = atob(pdfBase64);
  let byteNumbers = new Array(byteCharacters.length);
  for (let i = 0; i < byteCharacters.length; i++) {
    byteNumbers[i] = byteCharacters.charCodeAt(i);
  }
  let byteArray = new Uint8Array(byteNumbers);

  // Crear Blob a partir del array de bytes
  let pdfBlob = new Blob([byteArray], {type: "application/pdf"});

  let pdfUrl = URL.createObjectURL(pdfBlob);
  window.open(pdfUrl);  
}