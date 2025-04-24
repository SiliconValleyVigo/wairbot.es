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
mensajes_programados: id, id_curso, numero_mensajes, intervalo_dias, fecha_primer_mensaje, hora_envio, mensaje, observaciones_internas, referencia, timestamp
*/

const camposMensajesAdministrador = [
  {
    "tipo": "dataListApi",
    "id": "id_curso",
    "label": "Curso",
    "options": "Cursos::listarNombreMasId",
  },{
    "tipo": "number",
    "id": "numero_mensajes",
    "label": "Número de Mensajes",
  },{
    "tipo": "number",
    "id": "intervalo_dias",
    "label": "Intervalo de Días",
  },{
    "tipo": "date",
    "id": "fecha_primer_mensaje",
    "label": "Fecha Primer Mensaje",
  },{
    "tipo": "time",
    "id": "hora_envio",
    "label": "Hora de Envío",
  },{
    "tipo": "textarea",
    "id": "mensaje",
    "label": "Mensaje",
  },{
    "tipo": "textarea",
    "id": "observaciones_internas",
    "label": "Observaciones Internas",
  },{
    "tipo": "text",
    "id": "referencia",
    "label": "Referencia",
  }
];

const configMensajesAdministrador = {
  location: "mensajesAdministrador",
  titulo: "Mensajes",
  campos: camposMensajesAdministrador,
  clase: "Mensajes",
  nombreFuncion: "Mensajes",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "fecha_primer_mensaje",
    info2: "referencia",
    info3: "id_curso",
    info4: "",
    info5: "",
    id: "id",
    oculto: ""
  },
  mensajes: {
    eliminarTitulo: "Eliminar Mensaje",
    eliminarMensaje: "¿Estas Seguro de eliminar este Mensaje? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Mensaje Eliminado",
    eliminarExitoMensaje: "El mensaje ha sido eliminado correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar el mensaje pues tiene elementos asociados",
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

const paginaMensajesAdministrador = new Pagina(configMensajesAdministrador);

async function getMensajesAdministrador() {
  await paginaMensajesAdministrador.get();
}