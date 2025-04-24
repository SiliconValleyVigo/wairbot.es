/*
Ejemplo de JSON para crear campos de formulario
{
  "campos": [
    {
      "tipo": "text",
      "id": "nombre",
      "label": "Nombre",
      "defaultValue": "Juan Pérez"
      "disabled": true
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

//Inscripciones: id, id_alumno, id_curso, estado(PROPUESTO, SOLICITADO, ACEPTADO, CURSANDO, RECHAZADO, FINALIZADO), timestamp

/*
{
    "id": "1",
    "id_alumno": "9",
    "id_curso": "2",
    "estado": "PROPUESTO",
    "timestamp": "2024-11-11 13:22:02",
    "ALU_id": "9",
    "ALU_numero": "1",
    "ALU_apellidos": "Apellido 1 ",
    "ALU_nombre": "nombre 1",
    "ALU_dni": "dni1",
    "ALU_telefono": "34600857882",
    "ALU_fecha_nacimiento": "2001-01-01",
    "ALU_fecha_seleccion": "2002-01-01",
    "ALU_fecha_baja": "2003-01-01",
    "ALU_persona_sustituta": "persona 1",
    "ALU_email": "email1@gmail.com",
    "ALU_sexo": "H",
    "ALU_colectivo": "1",
    "ALU_grupo": "1",
    "ALU_lugar": "lugar 1",
    "ALU_trabajo": "trabajo 1",
    "ALU_observaciones": "Observaciones 1\n        ",
    "ALU_numero_cuenta": "111111111111",
    "ALU_orientador": "orientador 1",
    "ALU_carnet_conducir": "si",
    "ALU_vehiculo": "si",
    "ALU_mensajes_enviados": null,
    "ALU_referencia": "1",
    "ALU_timestamp": "2024-11-11 07:03:29",
    "CUR_id": "2",
    "CUR_nombre": "Curso 1",
    "CUR_horas": "10",
    "CUR_financiacion": "NORMAL",
    "CUR_previsto": "10",
    "CUR_asistencias": "10",
    "CUR_ausencias": "10",
    "CUR_horas_formacion": "10",
    "CUR_balance": "10",
    "CUR_numero_clases": "1",
    "CUR_fechas": "fechas curso 1",
    "CUR_horario": "horario curso 1",
    "CUR_lugar": "Lugar Curso 1",
    "CUR_observaciones_publicas": "Observaciones públicas curso 1            \n        ",
    "CUR_observaciones_internas": "Observaciones internas curso 1            \n        ",
    "CUR_descripcion": "Descripción del curso 1      \n        ",
    "CUR_referencia": "Referencia curso 1",
    "CUR_fecha_inicio": "2026-06-11",
    "CUR_timestamp": "0000-00-00 00:00:00"
}
*/

/*
ALU_apellidos
ALU_dni
ALU_telefono
ALU_email
ALU_sexo
ALU_colectivo
ALU_grupo
ALU_lugar
ALU_trabajo
ALU_observaciones
ALU_carnet_conducir
ALU_vehiculo
ALU_referencia

CUR_nombre
CUR_horas
CUR_financiacion
CUR_lugar
CUR_observaciones_publicas
CUR_observaciones_internas
CUR_descripcion
CUR_referencia
CUR_fecha_inicio
 */

const camposInscripcionesAdministrador = [
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
    "tipo": "text",
    "id": "CUR_nombre",
    "label": "Nombre",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_horas",
    "label": "Horas",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_financiacion",
    "label": "Financiación",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_lugar",
    "label": "Lugar",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_observaciones_publicas",
    "label": "Observaciones Públicas",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_observaciones_internas",
    "label": "Observaciones Internas",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_descripcion",
    "label": "Descripción",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_referencia",
    "label": "Referencia",
  },{
    "tipo": "date",
    "id": "CUR_fecha_inicio",
    "label": "Fecha de Inicio",
    "disabled": true
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

const configInscripcionesAdministrador = {
  location: "inscripcionesAdministrador",
  titulo: "Inscripciones",
  campos: camposInscripcionesAdministrador,
  clase: "Inscripciones",
  nombreFuncion: "Inscripciones",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "ALU_nombre",
    info2: "ALU_apellidos",
    info3: "CUR_nombre",
    info4: "CUR_fecha_inicio",
    info5: "estado",
    id: "id",
    oculto: "id_alumno&id_curso"
  },
  mensajes: {
    eliminarTitulo: "Eliminar Inscripcion",
    eliminarMensaje: "¿Estas Seguro de eliminar esta Inscripcion? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Inscripcion Eliminada",
    eliminarExitoMensaje: "La Inscripcion ha sido eliminada correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar la Inscripcion pues tiene elementos asociados",
  },
  datosExtra: {},
  datosExtraOpen: ['id_alumno', 'id_curso'],//datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
  funcionesExternas: {
    get: function () { console.log("Función externa ejecutada después de get"); },
    modalEliminar: function () { console.log("Función externa ejecutada después de modalEliminar"); },
    eliminar: function (data) { console.log("Función externa ejecutada después de eliminar", data); },
    open: function (data) { console.log("Función externa ejecutada después de open", data); },
    crear: function () {
      console.log("Función externa ejecutada después de crear");
      //obtener todos los inputs
      let inputs = document.querySelectorAll("input");

      //ocultar los padres de los inputs donde su id comienzan por ALU_ o CUR_
      inputs.forEach(input => {
        if (input.id.startsWith("ALU_") || input.id.startsWith("CUR_")) {
          input.parentElement.remove();
        }
      });

      //hacer lo mismo con los textareas
      let textareas = document.querySelectorAll("textarea");
      textareas.forEach(textarea => {
        if (textarea.id.startsWith("ALU_") || textarea.id.startsWith("CUR_")) {
          textarea.parentElement.remove();
        }
      });

      //esperar a que se cargue el datalist con id id_alumno_list
      
      let cursoId = getIdModalPadre();
      //si cursoId no es null o "null"
      //añadir el id del curso al input id_curso
      if (cursoId !== null && cursoId !== "null") {
        //obtener los shadowRoots de los data-search y obtener el datalist con id id_alumno_list
        let shadowRoots = document.querySelectorAll("data-search");

        //obtener el data-search con el id id_curso y cambiar el atributo default-value por el cursoId
        shadowRoots.forEach(shadowRoot => {
          if (shadowRoot.id === "id_curso") {
            shadowRoot.setAttribute("default-value", cursoId);
          }
        });
      }
    },
    guardar: function (data) { console.log("Función externa ejecutada después de guardar", data); }
  },
  funcionesContenidoOpen: [
    async function (id, data) {
      console.log({ data });
    }
  ]
};

const paginaInscripcionesAdministrador = new Pagina(configInscripcionesAdministrador);

async function getInscripcionesAdministrador() {
  await paginaInscripcionesAdministrador.get();
}

/////////////////////////
