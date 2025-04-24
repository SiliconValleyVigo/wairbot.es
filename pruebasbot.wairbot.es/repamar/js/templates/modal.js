function createModal(titulo, contenido, funcionGuardar, usuario, id = null, funcionCerrar = null, parametrosCerrar = null, idContainer = ""){
    if(funcionCerrar !== null){
        if(parametrosCerrar !== null){
            let parametros = '';
            parametrosCerrar.forEach(p => {
                parametros += `${p},`;
            });
            parametros = parametros.slice(0, -1);
            funcionCerrar = `${funcionCerrar}(${parametros})`;
        }else{
            funcionCerrar = `${funcionCerrar}()`;
        }
    }else{
        funcionCerrar = "destruirModal()";
    }

    if(id !== null){
        funcionGuardar = `${funcionGuardar}(${id})`;
    }else{
        funcionGuardar = `${funcionGuardar}()`;
    }

    if(idContainer !== ""){
        idContainer = "id= '" + idContainer + "'";
    }
    
    let modal = `
    <div class="modal" id="modal">
        <div class="modalContenido" ${idContainer}>
            <div class="modalHeader">
                <div class="modalTitulo" id="${id}">${titulo}</div>
                <div class="cerrarModal" id="cerrarModal" onclick="destruirModal()">&times;</div>
            </div>
            <div class="modalBody">
                <div class="modalBodyContenido">
                    ${contenido}
                </div>
            </div>
            <div class="modalFooter">
                <div class="modalBoton" onclick="${funcionGuardar}">Guardar</div>
                <div class="modalBoton" id="cerrarModal" onclick="destruirModal()">Cancelar</div>
            </div>
        </div>
    </div>
    `;

    //insertar modal en el id usuario en ultimo lugar
    document.getElementById(usuario).innerHTML += modal;
}

function destruirModal(){
    //obtener todos los elementos con la clase modal y destruir el ultimo de la lista en el DOM
    let modales = document.getElementsByClassName('modal');
    modales[modales.length - 1].remove();
}

/////////////////////////////////////// MODAL AVISO ///////////////////////////////////////

function createModalAviso(titulo, mensaje, funcionAceptar = null, arrayParametros = null){
    let botonAceptar = '';

    if(funcionAceptar !== null){
        if(arrayParametros !== null){
            let parametros = '';
            arrayParametros.forEach(p => {
                parametros += `${p},`;
            });
            parametros = parametros.slice(0, -1);
            botonAceptar = `<div class="modalAvisoBoton" id="modalAvisoBoton" onclick="${funcionAceptar}('${parametros}')">Aceptar</div>`;
        }else{
            botonAceptar = `<div class="modalAvisoBoton" id="modalAvisoBoton" onclick="${funcionAceptar}()">Aceptar</div>`;
        }
    }

    let modalAviso = `
    <div class="modalAvisoContainer" id="modalAvisoContainer">
        <div class="modalAviso" id="modalAviso">
            <div class="modalAvisoHeader">
                <div class="modalAvisoTitulo" id="modalAvisoTitulo">${titulo}</div>
                <div class="modalAvisoCerrar" id="modalAvisoCerrar" onclick="destruirModalAviso()">&times;</div>
            </div>
            <div class="modalAvisoMensaje" id="modalAvisoMensaje">${mensaje}</div>
            <div class="modalAvisoFooter">
                ${botonAceptar}
                <div class="modalAvisoBoton" id="modalAvisoBoton" onclick="destruirModalAviso()">Cerrar</div>
            </div>
        </div>
    </div>
    `;
    document.body.innerHTML += modalAviso;
}

function destruirModalAviso(){
    let modalAvisoContainer = document.getElementById('modalAvisoContainer');
    modalAvisoContainer.remove();
}
