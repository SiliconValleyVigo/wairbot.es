let autocompletarTimer; // Variable para almacenar el temporizador

function autocompletar(texto, campo) {
    // Limpiar temporizador si existe
    clearTimeout(autocompletarTimer);

    let cardData = JSON.parse(localStorage.getItem('cardData'));

    // Si campo es titulo, eliminar titulo de cardData
    if (campo == 'titulo') {
        cardData.titulo = '';
    }

    // Si campo es descripcion, eliminar descripcion de cardData
    if (campo == 'descripcion') {
        cardData.descripcion = '';
    }

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        texto: texto,
        campo: campo,
        card: cardData
    };

    // Establecer un temporizador de 0.5 segundos antes de realizar la consulta
    autocompletarTimer = setTimeout(() => {
        query(data, 'procesar', 'autocompletarResponse');
    }, 1500);
}

function autocompletarResponse(response){
    if(response.status == 'success'){
        //eliminar los elementos con el id autocompletadoPrevio
        let elementos = document.querySelectorAll('#autocompletadoPrevio');
        elementos.forEach(elemento => {
            elemento.parentNode.removeChild(elemento);
        });

        let campo = response.campo;
        let iaResultado = response.iaResultado;

        // Crear un nuevo elemento span
        let spanElement = document.createElement('span');
        spanElement.id = 'autocompletadoPrevio';
        spanElement.style.color = 'grey';
        spanElement.textContent = iaResultado;

        if(campo == 'respuesta'){
            let respuestaServicio = document.getElementById('respuestaServicio');
            respuestaServicio.appendChild(spanElement);

            // Hacer que el div sea editable
            respuestaServicio.contentEditable = true;

            // Crear un rango y un objeto de selección
            let range = document.createRange();
            let sel = window.getSelection();

            // Mover el cursor justo antes del span
            range.setStartBefore(spanElement);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

function cambiarCampo(campo){
    let cardData = JSON.parse(localStorage.getItem('cardData'));

    let textoTextarea = document.getElementById('respuestaServicio');
    textoTextarea = textoTextarea.textContent;

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        campo : campo,
        card : cardData,
        texto : textoTextarea
    };

    query(data, 'actualizarCardTrello', 'cambiarCampoResponse');
}

function cambiarCampoResponse(response){
    if(response.status == 'success'){
        let cardData = localStorage.getItem('cardData');
        
        if(cardData !== null){
            cardData = JSON.parse(localStorage.getItem('cardData'));
        }

        let texto = document.getElementById('respuestaServicio').textContent;

        if(response.campo == 'titulo'){
            cardData.titulo = texto;
            
            localStorage.setItem('cardData', JSON.stringify(cardData));
        }

        if(response.campo == 'descripcion'){
            cardData.descripcion = texto;
            
            localStorage.setItem('cardData', JSON.stringify(cardData));
        }

        //eliminar el texto d respuestaServicio
        let respuestaServicio = document.getElementById('respuestaServicio');
        respuestaServicio.innerHTML = '';

    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

document.addEventListener('keydown', function(event) {
    // Obtener el elemento con id 'autocompletadoPrevio'
    let autocompletadoPrevio = document.getElementById('autocompletadoPrevio');

    // Verificar si el elemento existe y la tecla es un caracter o espacio
    if (autocompletadoPrevio && (event.key.length === 1 || event.key === ' ')) {
        // Eliminar el elemento con id 'autocompletadoPrevio'
        autocompletadoPrevio.parentNode.removeChild(autocompletadoPrevio);
    } else if (autocompletadoPrevio && (event.key === 'ArrowRight' || event.key === 'Tab' || event.key === 'Enter')) {
        // Obtener el texto del elemento span
        let textoSpan = autocompletadoPrevio.textContent;

        // Obtener el elemento padre del span
        let padreSpan = autocompletadoPrevio.parentNode;

        //Obtener el id del textarea
        let campo = "";
        let idTextarea = padreSpan.id;
        let textoTextarea = padreSpan.textContent;

        if(idTextarea == 'tituloServicio'){
            campo = 'titulo';
        }

        if(idTextarea == 'descripcionServicio'){
            campo = 'descripcion';
        }

        // Crear un nodo de texto con el contenido del span
        let textoNodo = document.createTextNode(textoSpan);

        // Reemplazar el span con el nodo de texto
        padreSpan.replaceChild(textoNodo, autocompletadoPrevio);

        // Colocar el cursor al final del texto
        let selection = window.getSelection();
        let range = document.createRange();
        range.setStartAfter(textoNodo);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);

        if(idTextarea !== 'respuestaServicio'){
            //Cambiar el campo de la card
            let cardData = JSON.parse(localStorage.getItem('cardData'));

            let data = {
                trello: userData.trello,
                token: userData.token,
                id: userData.id,
                campo : campo,
                card : cardData,
                texto : textoTextarea
            };

            query(data, 'actualizarCardTrello', 'completadoServicioResponse');
        }

        // Prevenir el comportamiento predeterminado del evento
        event.preventDefault();
    }
});

function completadoServicioResponse(response){
    if(response.status == 'success'){
        
    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

//Al pulsar ctrl + enter hacer un enter normal
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.key === 'Enter') {
        //obtener el textarea donde se ha pulsado ctrl + enter
        let textarea = event.target;

        //escribir un enter normal
        textarea.value += '\n';        
    }
});


function consultarGpt(){ //id: promptServicio //funcion: consultaGeneral
    let cardData = JSON.parse(localStorage.getItem('cardData'));
    let campo = 'respuesta';

    let userData = JSON.parse(localStorage.getItem('wairbotTrelloUserData'));

    let texto = document.getElementById('promptServicio').textContent;

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        campo : campo,
        card : cardData,
        texto : texto
    };

    query(data, 'consultaGeneral', 'consultaGeneralResponse');
}

function consultaGeneralResponse(response){
    if(response.status == 'success'){
        let respuestaServicio = document.getElementById('respuestaServicio');
        respuestaServicio.innerHTML = response.respuesta;
    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

function obtenerSugerenciaIA(campo){
    let cardData = JSON.parse(localStorage.getItem('cardData'));

    let userData = JSON.parse(localStorage.getItem('wairbotTrelloUserData'));

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        campo : campo,
        card : cardData
    };

    if(campo == 'titulo'){
        query(data, 'nombreCardTrello', 'nombreCardTrelloResponse');
    }

    if(campo == 'descripcion'){
        query(data, 'descripcionCardTrello', 'descripcionCardTrelloResponse');
    }
}

function nombreCardTrelloResponse(response){
    //añadir a respuestaServicio el texto de la respuesta
    if(response.status == 'success'){
        let respuestaServicio = document.getElementById('respuestaServicio');
        respuestaServicio.innerHTML = response.titulo;
    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

function descripcionCardTrelloResponse(response){
    //añadir a respuestaServicio el texto de la respuesta
    if(response.status == 'success'){
        let respuestaServicio = document.getElementById('respuestaServicio');
        respuestaServicio.innerHTML = response.descripcion;
    } else {
        let aviso = document.getElementById('servicioAviso');
        aviso.innerHTML = response.message;
    }
}

function addDescripcion(){
    let cardData = JSON.parse(localStorage.getItem('cardData'));
    let descripcionCard = cardData.descripcion;

    let respuestaServicio = document.getElementById('respuestaServicio');
    let descripcionRespuesta = respuestaServicio.textContent;

    let descripcion = descripcionCard + '\n' + descripcionRespuesta;

    cardData.descripcion = descripcion;

    localStorage.setItem('cardData', JSON.stringify(cardData));

    let userData = JSON.parse(localStorage.getItem('wairbotTrelloUserData'));

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        campo : 'descripcion',
        card : cardData,
        texto : descripcion
    };

    query(data, 'actualizarCardTrello', 'completadoServicioResponse');
}

