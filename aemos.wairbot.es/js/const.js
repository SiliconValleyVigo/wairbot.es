const locationHandlers = {
    'alumnosAdministrador': getAlumnosAdministrador,
    'cursosAdministrador': getCursosAdministrador,
    'inscripcionesAdministrador': getInscripcionesAdministrador,
    'clasesAdministrador': getClasesAdministrador,
    'accionesAdministrador': getAccionesAdministrador,
    'formadoresAdministrador': getFormadoresAdministrador,
    'mensajesAdministrador': getMensajesAdministrador,
    'citasAdministrador': getCitasAdministrador,
    'wairbotDataAdministrador': getWairbotDataAdministrador,
    'wairbotCategoryAdministrador': getWairbotCategoryAdministrador,
    'controlAdministrador': getControlAdministrador,

};

//////////////////////////////////////////// WHATSAPP TEMPLATES ////////////////////////////////////////////
//un objeto que contiene las funciones para generadores de plantillas de whatsapp
const whatsappTemplates = {
    'plantillaPropuestaDeCurso': async function() {
        let clase = "Cursos";
        let funcion = "obtenerCursosQueNoIniciaronPorFecha";

        let data = {};

        let cursos = await queryAsync(clase, funcion, data);
        cursos = cursos['data'];
        console.log(cursos);

        //crear un select con los nombres de los cursos
        let select = `<select
            id="cursoSelectMensajeWhatsapp"
            name="curso"
            onchange="
                desabilitar('botonPrincipalEnviarMensajeWhatsapp'),
                insertarPropuestaDeCursoEnTextarea(this.value)
            ">`;
        select += `<option value="" selected>
            Seleccione un Curso
        </option>`;
        cursos.forEach(curso => {
            select += `<option value="${curso.id}">${curso.nombre}</option>`;
        });
        select += `</select>`;

        //crear un botón para enviar el mensaje de la propuesta
        let boton = `
            <button
                id="botonPrincipalEnviarMensajeWhatsapp"
                onclick="enviarPropuestaDeCurso()"
            >
                Enviar Propuesta
            </button>
        `;

        //crear el html
        let html = `
            <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; margin-top: 20px;">
                ${select}
                ${boton}
            </div>
        `;

        return html;
    }
};

async function insertarPropuestaDeCursoEnTextarea(cursoSeleccionado) {
    let clase = "Cursos";
    let funcion = "obtenerPorId";
    let data = {id: cursoSeleccionado};

    let curso = await queryAsync(clase, funcion, data);
    curso = curso['data'];

    let nombre = curso.nombre;
    let descripcion = curso.descripcion;
    let horas = curso.horas;
    let fechas = curso.fechas;
    let horario = curso.horario;
    let numero_clases = curso.numero_clases;
    let observaciones = curso.observaciones_publicas;


    let mensaje = `
Hola, desde Aemos te proponemos el siguiente curso:

*${nombre}*
${descripcion}
⏳ Horas: ${horas}
📅 Fechas: ${fechas}
🕒 Horario: ${horario}
👨‍🏫 Número de Clases: ${numero_clases}

${observaciones}

*¿Te interesa este curso?*
Escribe *1* si te interesa
Escribe *2* si no te interesa
`;

    document.getElementById('mensajeDeWhatsapp').value = mensaje;

    //al botón con id botonPrincipalEnviarMensajeWhatsapp display none
    document.getElementById('botonPrincipalEnviarMensajeWhatsapp').style.display = "none";
}

async function enviarPropuestaDeCurso() {
    let telefonos = document.getElementById("telefonosParaMensajeWhatsapp").innerText;

    let telefonosArray = telefonos.split(", ");
    let mensaje = document.getElementById("mensajeDeWhatsapp").value;
    let cursoSeleccionado = document.getElementById('cursoSelectMensajeWhatsapp').value;

    telefonosArray = ['34600857882'];

    //enviar el mensaje a los telefonos
    for (let telefono of telefonosArray) {
        let clase = "Utilidades";
        let funcion = "enviarMensajeWhatsapp";
        let datos = {
            userPhone: telefono,
            message: mensaje
        };

        await queryAsync(clase, funcion, datos);

        clase = "Utilidades";
        funcion = "iniciarFormularioEnWairbot";
        datos = {
            userPhone: telefono,
            curso: cursoSeleccionado
        };

        await queryAsync(clase, funcion, datos);
    }

    destruirModal();

    //modal aviso de envio
    createModalAviso("Mensaje Enviado", "Los mensajes han sido enviados correctamente a los teléfonos: " + telefonos);     
}
    
////////////////////////////////////////////////////////////////////////////////////////////////////////