//obtener la url de la pagina
let _urlActual = window.location.href || window.location.pathname;

//sustituir administrador/, agente/, tecnico/ o cliente/ por '' en la url
_urlActual = _urlActual.replace('administrador/', '');
_urlActual = _urlActual.replace('agente/', '');
_urlActual = _urlActual.replace('tecnico/', '');
_urlActual = _urlActual.replace('cliente/', '');

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
    let tokenQuery = localStorage.getItem('token');
    let usuario = JSON.parse(localStorage.getItem('usuario'));

    //insertar en datos los campos que no sean false y el token
    datos.token = tokenQuery;

    //añaidr clase y funcion a los datos
    datos.clase = clase;
    datos.funcion = funcion;
    datos.usuario = usuario;

    //usar fetch para hacer la peticion
    let res = await fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos)
    });

    let data = await res.json();

    if(data.error){
        console.log(data.error);
    } else {
        if(data.status === 'success'){
            let mensajeOk = data.message;
            console.log(mensajeOk);
        }

        if(data.status === 'error'){
            let mensajeError = data.message;
            console.log(mensajeError);
        }
    }

    return data;
}