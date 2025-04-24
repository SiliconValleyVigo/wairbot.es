function generarMenu(menuData) {
    let menuDesplegable = desplegarMenu(menuData);
    let menu = generarMenuHeader(menuData);
    
    let nav3_menu = document.getElementById('nav3_menu');
    if(nav3_menu !== null){
        nav3_menu.innerHTML = menu;
    }

    let nav2_menu = document.getElementById('nav2_menu');
    if(nav2_menu !== null){
        nav2_menu.innerHTML = menuDesplegable;
    }

    let usuario = JSON.parse(localStorage.getItem('usuario'));
    let rol = usuario.rol.toLowerCase();

    let administrador = document.getElementById(rol);
    if(administrador !== null){
        administrador.innerHTML = nav3_menu.innerHTML;
    }
}

function generarMenuHeader(menuData){
    let menu = '';
    for(let m of menuData){
        menu += `<div class="nav3_menu_item" onclick="${m.get}(), cerrarMenu()">${m.nombre}</div>`;
    }

    return menu;
}

function desplegarMenu(menuData){
    let menu = '';
    for(let m of menuData){
        menu += `<div class="nav2_menu_item" onclick="${m.get}()">${m.nombre}</div>`;
    }

    return menu;
}

function hamburguesa() {
    let nav3Container = document.getElementById('nav3Container');
    nav3Container.style.transform = 'translate(0)';
}

function cerrarMenu() {
    let nav3Container = document.getElementById('nav3Container');
    nav3Container.style.transform = 'translate(-100%)';
}

/*
const header1 = `
            <header class="headerGrande" id="headerGrande">
                <div class="nav1Container" id="nav1Container">
                    <div class="nav1" id="nav1">
                        <div class="logo" id="logo"></div>
                        <div class="buscador">
                            <div class="buscador_input" id="buscador_input">
                                <input type="text" name="buscador" id="buscador" placeholder="Buscar..." oninput="resetBusqueda()">
                                <button id="resetButton" onclick="resetBusqueda(true)">X</button>
                                <div class="buscador_lupa" id="buscador_lupa" onclick="aplicarBusqueda()">
                                    <img src="./imgs/lupa.svg" alt="Buscar">
                                </div>
                            </div>
                        </div>
                        <div class="usuario" id="usuario">
                            <div class="usuario_salir" id="usuario_salir" onclick="cerrarSesion()">
                                <img src="./imgs/exit.svg" alt="Salir">
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="nav2Container" id="nav2Container">
                <div class="nav2" id="nav2">
                    <div class="hamburguesa1" id="hamburguesa1" onclick="hamburguesa()">
                        <img src="./imgs/hamburguesa1.svg" alt="Menú">
                    </div>
                    <div class="nav2_menu" id="nav2_menu"></div>
                </div>
            </div>
        `;
        const header2 = `
            <header class="headerSmall" id="headerSmall">
                <div class="nav1Container" id="nav1Container">
                    <div class="nav1" id="nav1">
                        <div class="logo" id="logo"></div>
                    </div>
                </div>
            </header>
            <div class="nav2ContainerSmall" id="nav2ContainerSmall">
                <div class="nav2" id="nav2">
                    <div class="hamburguesa1" id="hamburguesa1" onclick="hamburguesa()">
                        <img src="./imgs/hamburguesa2.svg" alt="Menú">
                    </div>
                    <div class="buscador">
                        <div class="buscador_input" id="buscador_input">
                            <input type="text" name="buscador" id="buscador" placeholder="Buscar..." oninput="resetBusqueda()">
                            <button id="resetButton" onclick="resetBusqueda(true)">X</button>
                            <div class="buscador_lupa" id="buscador_lupa" onclick="aplicarBusqueda()">
                                <img src="./imgs/lupa.svg" alt="Buscar">
                            </div>
                        </div>
                    </div>
                    <div class="usuario_salir" id="usuario_salir" onclick="cerrarSesion()">
                        <img src="./imgs/exit.svg" alt="Salir">
                    </div>
                </div>
            </div>
        `;
*/

function navCondensed(condensed = false){
    console.log('navCondesed');
    if(condensed){
        //obtener usuario_salir
        let usuario_salir = document.querySelectorAll('.usuario_salir');

        console.log(usuario_salir.length);

        //si hay más de 1 no hacer nada, si no, obtener el primero
        if(usuario_salir.length === 2){
            return;
        }else{
            usuario_salir = usuario_salir[0];
        }

        //obtener nav2
        let nav2 = document.getElementById('nav2');

        //insertar usuario_salir en nav2
        nav2.appendChild(usuario_salir);

        //justifi-content: flex-end
        nav2.style.justifyContent = 'flex-end';

        let nav2Container = document.getElementById('nav2Container');
        if(nav2Container !== null){
            nav2Container.style.position = 'static';
        }

        //ocultar nav1
        let nav1 = document.getElementById('nav1');
        nav1.style.display = 'none';

        //ocultar hamburguesa1
        let hamburguesa1 = document.getElementById('hamburguesa1');
        hamburguesa1.style.display = 'none';

        //ocultar nav2_menu
        let nav2_menu = document.getElementById('nav2_menu');
        if(nav2_menu !== null){
            nav2_menu.style.display = 'none';
        }

        //ocultar la clase buscador
        let buscador = document.querySelectorAll('.buscador');
        buscador.forEach(b => {
            b.style.display = 'none';
        });

    }else{
        //comprobar si nav1 está oculto
        let nav1 = document.getElementById('nav1');

        if(nav1.style.display === 'none'){
            //obtener usuario_salir
            let usuario_salir = document.querySelectorAll('.usuario_salir');

            //obtener nav1
            let nav1 = document.getElementById('nav1');

            //insertar usuario_salir en nav1
            nav1.appendChild(usuario_salir[0]);

            //mostrar nav1
            nav1.style.display = 'flex';

            //mostrar la clase buscador
            let buscador = document.querySelectorAll('.buscador');
            buscador.forEach(b => {
                b.style.display = 'flex';
            });

            let nav2 = document.getElementById('nav2');
            nav2.style.justifyContent = 'normal';

            //obtener nav2
            let nav2Container = document.getElementById('nav2Container');

            //quitar a nav2 la propiedad position: por defecto y poner la propiedad position: sticky
            nav2Container.style.position = 'sticky';

            //mostrar hamburguesa1
            let hamburguesa1 = document.getElementById('hamburguesa1');
            hamburguesa1.style.display = 'block';

            //mostrar nav2_menu
            let nav2_menu = document.getElementById('nav2_menu');
            nav2_menu.style.display = 'flex';
        }
    }
}
