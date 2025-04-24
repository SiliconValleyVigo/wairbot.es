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

//alumnos: id, numero, apellidos, nombre, dni, telefono, fecha_nacimiento, fecha_seleccion, fecha_baja, persona_sustituta, email, sexo, colectivo, grupo, lugar, trabajo, observaciones, numero_cuenta, orientador, carnet-conducir, vehiculo, mensajes_enviados, referencia, timestamp
const camposAlumnosAdministrador = [
    {
        tipo: "text",
        id: "numero",
        label: "Número",
    },{
        tipo: "text",
        id: "apellidos",
        label: "Apellidos",
    },{
        tipo: "text",
        id: "nombre",
        label: "Nombre",
    },{
        tipo: "text",
        id: "dni",
        label: "DNI",
    },{
        tipo: "text",
        id: "telefono",
        label: "Teléfono",
    },{
        tipo: "date",
        id: "fecha_nacimiento",
        label: "Fecha de Nacimiento",
    },{
        tipo: "date",
        id: "fecha_seleccion",
        label: "Fecha de Selección",
    },{
        tipo: "date",
        id: "fecha_baja",
        label: "Fecha de Baja",
    },{
        tipo: "text",
        id: "persona_sustituta",
        label: "Persona Sustituta",
    },{
        tipo: "text",
        id: "email",
        label: "Email",
    },{
        tipo: "select",
        id: "sexo",
        label: "Sexo",
        options: {
            "H": "Hombre",
            "M": "Mujer",
            "nsnc": "NS/NC"
        }
    },{
        tipo: "text",
        id: "colectivo",
        label: "Colectivo",
    },{
        tipo: "text",
        id: "grupo",
        label: "Grupo",
    },{
        tipo: "text",
        id: "lugar",
        label: "Lugar",
    },{
        tipo: "text",
        id: "trabajo",
        label: "Trabajo",
    },{
        tipo: "textarea",
        id: "observaciones",
        label: "Observaciones",
    },{
        tipo: "text",
        id: "numero_cuenta",
        label: "Número de Cuenta",
    },{
        tipo: "text",
        id: "orientador",
        label: "Orientador",
    },{
        tipo: "select",
        id: "carnet_conducir",
        label: "Carnet",
        options: {
            "si": "Sí",
            "no": "No",
        }
    },{
        tipo: "select",
        id: "vehiculo",
        label: "Vehículo",
        options: {
            "si": "Sí",
            "no": "No",
        }
    },{
        tipo: "text",
        id: "referencia",
        label: "Referencia",
    },{
        tipo: "textarea",
        id: "historial",
        label: "Historial",
    }
];

const configAlumnosAdministrador = {
    location: "alumnosAdministrador",
    titulo: "Alumnos",
    campos: camposAlumnosAdministrador,
    clase: "Alumnos",
    nombreFuncion: "Alumnos",
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "nombre",
        info2: "apellidos",
        info3: "telefono",
        info4: "email",
        info5: "numero",
        id: "id",
        //oculto: "id_alumno&id_curso" en datosExtraOpen se va a obtener los valores de estos campos para pasar como dato extra
    },
    mensajes: {
        eliminarTitulo: "Eliminar Alumno",
        eliminarMensaje: "¿Estas Seguro de eliminar este Alumno? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Alumno Eliminado",
        eliminarExitoMensaje: "La Alumno ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar al Alumno pues tiene elementos asociados",
    },
    datosExtra: {},
    datosExtraOpen: [],//Array simple, datos que se pasan a la función open que los coge de "card_info_oculto_${id}"
    funcionesExternas: {
        get: function() { console.log("Función externa ejecutada después de get");},
        modalEliminar: function() { console.log("Función externa ejecutada después de modalEliminar");},
        eliminar: function(data) { console.log("Función externa ejecutada después de eliminar");},
        open: function(data) { console.log("Función externa ejecutada después de open");},
        crear: function() {console.log("Función externa ejecutada después de crear");},
        guardar: function(data) { console.log("Función externa ejecutada después de guardar");}
    },
    funcionesContenidoOpen: [
        async function(id, data) {
            //inscripciones por alumno
            let configInscripcionesPorAlumno = configInscripcionesAdministrador;
            configInscripcionesPorAlumno.location = "inscripcionesAdministrador";
            configInscripcionesPorAlumno.funcionLeer = "listarPorAlumno";
            configInscripcionesPorAlumno.returnHtml = true;
            configInscripcionesPorAlumno.dataFuncionLeerHtml = {id_alumno: id};
            configInscripcionesPorAlumno.campos = [
             {
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

            const paginaInscripcionesPorAlumno = new Pagina(configInscripcionesPorAlumno);
            return await paginaInscripcionesPorAlumno.get();
        }
    ]
};

const paginaAlumnosAdministrador = new Pagina(configAlumnosAdministrador);

async function getAlumnosAdministrador() {
    await paginaAlumnosAdministrador.get();
}