function login(){
    let email = document.getElementById('loginEmail').value;
    let password = document.getElementById('loginPassword').value;
    
    let data = {
        email: email,
        password: password
    };

    query(data, 'login', 'saveUserData');
}