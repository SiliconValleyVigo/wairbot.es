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
    console.log(usuario);

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
    console.log(usuario);
    if(usuario.rol === 'ADMINISTRADOR'){
        //guardar en localStorage nombresDeAgentes, nombresDeTecnicos y nombresDeClientes, nombresDeMotores, solicitudes
        localStorage.setItem('nombresDeAgentes', JSON.stringify(datos.nombresDeAgentes));
        localStorage.setItem('nombresDeTecnicos', JSON.stringify(datos.nombresDeTecnicos));
        localStorage.setItem('nombresDeClientes', JSON.stringify(datos.nombresDeClientes));
        localStorage.setItem('nombresDeMotores', JSON.stringify(datos.nombresDeMotores));
        localStorage.setItem('solicitudes', JSON.stringify(datos.solicitudes));

        document.getElementById('administrador').style.display = 'flex';

        let menuData = [
            {
                nombre: 'AGENTES',
                get: 'getAgentes',
            },{
                nombre: 'TÉCNICOS',
                get: 'getTecnicos',
            },{
                nombre: 'CLIENTES',
                get: 'getClientes',
            },{
                nombre: 'MOTORES EN STOCK',
                get: 'getStock',
            },{
                nombre: 'MOTORES VENDIDOS',
                get: 'getVendidos',
            },{
                nombre: 'SOLICITUDES',
                get: 'getSolicitudes',
            },{
                nombre: 'PUBLICIDAD',
                get: 'getPublicidad',
            }
        ];
        generarMenu(menuData);
    }
    
    if(usuario.rol === 'AGENTE'){
        localStorage.setItem('nombresDeTecnicos', JSON.stringify(datos.nombresDeTecnicos));
        localStorage.setItem('nombresDeClientes', JSON.stringify(datos.nombresDeClientes));
        localStorage.setItem('nombresDeMotores', JSON.stringify(datos.nombresDeMotores));

        document.getElementById('agente').style.display = 'flex';

        let menuData = [
            {
                nombre: 'TÉCNICOS',
                get: 'getTecnicosAgente',
            },{
                nombre: 'CLIENTES',
                get: 'getClientesAgente',
            },{
                nombre: 'MOTORES EN STOCK',
                get: 'getStockAgente',
            },{
                nombre: 'MOTORES VENDIDOS',
                get: 'getVendidosAgente',
            }
        ];

        generarMenu(menuData);
 
    }

    if(usuario.rol === 'TECNICO'){
        document.getElementById('tecnico').style.display = 'flex';

        let menuData = [
{
                nombre: 'PARTES ASIGNADOS',
                get: 'getPartesTecnico',
            }
        ];

        generarMenu(menuData);
    }

    if(usuario.rol === 'CLIENTE'){
        let publicidad = datos.publicidad.data;
        localStorage.setItem('publicidad', JSON.stringify(publicidad));

        document.getElementById('cliente').style.display = 'flex';

        let menuData = [
            {
                nombre: 'MOTORES',
                get: 'getVendidosCliente',
            },{
                nombre: 'NOTIFICACIONES',
                get: 'getNotificacionesCliente',
            }
        ];

        generarMenu(menuData);
    }
}