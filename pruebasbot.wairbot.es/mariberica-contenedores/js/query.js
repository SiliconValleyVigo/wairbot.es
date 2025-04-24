//obtener la url de la pagina
let _urlActual = window.location.href || window.location.pathname;

//si la url contiene un ? eliminarlo
if(_urlActual.includes('?')){
    _urlActual = _urlActual.split('?')[0];
}

let _URL = _urlActual + '/api/';

function query(clase, funcion, datos, procesarRespuesta) {
    if(clase !== 'Login'){
        let tokenQuery = localStorage.getItem('token');
        datos.token = tokenQuery;
    }

    // añadir clase y funcion a los datos
    datos.clase = clase;
    datos.funcion = funcion;
    datos.usuario = localStorage.getItem('usuario');

    // usar fetch para hacer la petición
    fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        // Llamar a la función procesarRespuesta con la respuesta
        window[procesarRespuesta](res);
    })
    .catch(error => {
        // Manejar errores de la petición
        console.error('Error en la petición:', error);
        // Llamar a la función procesarRespuesta con el error
        window[procesarRespuesta]({ error: true, message: error.message });
    });
}


//hacer una versión de query asincrona
async function queryAsync(clase, funcion, datos){
    //spinnerContainer display flex
    let spinnerContainer = document.getElementById('spinnerContainer');

    //si spinnerContainer no existe esperar 1 segundo y volver a intentar
    if(!spinnerContainer){
        await new Promise(resolve => setTimeout(resolve, 1000));
        spinnerContainer = document.getElementById('spinnerContainer');
    }

    spinnerContainer.style.display = 'flex';

    //impeidir el click en los botones
    await impedirClick();

    let tokenQuery = localStorage.getItem('token');
    let usuario = JSON.parse(localStorage.getItem('usuario'));

    //insertar en datos los campos que no sean false y el token
    datos.token = tokenQuery;

    //añaidr clase y funcion a los datos
    datos.clase = clase;
    datos.funcion = funcion;
    datos.usuario = usuario;

    //comprobar si existe el elemento selectFiltro
    let selectFiltro = document.getElementById('selectFiltro');
    if(selectFiltro){
        datos.filtro = selectFiltro.value;
        datos.codigoBot = selectFiltro.value;
    }

    //comprobar si existe el elemento del dom con el id 'rows_per_page' y si existe añadirlo a datos
    let rowsPerPage = document.getElementById('rows_per_page');
    if(rowsPerPage){
        datos.rows_per_page = rowsPerPage.value;
    }

    //usar fetch para hacer la peticion
    let res = await fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos)
    });

    let data = await res.json().catch(error => {
        spinnerContainer.style.display = 'none';

        let modalError = document.getElementById('modalError');
        let modalErrorTexto = document.getElementById('modalErrorTexto');

        modalErrorTexto.innerHTML = "Error en la conexión con el servidor";
        modalError.style.display = 'block';
    });

    if(data.error){
        console.log(data.error);

        let modalError = document.getElementById('modalError');
        let modalErrorTexto = document.getElementById('modalErrorTexto');

        modalErrorTexto.innerHTML = data.error.result;
        modalError.style.display = 'block';
    } else {
        if(data.status === 'success'){
            spinnerContainer.style.display = 'none';
            let mensajeOk = data.message;
            console.log(mensajeOk);
        }

        if(data.status === 'error'){
            let mensajeError = data.message;
            console.log(mensajeError);

            let modalError = document.getElementById('modalError');
            let modalErrorTexto = document.getElementById('modalErrorTexto');

            modalErrorTexto.innerHTML = mensajeError;
            modalError.style.display = 'block';
        }
    }

    //permitir el click en los botones
    await permitirClick();

    //spinnerContainer display none
    spinnerContainer.style.display = 'none';

    return data;
}

// Definir preventClick fuera de los bucles para asegurar una referencia única
const preventClick = (e) => e.preventDefault();

async function impedirClick(){
    let botones = document.querySelectorAll('.modalBoton');

    botones.forEach(b => {
        b.addEventListener('click', preventClick);
        // Almacenar que el evento preventClick está activo
        b._isClickPrevented = true;
    });
}

async function permitirClick(){
    let botones = document.querySelectorAll('.modalBoton');

    botones.forEach(b => {
        // Verificar si el evento preventClick está activo antes de intentar removerlo
        if (b._isClickPrevented) {
            b.removeEventListener('click', preventClick);
            // Indicar que el evento preventClick ya no está activo
            delete b._isClickPrevented;
        }
    });
}