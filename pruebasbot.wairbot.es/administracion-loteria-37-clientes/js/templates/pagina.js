class Pagina {
    constructor(config) {
        this.location = config.location;
        this.titulo = config.titulo;
        this.campos = config.campos;
        this.clase = config.clase;
        this.nombreFuncion = config.nombreFuncion; // Nombre de la función para diferenciar
        this.funcionGuardar = `guardar${this.nombreFuncion}`; // Nombre de la función para el botón de guardar
        this.rol = config.rol;
        this.funcionLeer = config.funcionLeer;
        this.camposCard = config.camposCard;
        this.mensajes = config.mensajes;
        this.datosExtra = config.datosExtra || {};
        this.funcionesExternas = config.funcionesExternas || {};
        this.funcionesContenidoOpen = config.funcionesContenidoOpen || [];
        this.funcionModificadoraDeTabla = config.funcionModificadoraDeTabla || "";
        this.returnHtml = config.returnHtml || false;
        this.dataFuncionLeerHtml = config.dataFuncionLeerHtml || {};

        // Hacer que los métodos sean accesibles desde el HTML
        window[`open${this.nombreFuncion}`] = this.open.bind(this);
        window[`crear${this.nombreFuncion}`] = this.crear.bind(this);
        window[`eliminar${this.nombreFuncion}`] = this.eliminar.bind(this);
        window[`modalEliminar${this.nombreFuncion}`] = this.modalEliminar.bind(this);
        window[`guardar${this.nombreFuncion}`] = this.guardar.bind(this);
    }

    async get(nPagina = 1, returnHtml = true) {
        let funcionAbrir = `open${this.nombreFuncion}`;
        let funcionCrear = `crear${this.nombreFuncion}`;
        let nombreTablaConfiguracion = this.clase.toLowerCase();
        let hayPaginacion = true;
        let filtro = false;

        if(returnHtml === true) {
            returnHtml = this.returnHtml;
        }else{
            returnHtml = false;
        }

        if (returnHtml) {
            let data = await getGrid(
                this.location,
                this.clase,
                this.funcionLeer,
                funcionAbrir,
                funcionCrear,
                this.rol,
                this.titulo,
                hayPaginacion,
                this.camposCard,
                nPagina,
                nombreTablaConfiguracion,
                this.campos,
                this.funcionModificadoraDeTabla,
                filtro,
                this.returnHtml,
                this.dataFuncionLeerHtml
            );

            this.ejecutarFuncionExterna('get');
            
            return data;
        }else{
            await getGrid(
                this.location,
                this.clase,
                this.funcionLeer,
                funcionAbrir,
                funcionCrear,
                this.rol,
                this.titulo,
                hayPaginacion,
                this.camposCard,
                nPagina,
                nombreTablaConfiguracion,
                this.campos,
                this.funcionModificadoraDeTabla,
                filtro,
                false,
                this.dataFuncionLeerHtml
            );
        }

        this.ejecutarFuncionExterna('get');
    }

    modalEliminar(id) {
        let titulo = this.mensajes.eliminarTitulo;
        let mensaje = this.mensajes.eliminarMensaje;
        let funcionAceptar = `eliminar${this.nombreFuncion}`;
        let arrayParametros = [id];

        createModalAviso(titulo, mensaje, funcionAceptar, arrayParametros);
        this.ejecutarFuncionExterna('modalEliminar');
    }

    async eliminar(id) {
        let datos = { id: id };
        let data = await queryAsync(this.clase, "eliminar", datos);

        if (data.status === "success") {
            this.get();
            destruirModalAviso();
            destruirModal();

            createModalAviso(this.mensajes.eliminarExitoTitulo, this.mensajes.eliminarExitoMensaje, "destruirModalAviso", null);
        } else {
            destruirModalAviso();
            createModalAviso(this.mensajes.eliminarErrorTitulo, this.mensajes.eliminarErrorMensaje, "destruirModalAviso", null);
        }
        this.ejecutarFuncionExterna('eliminar', data);

        await new Promise((resolve) => setTimeout(resolve, 1000));

        let spinnerContainer = document.getElementById('spinnerContainer');
        spinnerContainer.style.display = 'none';
    }

    async open(id) {
        let datos = { id: id };
        let data = await queryAsync(this.clase, "obtenerPorId", datos);
        data = data.data;

        let botonEliminar = {
            texto: "Eliminar",
            funcion: `modalEliminar${this.nombreFuncion}`,
            id: id,
        };

        let campos = await crearCamposDeFormulario(this.campos, data, botonEliminar);
        let form = createForm(campos, id);

        let contenido = form;
        for (let func of this.funcionesContenidoOpen) {
            contenido += await func(id, data);
        }

        createModal(`Editar ${this.clase}`, contenido, this.funcionGuardar, this.rol, id);

        this.campos.forEach((c) => {
            if (c.bloquear) {
                document.getElementById(c.id).disabled = true;
            }
        });
        this.ejecutarFuncionExterna('open', data);
    }

    async crear() {
        let campos = await crearCamposDeFormulario(this.campos);
        let form = createForm(campos, "");

        createModal(`Crear ${this.clase}`, form, this.funcionGuardar, this.rol);
        this.ejecutarFuncionExterna('crear');
    }

    async guardar(id = 0) {
        let datos = getDatosFormulario(this.campos, this.datosExtra);
        
        // Asegurarse de que datos.data exista
        if (!datos.data) {
            datos.data = {};
        }
    
        // Función para convertir archivo a base64 y obtener la URL
        const convertirArchivoABase64 = (campo) => {
            return new Promise((resolve, reject) => {
                let file = document.getElementById(campo.id).files[0];
                const reader = new FileReader();
                reader.onload = (e) => {
                    const base64String = e.target.result.split(",")[1];
                    const data = {
                        carpeta: "loterias37vigo_CHAT_",
                        extension: file.name.split(".").pop(),
                        nombreArchivo: file.name,
                        archivo: base64String,
                        key: "PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU",
                    };
    
                    fetch("https://pruebasbot.wairbot.es/base64-a-url/", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(data),
                    })
                        .then((response) => response.json())
                        .then((result) => {
                            if (result.success) {
                                console.log("URL del archivo:", result.path);
                                resolve({ id: campo.id, url: result.path });
                            } else {
                                alert("Error al guardar el archivo: " + result.message);
                                reject(result.message);
                            }
                        })
                        .catch((error) => {
                            console.error("Error:", error);
                            reject(error);
                        });
                };
                reader.readAsDataURL(file);
            });
        };
    
        // Crear un array de promesas para los campos de tipo file
        let promesas = [];
        for (let campo of this.campos) {
            if (campo.tipo === "file" && document.getElementById(campo.id).files.length > 0) {
                promesas.push(convertirArchivoABase64(campo));
            }
            if (campo.tipo === "json") {
                //obtener el valor del campo por su id del localStorage
                let valorLocalstorage = localStorage.getItem(campo.id);
                if (valorLocalstorage) {
                    datos.data[campo.id] = valorLocalstorage;
                }
            }
        }
    
        // Esperar a que todas las promesas se resuelvan
        try {
            let resultados = await Promise.all(promesas);
            resultados.forEach(result => {
                datos.data[result.id] = result.url;
            });
        } catch (error) {
            console.error("Error en la conversión de archivos:", error);
            return; // Salir si hay un error
        }
    
        if (id !== 0) {
            datos.id = id;
            await queryAsync(this.clase, "editar", datos);
        } else {
            await queryAsync(this.clase, "crear", datos);
        }
    
        this.get(1, false);
        this.ejecutarFuncionExterna('guardar', datos);
        destruirModal();
    }

    ejecutarFuncionExterna(metodo, data = null) {
        if (this.funcionesExternas[metodo]) {
            this.funcionesExternas[metodo](data);
        }
    }
}

/*

/*
uploadImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const base64String = e.target.result.split(",")[1];
            const data = {
                carpeta: this.getAttribute("codigoAdministradorBot"),
                extension: file.name.split(".").pop(),
                nombreArchivo: file.name,
                archivo: base64String,
                key: "PBdFD4FHSinwaabBY3BrPFny4g3PHMvtZ8bef9qkaxkGzbryXU",
            };

            fetch("https://pruebasbot.wairbot.es/base64-a-url/", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            })
                .then((response) => response.json())
                .then((result) => {
                    if (result.success) {
                        //guardar la URL del archivo en la conversación dentro de un div oculto con display: none dendro del div con id preview
                        const preview = this.shadow.querySelector("#preview");
                        const url = document.createElement("div");
                        url.id = "previewURL";
                        url.style.display = "none";
                        url.innerHTML = result.path;
                        preview.appendChild(url);
                    } else {
                        alert("Error al guardar el archivo: " + result.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        };
        reader.readAsDataURL(file);
    }
*/


/*
// Ejemplo de uso
const configCoordinadores = {
    campos: camposCoordinadoresAdministrador,
    clase: "Coordinadores",
    nombreFuncion: "Coordinadores", // Nombre de la función para diferenciar
    rol: "ADMINISTRADOR",
    funcionLeer: "listar",
    funcionModificadoraDeTabla: "",
    camposCard: {
        info1: "nombre",
        info2: "telefono_fijo",
        info3: "telefono_movil",
        info4: "direccion",
        info5: "email",
        id: "id",
    },
    mensajes: {
        eliminarTitulo: "Eliminar Coordinador",
        eliminarMensaje: "¿Estas Seguro de eliminar este Coordinador? <br> <br> <strong>Esta acción no se puede deshacer</strong>",
        eliminarExitoTitulo: "Coordinador Eliminado",
        eliminarExitoMensaje: "El Coordinador ha sido eliminado correctamente",
        eliminarErrorTitulo: "Error",
        eliminarErrorMensaje: "No es posible eliminar el Coordinador pues tiene elementos asociados",
    },
    datosExtra: {},
    funcionesExternas: {
        get: function() {
            console.log("Función externa ejecutada después de get", data);
        },
        modalEliminar: function() {
            console.log("Función externa ejecutada después de modalEliminar", data);
        },
        eliminar: function(data) {
            console.log("Función externa ejecutada después de eliminar", data);
        },
        open: function(data) {
            console.log("Función externa ejecutada después de open", data);
        },
        crear: function() {
            console.log("Función externa ejecutada después de crear", data);
        },
        guardar: function(data) {
            console.log("Función externa ejecutada después de guardar", data);
        }
    },
    funcionesContenidoOpen: [
        async function(id) {
            // Aquí puedes definir la lógica para agregar contenido adicional en open
            return `<div>Contenido adicional para el ID ${id}</div>`;
        },
        async function(id) {
            // Otra función para agregar contenido adicional en open
            return `<div>Más contenido adicional para el ID ${id}</div>`;
        }
    ]
};

const adminCoordinadores = new Pagina(configCoordinadores);

// Llamar al método get para insertar el HTML en su lugar
adminCoordinadores.get();
*/
