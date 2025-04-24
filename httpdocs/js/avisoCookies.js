//comprobar si se han aceptado las cookies
if (localStorage.getItem("cookies") != "aceptadas") {
  let bannerCookies = `
<div 
    style="
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        bottom: 0;
        width: 100%;
        background-color: #79ceff;
        padding: 10px 0
    " 
    id="bannerCookies"
><div>
    <span data-translate="estaWebUsaCookiesPuedesVerLa">Esta Web usa cookies, puedes ver la</span> 
    <a href="../info/cookies/" target="_blank" data-translate="politicaDeCookies">
        política de cookies
    </a>
</div>
    <button
        style="
            margin-left: 10px;
            margin-top: 0;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            box-shadow: none;
            background-color: #0079bf; 
            color: #fff;
            font-size: 1em; 
            cursor: pointer;
        " 
        onclick="localStorage.setItem('cookies', 'aceptadas'); this.parentElement.style.display = 'none';" data-translate="aceptar"
    >Aceptar
    </button>
</div>`;

  document.body.innerHTML += bannerCookies;
}
