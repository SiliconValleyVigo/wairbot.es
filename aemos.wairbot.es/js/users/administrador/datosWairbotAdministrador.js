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
data: name, url_file, text, category, role, user_phone, 
*/

const camposWairbotDataAdministrador = [
  {
    tipo: "text",
    id: "name",
    label: "Nombre",
  },{
    tipo: "file",
    id: "url_file",
    label: "Archivo",
  },{
    tipo: "textarea",
    id: "text",
    label: "Texto",
  },{
    tipo: "dataListApi",
    id: "category",
    label: "Categoría",
    options: "WairbotCategory::listarNombreMasId"
  },{
    tipo: "dataListApi",
    id: "role",
    label: "Rol",
    options: "WairbotCategory::listarNombreMasIdRoles"
  },{
    tipo: "dataListApi",
    id: "user_phone",
    label: "Alumno",
    options: "Alumnos::listarNombreMasId",
  }
];

const configWairbotDataAdministrador = {
  location: "wairbotDataAdministrador",
  titulo: "Datos Wairbot",
  campos: camposWairbotDataAdministrador,
  clase: "WairbotData",
  nombreFuncion: "WairbotData",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "name",
    info2: "",
    info3: "",
    info4: "",
    info5: "",
    id: "id",
    oculto: ""
  },
  mensajes: {
    eliminarTitulo: "Eliminar Datos Wairbot",
    eliminarMensaje: "¿Estas Seguro de eliminar este Dato? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Dato eliminado",
    eliminarExitoMensaje: "El dato ha sido eliminado correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar el Dato pues tiene elementos asociados",
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

const paginaWairbotDataAdministrador = new Pagina(configWairbotDataAdministrador);

async function getWairbotDataAdministrador() {
  await paginaWairbotDataAdministrador.get();
}