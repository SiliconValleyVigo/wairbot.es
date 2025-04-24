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
      },
      "onchange": "actualizarPais"
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
TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, id_naviera, naviera, identificador_naviera, fecha_embarque(fecha), fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp

    navieras: id, nombre, accesos(json), referencia, notas(textarea), timestamp
*/

const camposComprasAdministrador = [
  {
    "tipo": "date",
    "id": "fecha",
    "label": "Fecha",
  },
  {
    "tipo": "dataListApi",
    "id": "id_proveedor",
    "label": "Proveedor",
    "options": "Proveedores::listarNombreMasId"
  },
  {
    "tipo": "text",
    "id": "n_compra",
    "label": "Nº Compra",
  },
  {
    "tipo": "select",
    "id": "seguro",
    "label": "Seguro",
    "options": {
      "SI": "SI",
      "NO": "NO",
      "PENDIENTE": "PENDIENTE"
    }
  },
  {
    "tipo": "textarea",
    "id": "lc",
    "label": "LC"
  },
  {
    "tipo": "textarea",
    "id": "etiquetas",
    "label": "Etiquetas"
  },
  {
    "tipo": "textarea",
    "id": "documentos",
    "label": "Documentos"
  },
  {
    "tipo": "text",
    "id": "referencia",
    "label": "Referencia"
  },
  {
    "tipo": "textarea",
    "id": "notas",
    "label": "Notas"
  }
];

const configComprasAdministrador = {
  location: "ComprasAdministrador",
  titulo: "Compras",
  campos: camposComprasAdministrador,
  clase: "Compras",
  nombreFuncion: "Compras",
  rol: "ADMINISTRADOR",
  funcionLeer: "listar",
  funcionModificadoraDeTabla: "",
  camposCard: {
    info1: "n_compra",
    info2: "fecha",
    info3: "referencia",
    info4: "",
    info5: "",
    id: "id",
    //oculto: "id_Clase&id_curso" en datosExtraOpen se va a obtener los valores de estos campos para pasar como dato extra
  },
  mensajes: {
    eliminarTitulo: "Eliminar Registro",
    eliminarMensaje: "¿Estas Seguro de eliminar Registro? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
    eliminarExitoTitulo: "Registro Eliminado",
    eliminarExitoMensaje: "Registro ha sido eliminado correctamente",
    eliminarErrorTitulo: "Error",
    eliminarErrorMensaje: "No es posible eliminar Registro pues tiene elementos asociados",
  },
  datosExtra: {},
  datosExtraOpen: [],//Array simple, datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
  funcionesExternas: {
    get: function () { console.log("Función externa ejecutada después de get"); },
    modalEliminar: function () { console.log("Función externa ejecutada después de modalEliminar"); },
    eliminar: function (data) { console.log("Función externa ejecutada después de eliminar"); },
    open: function (data) { console.log("Función externa ejecutada después de open"); },
    crear: function () { console.log("Función externa ejecutada después de crear"); },
    guardar: function (data) { console.log("Función externa ejecutada después de guardar"); }
  },
  funcionesContenidoOpen: [
    async function (id, data) {
      //contenedores por compra
      let configContenedoresPorCompra = configContenedoresAdministrador;
      configContenedoresPorCompra.location = "contenedoresAdministrador";
      configContenedoresPorCompra.funcionLeer = "listarPorCompra";
      configContenedoresPorCompra.returnHtml = true;
      configContenedoresPorCompra.dataFuncionLeerHtml = {id_compra: id};
      const paginaContenedoresPorCompra = new Pagina(configContenedoresPorCompra);
      return await paginaContenedoresPorCompra.get();
    }
  ]
};

const paginaComprasAdministrador = new Pagina(configComprasAdministrador);

async function getComprasAdministrador() {
  await paginaComprasAdministrador.get();
}

/*
//EJEMPLO FUNCIONES DE CONTENIDO OPEN

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
*/