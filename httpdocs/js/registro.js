function registro(){
    let email = document.getElementById('registroEmail').value;
    let password = document.getElementById('registroPassword').value;
    let condiciones = document.getElementById('registroCondiciones').checked;

    // Obtener el idioma del navegador
    let langNavigatior = navigator.language || navigator.userLanguage;

    // Obtener el idioma antes del "-"
    langNavigatior = langNavigatior.split("-")[0];

    //comprobar los datos
    if(email == ''){
        //obtener el mensaje de error en el idioma del navegador, si no existe en el idioma del navegador obtenerlo en inglés
        let mensajeError = '';
        if(translations[langNavigatior]){
            mensajeError = translations[langNavigatior].elEmailEsObligatorio;
        }else{
            mensajeError = translations['en'].elEmailEsObligatorio;
        }

        let aviso = document.getElementById('registroAviso');
        aviso.innerHTML = `<span data-translate="elEmailEsObligatorio">${mensajeError}</span>`;

        return false;
    }

    if(password == ''){
        //obtener el mensaje de error en el idioma del navegador, si no existe en el idioma del navegador obtenerlo en inglés
        let mensajeError = '';
        if(translations[langNavigatior]){
            mensajeError = translations[langNavigatior].laContraseñaEsObligatoria;
        }else{
            mensajeError = translations['en'].laContraseñaEsObligatoria;
        }
        
        let aviso = document.getElementById('registroAviso');
        aviso.innerHTML = `<span data-translate="laContraseñaEsObligatoria">${mensajeError}</span>`
        return false;
    }

    if(condiciones == false){
        //obtener el mensaje de error en el idioma del navegador, si no existe en el idioma del navegador obtenerlo en inglés
        let mensajeError = '';
        if(translations[langNavigatior]){
            mensajeError = translations[langNavigatior].debesAceptarLasCondiciones;
        }else{
            mensajeError = translations['en'].debesAceptarLasCondiciones;
        }

        let aviso = document.getElementById('registroAviso');
        aviso.innerHTML = `<span data-translate="debesAceptarLasCondiciones">${mensajeError}</span>`
        return false;
    }

    //comprobar si email tiene formato correcto
    let emailValido = validateEmail(email);
    if(emailValido == false){
        let mensajeError = '';
        if(translations[langNavigatior]){
            mensajeError = translations[langNavigatior].elEmailNoEsValido;
        }else{
            mensajeError = translations['en'].elEmailNoEsValido;
        }

        let aviso = document.getElementById('registroAviso');
        aviso.innerHTML = `<span data-translate="elEmailNoEsValido">${mensajeError}</span>`
        return false;
    }

    //comprobar si contraseña tiene más de 8 caracteres
    if(password.length < 8){
        let mensajeError = '';
        if(translations[langNavigatior]){
            mensajeError = translations[langNavigatior].laContraseñaDebeTenerMasDe8Caracteres;
        }else{
            mensajeError = translations['en'].laContraseñaDebeTenerMasDe8Caracteres;
        }

        let aviso = document.getElementById('registroAviso');
        aviso.innerHTML = `<span data-translate="laContraseñaDebeTenerMasDe8Caracteres">${mensajeError}</span>`
        return false;
    }

    let data = {
        email: email,
        password: password
    };

    // Obtener el idioma del navegador
    let lang = navigator.language || navigator.userLanguage;

    // Obtener el idioma antes del "-"
    lang = lang.split("-")[0];

    // Cambiar el idioma si no es español y existe la traducción
    if (lang !== "es" && translations[lang]) {
      changeLanguage(lang);
    }

    query(data, 'registro', 'saveUserData');
}

function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}