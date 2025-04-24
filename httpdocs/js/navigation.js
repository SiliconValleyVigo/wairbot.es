function autoNavigation(){
    wairbotTrelloUserData = localStorage.getItem('wairbotTrelloUserData');
    userData = {};

    if(wairbotTrelloUserData == null){
        document.getElementById('login').style.display = 'flex';
    }else{
        userData = JSON.parse(wairbotTrelloUserData);
    
        let autorizado = userData.autorizado;
        let trello = userData.trello;

        //si el usuario no tiene trello autorizado llevar a autorización
        if(trello == ''){
            document.getElementById('trelloAuth').style.display = 'flex';
            document.getElementById('servicio').style.display = 'none';
            document.getElementById('login').style.display = 'none';
            document.getElementById('registro').style.display = 'none';
            document.getElementById('pago').style.display = 'none';

            //redirigir a /auth en una nueva pestaña
            let urlAuthWairbot = 'https://wairbot.es/auth/?userToken=' + userData.token + '&userId=' + userData.id;
            window.open(urlAuthWairbot, '_blank');
        }

        //si el usuario no está autorizado llevar a pago
        if(autorizado == 0){
            document.getElementById('pago').style.display = 'flex';
            document.getElementById('trelloAuth').style.display = 'none';
            document.getElementById('servicio').style.display = 'none';
            document.getElementById('login').style.display = 'none';
            document.getElementById('registro').style.display = 'none';
        }

        //si el usuario está autorizado y tiene trello autorizado llevar a servicio
        if(trello !== ''){
            document.getElementById('servicio').style.display = 'flex';
            document.getElementById('pago').style.display = 'none';
            document.getElementById('trelloAuth').style.display = 'none';
            document.getElementById('login').style.display = 'none';
            document.getElementById('registro').style.display = 'none';
        }
    }

    // Obtener el idioma del navegador
    var lang = navigator.language || navigator.userLanguage;
    
    // Obtener el idioma antes del "-"
    lang = lang.split("-")[0];
    
    // Cambiar el idioma si no es español y existe la traducción
    if (lang !== "es" && translations[lang]) {
      changeLanguage(lang);
    }
}

function loginToRegistro(){
    document.getElementById('login').style.display = 'none';
    document.getElementById('registro').style.display = 'flex';
}

function registroToLogin(){
    document.getElementById('registro').style.display = 'none';
    document.getElementById('login').style.display = 'flex';
}

function recuperarToLogin(){
    document.getElementById('recuperar').style.display = 'none';
    document.getElementById('login').style.display = 'flex';
}

//aplicar autoNavigation al cargar la página
window.addEventListener('load', autoNavigation, false);



//si autorizado es 0 obtener wairbotTrelloUserData de la API cada 5 segundos
let autorizacionUserData = localStorage.getItem('wairbotTrelloUserData');

let intervalId;

if(autorizacionUserData != null){
    autorizacionUserData = JSON.parse(autorizacionUserData);
    let autorizado = autorizacionUserData.autorizado;

    if(autorizado == 0){
        intervalId = setInterval(function(){
            let loginElement = document.getElementById('login');
            let displayStyle = window.getComputedStyle(loginElement).display;
        
            if (displayStyle !== 'none') {
                clearInterval(intervalId);
                return;
            }
        
            let userData = JSON.parse(localStorage.getItem('wairbotTrelloUserData'));
            let token = userData.token;
            let id = userData.id;
        
            let data = {
                token: token,
                id: id
            };
        
            query(data, 'getUserData', 'checkAutorizacion');
        }, 5000);
    }
}

function checkAutorizacion(res){
    localStorage.setItem('wairbotTrelloUserData', JSON.stringify(res));
    
    //si trello está vacío o nulo llevar a autorización
    if(res.trello == '' || res.trello == null){
        document.getElementById('trelloAuth').style.display = 'flex';
        document.getElementById('servicio').style.display = 'none';
        document.getElementById('login').style.display = 'none';
        document.getElementById('registro').style.display = 'none';
        document.getElementById('pago').style.display = 'none';

        //redirigir a /auth en una nueva pestaña
        let urlAuthWairbot = 'https://wairbot.es/auth/?userToken=' + res.token + '&userId=' + res.id;
        window.open(urlAuthWairbot, '_blank');
    }

    //si autorizado es 0 llevar a pago
    if(res.autorizado == 0){
        document.getElementById('pago').style.display = 'flex';
        document.getElementById('trelloAuth').style.display = 'none';
        document.getElementById('servicio').style.display = 'none';
        document.getElementById('login').style.display = 'none';
        document.getElementById('registro').style.display = 'none';
    }

    //si autorizado es 1 llevar a servicio
    if(res.autorizado == 1){
        document.getElementById('servicio').style.display = 'flex';
        document.getElementById('pago').style.display = 'none';
        document.getElementById('trelloAuth').style.display = 'none';
        document.getElementById('login').style.display = 'none';
        document.getElementById('registro').style.display = 'none';

        clearInterval(intervalId);
    }
}

function cerrarSesion(){
    localStorage.removeItem('wairbotTrelloUserData');
    autoNavigation();
}