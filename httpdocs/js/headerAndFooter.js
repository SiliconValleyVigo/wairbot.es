let footerInfo = `
    <section class="footerEnlaces">
        <div>
            <a href="/info/ayuda/" data-translate="Ayuda">Ayuda</a>
            <a href="/info/precios/" data-translate="Precios">Precios</a>
            <a href="https://billing.stripe.com/p/login/9AQg183UzeW3cdacMM" data-translate="Facturacion" target="_blank">Facturación</a>
        </div>
        <div>
            <a href="/info/proteccion-de-datos/" data-translate="Privacidad">Privacidad</a>
            <a href="/info/condiciones/" data-translate="Terminos">Términos</a>
            <a href="/info/cookies/" data-translate="Cookies">Cookies</a>
        </div>
    </section>

    <section class="footerFirma">
        <span data-translate="footerFirmaText">Wairbot Text Autocomplete es un producto de</span>
        <a href="https://siliconvalleyvigo.com" target="_blank"> <img src="/assets/logoSVV.png" alt="logoSVV" height="20px"> </a>
    </section>
`;

//añaadir el footer en la etiqueta footer
document.querySelector("footer").innerHTML = footerInfo;

let headerInfo = `
    <a href="#" id="logo" style="min-height: 65px;">
        <img src="/assets/logoWairbotTA.png" alt="logo" height="60px" style="display: block;">
    </a>
    <button class="menu-button" onclick="toggleMenu()">☰</button>
    <nav>
        <a href="/info/ayuda/" data-translate="Ayuda">Ayuda</a>
        <a href="/info/precios/" data-translate="Precios">Precios</a>
        <a href="https://billing.stripe.com/p/login/9AQg183UzeW3cdacMM" target="_blank" data-translate="Facturacion">Facturación</a>
    </nav>
`;

//añadir el header en la etiqueta header
document.querySelector("header").innerHTML = headerInfo;
  