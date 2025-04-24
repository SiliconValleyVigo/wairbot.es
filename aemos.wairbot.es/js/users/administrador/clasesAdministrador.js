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

//clases: id, id_curso, nombre, fecha, hora_inicio, hora_fin, lugar, observaciones, referencia, timestamp
const camposClasesAdministrador = [
  {
    tipo: "dataListApi",
    id: "id_curso",
    label: "Curso",
    options: "Cursos::listarNombreMasId",
  }, 
  {
    tipo: "dataListApi",
    id: "id_formador",
    label: "Formador",
    options: "Formadores::listarNombreMasId",
  },{
    tipo: "date",
    id: "fecha",
    label: "Fecha",
    defaultValue: ""
  }, {
    tipo: "time",
    id: "hora_inicio",
    label: "Hora Inicio",
    defaultValue: ""
  }, {
    tipo: "time",
    id: "hora_fin",
    label: "Hora Fin",
    defaultValue: ""
  }, {
    tipo: "text",
    id: "lugar",
    label: "Lugar",
    defaultValue: ""
  }, {
    tipo: "text",
    id: "nombre",
    label: "Nombre",
    defaultValue: ""
  }, {
    tipo: "textarea",
    id: "observaciones",
    label: "Observaciones",
    defaultValue: ""
  }, {
    tipo: "text",
    id: "referencia",
    label: "Referencia",
    defaultValue: ""
  }
];

const configClasesAdministrador = {
  location: "ClasesAdministrador",
  titulo: "Clases",
  campos: camposClasesAdministrador,
  clase: "Clases",
  nombreFuncion: "Clases",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "fecha",
    info2: "hora_inicio",
    info3: "hora_fin",
    info4: "lugar",
    info5: "nombre",
    id: "id",
    //oculto: "id_Clase&id_curso" en datosExtraOpen se va a obtener los valores de estos campos para pasar como dato extra
  },
  mensajes: {
    eliminarTitulo: "Eliminar Clase",
    eliminarMensaje: "¿Estas Seguro de eliminar esta Clase? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Clase Eliminado",
    eliminarExitoMensaje: "La Clase ha sido eliminada correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar Clase pues tiene elementos asociados",
  },
  datosExtra: {},
  datosExtraOpen: [],//Array simple, datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
  funcionesExternas: {
    get: function () { console.log("Función externa ejecutada después de get"); },
    modalEliminar: function () { console.log("Función externa ejecutada después de modalEliminar"); },
    eliminar: function (data) { console.log("Función externa ejecutada después de eliminar"); },
    open: function (data) { console.log("Función externa ejecutada después de open"); },
    crear: function () {
      console.log("Función externa ejecutada después de crear");

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
    guardar: function (data) { console.log("Función externa ejecutada después de guardar"); }
  },
  funcionesContenidoOpen: [
    async function (id, data) { }
  ]
};

const paginaClasesAdministrador = new Pagina(configClasesAdministrador);

async function getClasesAdministrador() {
  await paginaClasesAdministrador.get();
}

