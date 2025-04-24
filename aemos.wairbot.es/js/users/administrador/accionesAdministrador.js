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

//acciones_formativas: id, id_alumno, id_curso, id_clase, asistio(SI, NO), notas, curso_igualdad_previo(SI, NO), entrega_de_material(SI, NO), alumno_trabajando(SI, NO)

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

CLA_nombre
CLA_fecha
CLA_hora_inicio
CLA_hora_fin
CLA_lugar
CLA_observaciones
CLA_referencia

 */

const camposAccionesAdministrador = [
  {
    "tipo": "dataListApi",
    "id": "id_alumno",
    "label": "Nombre Alumno",
    "options": "Alumnos::listarNombreMasId"
  },{
    "tipo": "text",
    "id": "ALU_apellidos",
    "label": "Apellidos Alumno",
    "disabled": true
  },{
    "tipo": "dataListApi",
    "id": "id_curso",
    "label": "Curso",
    "options": "Cursos::listarNombreMasId"
  },{
    "tipo": "dataListApi",
    "id": "id_clase",
    "label": "Clase",
    "options": "Clases::listarNombreMasId"
  },{
    "tipo": "select",
    "id": "asistio",
    "label": "Asistió",
    "options": {
      "NO": "NO",
      "SI": "SI"
    }
  },{
    "tipo": "textarea",
    "id": "notas",
    "label": "Notas"
  },{
    "tipo": "select",
    "id": "curso_igualdad_previo",
    "label": "Curso Igualdad Previo",
    "options": {
      "NO": "NO",
      "SI": "SI"
    }
  },{
    "tipo": "select",
    "id": "entrega_de_material",
    "label": "Entrega de Material",
    "options": {
      "NO": "NO",
      "SI": "SI"
    }
  },{
    "tipo": "select",
    "id": "alumno_trabajando",
    "label": "Alumno Trabajando",
    "options": {
      "NO": "NO",
      "SI": "SI"
    }
  },{
    "tipo": "text",
    "id": "ALU_dni",
    "label": "DNI Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_telefono",
    "label": "Teléfono Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_email",
    "label": "Email Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_sexo",
    "label": "Sexo Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_colectivo",
    "label": "Colectivo Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_grupo",
    "label": "Grupo Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_lugar",
    "label": "Lugar Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "ALU_trabajo",
    "label": "Trabajo Alumno",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "ALU_observaciones",
    "label": "Observaciones Alumno",
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
    "label": "Referencia Alumno",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_nombre",
    "label": "Nombre Curso",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_horas",
    "label": "Horas",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_financiacion",
    "label": "Financiación Curso",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_lugar",
    "label": "Lugar Curso",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_observaciones_publicas",
    "label": "Observaciones Públicas Curso",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_observaciones_internas",
    "label": "Observaciones Internas Curso",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CUR_descripcion",
    "label": "Descripción Curso",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_referencia",
    "label": "Referencia",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CUR_fecha_inicio",
    "label": "Fecha de Inicio",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_nombre",
    "label": "Nombre",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_fecha",
    "label": "Fecha",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_hora_inicio",
    "label": "Hora de Inicio",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_hora_fin",
    "label": "Hora de Fin",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_lugar",
    "label": "Lugar",
    "disabled": true
  },{
    "tipo": "textarea",
    "id": "CLA_observaciones",
    "label": "Observaciones",
    "disabled": true
  },{
    "tipo": "text",
    "id": "CLA_referencia",
    "label": "Referencia",
    "disabled": true
  }
];

const configAccionesAdministrador = {
  location: "accionesAdministrador",
  titulo: "Acciones Formativas",
  campos: camposAccionesAdministrador,
  clase: "Acciones",
  nombreFuncion: "Acciones",
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
    oculto: "id_alumno&id_curso&id_clase"
  },
  mensajes: {
    eliminarTitulo: "Eliminar Accion Formativa",
    eliminarMensaje: "¿Estas Seguro de eliminar esta Accion Formativa? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Accion Formativa Eliminada",
    eliminarExitoMensaje: "La Accion Formativa ha sido eliminada correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar la Accion Formativa pues tiene elementos asociados",
  },
  datosExtra: {},
  datosExtraOpen: ['id_alumno', 'id_curso', 'id_clase'],//datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
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
        if (input.id.startsWith("ALU_") || input.id.startsWith("CUR_") || input.id.startsWith("CLA_")) {
          input.parentElement.remove();
        }
      });

      //hacer lo mismo con los textareas
      let textareas = document.querySelectorAll("textarea");
      textareas.forEach(textarea => {
        if (textarea.id.startsWith("ALU_") || textarea.id.startsWith("CUR_") || textarea.id.startsWith("CLA_")) {
          textarea.parentElement.remove();
        }
      });
    },
    guardar: function (data) { console.log("Función externa ejecutada después de guardar", data); }
  },
  funcionesContenidoOpen: [
    async function (id, data) {
      console.log({ data });
    }
  ]
};

const paginaAccionesAdministrador = new Pagina(configAccionesAdministrador);

async function getAccionesAdministrador() {
  await paginaAccionesAdministrador.get();
}