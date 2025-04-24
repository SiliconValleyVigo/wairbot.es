function login(event) {
    event.preventDefault();
    let usuarioLogin = document.getElementById("usuarioLogin").value;
    let passwordLogin = document.getElementById("passwordLogin").value;
    let avisoLogin = document.getElementById("avisoLogin");

    if (usuarioLogin === "") {
        avisoLogin.innerHTML = "Por favor, ingrese un usuario";
        return;
    }

    if (passwordLogin === "") {
        avisoLogin.innerHTML = "Por favor, ingrese una contraseña";
        return;
    }

    let datos = {
        user: usuarioLogin,
        password: passwordLogin
    }

    localStorage.setItem('usuario', usuarioLogin);

    query('Login', 'initLogin', datos, 'guardarDatosLogin');
}

function guardarDatosLogin(datos) {
    console.log(datos);
    if(datos.status === 'error'){
        //si existe un avisoLogin, mostrar mensaje
        let avisoLogin = document.getElementById('avisoLogin');
        if(avisoLogin){
            avisoLogin.innerHTML = 'Usuario o contraseña incorrectos'
        }
        
        return;
    }

    //comprobar si existe un token en localStorage
    let tokenLocal = localStorage.getItem('token');

    if(!tokenLocal || tokenLocal === 'undefined' || tokenLocal === 'null'){
        //si no existe token, guardarlo
        localStorage.setItem('token', datos.token);
    }
    
    datos = datos.data;

    console.log(datos);

    let usuario = datos.usuario;
    console.log(usuario);

    if(usuario.rol === 'AGENTE'){
        localStorage.setItem('comunidades', JSON.stringify(datos.comunidades));
    }

    //guardar usuario en localStorage
    localStorage.setItem('usuario', JSON.stringify(usuario));

    let sections = document.querySelectorAll('section');
    sections.forEach(s => {
        s.style.display = 'none';
    });

    //si el rol del usuario es cliente, salir
    generarMenuData(usuario, datos);
}

function generarMenuData(usuario, datos){
    console.log(datos);
    if(usuario.rol === 'SOCIO'){
        document.getElementById('SOCIO').style.display = 'flex';

        let menuData = [
            {
                nombre: 'Socio',
                get: 'getSocio',
            }
        ];
        
        generarMenu(menuData);

        getSocio();
    }
    
    if(usuario.rol === 'ADMINISTRADOR'){
        document.getElementById('ADMINISTRADOR').style.display = 'flex';

        let menuOptions = {
            'solicitudes': "getSolicitudes",
            'socios': "getSocios",
            'bajas': "getBajas",
            'remesas': "getRemesas",
            'facturas': "getFacturas",
            'facturasRectificativas': "getFacturasRectificativas"
        };

        let menuData = [];
        //recorrer locationHandlers por key value
        for(let key in menuOptions){
            let obj = {
                nombre: key,
                get: menuOptions[key]
            };
            menuData.push(obj);
        }

        generarMenu(menuData);

        let usuarios = datos.usuarios;

        //poner en formato:  <option value="key">value</option>
        let usuariosHtml = "";
        for(let key in usuarios){
            usuariosHtml += `<option value="${key}">${usuarios[key]}</option>`;
        }

        localStorage.setItem('usariosHtml', usuariosHtml);
    }
}