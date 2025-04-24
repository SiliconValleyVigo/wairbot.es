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

/*
cita_previa: id, id_alumno, nombre, telefono, disponibilidad, timestamp
*/

async function getCamposControlAdministrador() {
  let camposControlAdministrador = [
    {
      tipo: "html",
      id: "alumno_nombre",
      label: "Alumno",
    }
  ];

  //obtener todos los cursos
  let clase = "Cursos";
  let funcion = "listarNombreMasId"
  let data = {};

  let cursos = await queryAsync(clase, funcion, data);
  cursos = cursos.data;
  console.log(cursos);
  //recorrer con key y value
  for (const key in cursos) {
    let idCampo = cursos[key];
    //todo en minúsculas
    idCampo = idCampo.toLowerCase();
    //cambiar espacio por _
    idCampo = idCampo.replace(" ", "_");
    let value = cursos[key];
    camposControlAdministrador.push({
      tipo: "html",
      id: idCampo,
      label: value
    });
  }

  console.log({camposControlAdministrador});

  return camposControlAdministrador;
}

const camposControlAdministrador = [
  {
    tipo: "html",
    id: "alumno_nombre",
    label: "Alumno",
    funcionRestoDeDatos: "getCamposControlAdministrador"
  }
];

const configControlAdministrador = {
  location: "controlAdministrador",
  titulo: "Cita Previa",
  campos: camposControlAdministrador,
  clase: "Control",
  nombreFuncion: "Control",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "alumno_nombre",
    info2: "",
    info3: "",
    info4: "",
    info5: "",
    id: "id",
    oculto: ""
  },
  mensajes: {
    eliminarTitulo: "Eliminar Cita Previa",
    eliminarMensaje: "¿Estas Seguro de eliminar esta Cita Previa? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Cita Previa Eliminada",
    eliminarExitoMensaje: "La Cita Previa ha sido eliminada correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar la Cita Previa pues tiene elementos asociados",
  },
  datosExtra: {},
  datosExtraOpen: [],//datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
  funcionesExternas: {
    get: function () { console.log("Función externa ejecutada después de get"); },
    modalEliminar: function () { console.log("Función externa ejecutada después de modalEliminar"); },
    eliminar: function (data) { console.log("Función externa ejecutada después de eliminar", data); },
    open: function (data) { console.log("Función externa ejecutada después de open", data); },
    crear: function () { console.log("Función externa ejecutada después de crear"); },
    guardar: function (data) { console.log("Función externa ejecutada después de guardar", data); }
  },
  funcionesContenidoOpen: [
    async function (id, data) {
      console.log({ data });
    }
  ]
};

const paginaControlAdministrador = new Pagina(configControlAdministrador);

async function getControlAdministrador() {
  await paginaControlAdministrador.get();
}