///////////////////////////////////////////////// GRID PRINCIPAL /////////////////////////////////////////////////
async function getSocio(nPagina = 1) {
    //obtener el id del socio de localStorage
    let usuario = localStorage.getItem('usuario');
    usuario = JSON.parse(usuario);

    let id = usuario.id;

    await openSocio(id);
}