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
