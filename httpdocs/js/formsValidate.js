function togglePasswordVisibility(tipo) {
    var passwordField = document.getElementById(tipo + "Password");
    if (passwordField.type === "password") {
        passwordField.type = "text";
    } else {
        passwordField.type = "password";
    }
}

function validateForm(tipo) {
    var emailField = document.getElementById(tipo + "Email");
    var passwordField = document.getElementById(tipo + "Password");

    // Validar el formato del correo electrónico
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailField.value)) {
        let aviso = document.getElementById(tipo + "Aviso");
        aviso.innerHTML = "El correo electrónico no es válido";
        return false;
    }

    // Validar la longitud mínima de la contraseña
    if (passwordField.value.length < 8) {
        let aviso = document.getElementById(tipo + "Aviso");
        return false;
    }

    // El formulario se enviará si todas las validaciones pasan
    return true;
}