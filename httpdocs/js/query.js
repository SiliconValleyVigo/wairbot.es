/*
titulo
descripcion
tokensUsados
*/
async function query(data, actionServer, actionResult) {
    //display block en cargandoFondo y cargandoBarra
    document.getElementById('cargandoFondo').style.display = 'block';
    document.getElementById('cargandoBarra').style.display = 'block';

    //si data contiene card
    if(data.card){
        // Asegúrate de tener acceso a la instancia `t` de Trello
        let t = TrelloPowerUp.iframe();

        async function getCardData() {
          let card = await t.card('name', 'desc');
          let titulo = card.name;
          let descripcion = card.desc;
        
          return {
            titulo: titulo,
            descripcion: descripcion
          };
        }

        let datosTarjeta = await getCardData();
        let titulo = datosTarjeta.titulo;
        let descripcion = datosTarjeta.descripcion;

        data.card.titulo = titulo;
        data.card.descripcion = descripcion;
    }

    data.funcion = actionServer;

    fetch(server, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {
            'Content-Type': 'application/json'
        },
        redirect: 'manual' // Agregado para prevenir redirecciones automáticas
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    }) // Agregado manejo de errores HTTP
    .then(res => {
        //comprobar si res contiene mensaje
        if(res.message){
            // Obtener el idioma del navegador
            let lang = navigator.language || navigator.userLanguage;

            // Obtener el idioma antes del "-"
            lang = lang.split("-")[0];

            res.message = traducirMenajesRespuesta(res.message, lang);
        }

        if (typeof window[actionResult] === "function") {
            window[actionResult](res, actionServer);
        }

        //display none en cargandoFondo y cargandoBarra
        document.getElementById('cargandoFondo').style.display = 'none';
        document.getElementById('cargandoBarra').style.display = 'none';

        if(res.tokensUsados){
            gestionarTokensOpenAI(res.tokensUsados);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function traducirMenajesRespuesta(message, lang){
    //comprobar si message contiene alguno de los siguientes mensajes
    if(message == 'Usuario no autorizado'){
        message = translations[lang].UsuarioNoAutorizado;
    }

    if(message == 'El correo electrónico ya está registrado'){
        message = translations[lang].ElCorreoElectrónicoYaEstaRegistrado;
    }

    if(message == 'Usuario o contraseña incorrectos'){
        message = translations[lang].UsuarioOContraseñaIncorrectos;
    }

    if(message == 'Se ha enviado una nueva contraseña al correo electrónico'){
        message = translations[lang].SeHaEnviadoUnaNuevaContraseñaAlCorreoElectronico;
    }
    
    return message;
}

//Gestionar tokens trello Tokens a punto de agotarse Tokens agotados  -  gestionarTokensOpenAI  --  tokensUsados
function gestionarTokensOpenAI(tokensUsados){
    let userData = JSON.parse(localStorage.getItem('wairbotTrelloUserData'));

    let data = {
        trello: userData.trello,
        token: userData.token,
        id: userData.id,
        tokensUsados: tokensUsados
    };

    query(data, 'gestionarTokensOpenAI', 'gestionarTokensOpenAIResult');
}

function gestionarTokensOpenAIResult(res){
    if(res.status == 'alert'){
        let mensajeDeAlerta = document.getElementById('mensajeDeAlerta');
        mensajeDeAlerta.style.display = 'block';
    }

    if(res.status == 'error'){
        let mensajeDePeligro = document.getElementById('mensajeDePeligro');
        mensajeDePeligro.style.display = 'block';
    }
}


function saveUserData(res, actionServer){
    if(res.status == 'success'){
        let token = res.token;
        let id = res.id;
        let email = res.email;
        let autorizado = res.autorizado;
        let trello = res.trello;

        userData = {
            token: token,
            id: id,
            email: email,
            autorizado: autorizado,
            trello: trello
        };

        localStorage.setItem('wairbotTrelloUserData', JSON.stringify(userData));

        autoNavigation();
    }else{
        if(actionServer == 'login'){
            let aviso = document.getElementById('loginAviso');
            aviso.innerHTML = res.message;
        }

        if(actionServer == 'registro'){
            let aviso = document.getElementById('registroAviso');
            aviso.innerHTML = res.message;
        }

        if(actionServer == 'autorizar'){
            let aviso = document.getElementById('pagoAviso');
            aviso.innerHTML = res.message;
        }

        if(actionServer == 'trelloAuth'){
            let aviso = document.getElementById('trelloAuthAviso');
            aviso.innerHTML = res.message;
        }
    }
}