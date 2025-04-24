function recuperarContrasena() {
    let email = document.getElementById("recuperarEmail").value;
    
    let data = {
        email: email
    };
    
    query(data, "recuperarContrasena", "recuperarToLogin");
}