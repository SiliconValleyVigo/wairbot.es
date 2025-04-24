//comprobar si hay token en localstorage
let token = localStorage.getItem('token');

if(token === null || token === undefined || token === 'undefined' || token === '' || token === 'null'){
    mostrarSection('login');
}else{
    query('getAllData', 'getAll', {}, 'guardarDatosLogin');
}

function mostrarSection(section) {
    let sections = document.querySelectorAll('section');
    sections.forEach(s => {
        s.style.display = 'none';
    });

    document.getElementById(section).style.display = 'flex';

    let thisUrl = window.location.href;
    
    if(section === 'login'){
        let htmlLogin = `
            <form>
                <img src="${thisUrl}/imgs/logo.svg" alt="Logo" class="logo">
                <h2>LOGIN</h2>
                <div class="inputGroup">
                    <label>Usuario o email</label>
                    <input type="text" id="usuarioLogin" required>
                </div>
                <div class="inputGroup">
                    <label>Contraseña</label>
                    <input type="password" id="passwordLogin" required>
                </div>
                <button onclick="login(event)">Entrar</button>
                <small id="avisoLogin"></small>
            </form>
        `;

        document.getElementById('login').innerHTML = htmlLogin;
    }
}