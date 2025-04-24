//obtener la url de la pagina
let _urlActual = window.location.href || window.location.pathname;

//sustituir administrador/, agente/, tecnico/ o cliente/ por '' en la url
_urlActual = _urlActual.replace('administrador/', '');
_urlActual = _urlActual.replace('agente/', '');
_urlActual = _urlActual.replace('tecnico/', '');
_urlActual = _urlActual.replace('cliente/', '');

//eliminar todo lo que esté después del simbolo ? en la url incluido el simbolo
let index = _urlActual.indexOf('?');
if(index !== -1){
    _urlActual = _urlActual.substring(0, index);
}

let _URL = _urlActual + '/api/';

console.log(_URL);

function query(clase, funcion, datos, procesarRespuesta) {
    if(clase !== 'Login'){
        let tokenQuery = localStorage.getItem('token');
        datos.token = tokenQuery;
    }

    //añaidr clase y funcion a los datos
    datos.clase = clase;
    datos.funcion = funcion;
    datos.usuario = localStorage.getItem('usuario');

    console.log(datos);

    //usar fetch para hacer la peticion
    fetch(_URL, {
        method: 'POST',
        body: JSON.stringify(datos)
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            console.log(res.error);
            //imprimir la respuesta completa
            console.log(res);
        } else {
            window[procesarRespuesta](res);
            procesandoInicio = false;
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
    console.log(data);

    if(data.error){
        console.log(data.error);
        //imprimir la respuesta completa
        console.log(data);
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