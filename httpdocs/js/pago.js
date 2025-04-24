function pago(){
    let userData = localStorage.getItem("wairbotTrelloUserData");

    //si no es null convertir a objeto
    if (userData != null) {
        userData = JSON.parse(userData);
    } else {
        autoNavigation();
        return;
    }

    let email = userData.email;

    //abrir la pagina de pago /info/precios/ en otra ventana con el email en la url
    
    let url = "/info/precios/?email=" + email;
    window.open(url, "_blank");
    
}