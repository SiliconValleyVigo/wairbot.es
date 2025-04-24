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

    let usuario = datos.usuario;

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

function generarMenuData(usuario){
    if(usuario.rol === 'VOLUNTARIO'){
        document.getElementById('VOLUNTARIO').style.display = 'flex';

        let menuData = [
            {
                nombre: 'Acciones',
                get: 'getAccionesVoluntario',
            }
        ];
        
        generarMenu(menuData);
    }
    
    if(usuario.rol === 'ADMINISTRADOR'){
        document.getElementById('ADMINISTRADOR').style.display = 'flex';

        let menuOptions = {
            'Acciones': 'getAccionesAdministrador',
            'Acciones Periodicas': 'getAccionesPeriodicasAdministrador',
            'Beneficiarios': 'getBeneficiadosAdministrador',
            'Beneficiarios Baja': 'getBeneficiadosInactivosAdministrador',
            'Coordinadores': 'getCoordinadoresAdministrador',
            'Voluntarios': 'getVoluntariosAdministrador',
            'Voluntarios Baja': 'getVoluntariosInactivosAdministrador'
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
    }

    if(usuario.rol === 'COORDINADOR'){
        document.getElementById('COORDINADOR').style.display = 'flex';

        let menuOptions = {
            'Acciones': 'getAccionesCoordinador',
            'Beneficiarios': 'getBeneficiadosCoordinador',
            'Voluntarios': 'getVoluntariosCoordinador'
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
    }
}