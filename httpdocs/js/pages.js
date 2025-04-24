function toggleMenu() {
    var nav = document.querySelector('nav');
    nav.classList.toggle('show');
}

//si se hace click fuera del menu, comprueba si esta abierto y lo cierra
window.onclick = function(event) {
    if (!event.target.matches('.menu-button')) {
        var nav = document.querySelector('nav');
        if (nav.classList.contains('show')) {
            nav.classList.remove('show');
        }
    }
}

//si se desliza el dedo fuera del menu, comprueba si esta abierto y lo cierra
window.ontouchmove = function(event) {
    if (!event.target.matches('.menu-button')) {
        var nav = document.querySelector('nav');
        if (nav.classList.contains('show')) {
            nav.classList.remove('show');
        }
    }
}