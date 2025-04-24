//obtener la url de la pagina
let _urlActual = window.location.href || window.location.pathname;

let _URL = _urlActual + '/api/';

function query(clase, funcion, datos, procesarRespuesta) {
    if(clase !== 'Login'){
        let tokenQuery = localStorage.getItem('token');
        datos.token = tokenQuery;
    }

    //añaidr clase y funcion a los datos
    datos.clase = clase;
    datos.funcion = funcion;
    datos.usuario = localStorage.getItem('usuario');

    //usar fetch para hacer la peticion
    fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
        } else {
            window[procesarRespuesta](res);
        }
    })
    .catch(error => {
        console.log(error);
    });
}


//hacer una versión de query asincrona
async function queryAsync(clase, funcion, datos){
    //spinnerContainer display flex
    let spinnerContainer = document.getElementById('spinnerContainer');
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

    //comprobar si existe un filtro en localStorage
    let filtro = localStorage.getItem('filtro_activo');
    let location = localStorage.getItem('location');
    let locationSinFiltro = ['comunidades']
    if(filtro && !locationSinFiltro.includes(location)){
        datos.filtro = filtro;
        codigoBot = filtro;
    }

    //usar fetch para hacer la peticion
    let res = await fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos)
    });

    let data = await res.json();

    if(data.error){
        console.log(data.error);

        let modalError = document.getElementById('modalError');
        let modalErrorTexto = document.getElementById('modalErrorTexto');

        modalErrorTexto.innerHTML = data.error.result;
        modalError.style.display = 'block';

        spinnerContainer.style.display = 'none';
    } else {
        if(data.status === 'success'){
            let mensajeOk = data.message;
            console.log(mensajeOk);

            spinnerContainer.style.display = 'none';
        }

        if(data.status === 'error'){
            let mensajeError = data.message;
            console.log(mensajeError);

            let modalError = document.getElementById('modalError');
            let modalErrorTexto = document.getElementById('modalErrorTexto');

            modalErrorTexto.innerHTML = mensajeError;
            modalError.style.display = 'block';

            spinnerContainer.style.display = 'none';
        }
    }

    //spinnerContainer display none
    spinnerContainer.style.display = 'none';

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