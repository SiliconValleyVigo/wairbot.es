function trelloAuth(){
    wairbotTrelloUserData = localStorage.getItem('wairbotTrelloUserData');
    userData = {};

    if(wairbotTrelloUserData == null){
        document.getElementById('login').style.display = 'flex';
    }else{
        userData = JSON.parse(wairbotTrelloUserData);
    }

    query(userData, 'getUserData', 'saveUserData');
}