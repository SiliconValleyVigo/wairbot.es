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

//formadores: id, nombre, dni, telefono, email, observaciones, referencia, timestamp

const camposFormadoresAdministrador = [
  {
    tipo: "text",
    id: "nombre",
    label: "Nombre",
  },
  {
    tipo: "text",
    id: "dni",
    label: "DNI",
  },
  {
    tipo: "text",
    id: "telefono",
    label: "Teléfono",
  },
  {
    tipo: "email",
    id: "email",
    label: "Email",
  },
  {
    tipo: "textarea",
    id: "observaciones",
    label: "Observaciones",
  },
  {
    tipo: "text",
    id: "referencia",
    label: "Referencia",
  }
];

const configFormadoresAdministrador = {
  location: "formadoresAdministrador",
  titulo: "Formadores",
  campos: camposFormadoresAdministrador,
  clase: "Formadores",
  nombreFuncion: "Formadores",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "nombre",
    info2: "dni",
    info3: "telefono",
    info4: "email",
    info5: "referencia",
    id: "id",
    oculto: ""
  },
  mensajes: {
    eliminarTitulo: "Eliminar Accion Formativa",
    eliminarMensaje: "¿Estas Seguro de eliminar este Formador? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Formador Eliminado",
    eliminarExitoMensaje: "El formador ha sido eliminado correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar al Formador pues tiene elementos asociados",
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

const paginaFormadoresAdministrador = new Pagina(configFormadoresAdministrador);

async function getFormadoresAdministrador() {
  await paginaFormadoresAdministrador.get();
}