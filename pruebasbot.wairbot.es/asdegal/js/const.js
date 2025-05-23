let dataListColeccion = [];

const locationHandlers = {
    'AccionesAdministrador': getAccionesAdministrador,
    'BeneficiadosAdministrador': getBeneficiadosAdministrador,
    'CoordinadoresAdministrador': getCoordinadoresAdministrador,
    'VoluntariosAdministrador': getVoluntariosAdministrador,
    'AccionesCoordinador': getAccionesCoordinador,
    'BeneficiadosCoordinador': getBeneficiadosCoordinador,
    'VoluntariosCoordinador': getVoluntariosCoordinador,
    'AccionesVoluntario': getAccionesVoluntario,
    'AccionesPeriodicasAdministrador': getAccionesPeriodicasAdministrador,
    'VoluntariosInactivosAdministrador': getVoluntariosInactivosAdministrador,
    'BeneficiadosInactivosAdministrador': getBeneficiadosInactivosAdministrador,
};

const optionPrefix = `
    <option value="1">Estados Unidos</option>
    <option value="7">Rusia</option>
    <option value="20">Egipto</option>
    <option value="27">Sudáfrica</option>
    <option value="30">Grecia</option>
    <option value="31">Países Bajos</option>
    <option value="32">Bélgica</option>
    <option value="33">Francia</option>
    <option value="34">España</option>
    <option value="36">Hungría</option>
    <option value="39">Italia</option>
    <option value="40">Rumania</option>
    <option value="41">Suiza</option>
    <option value="44">Reino Unido</option>
    <option value="45">Dinamarca</option>
    <option value="46">Suecia</option>
    <option value="47">Noruega</option>
    <option value="48">Polonia</option>
    <option value="49">Alemania</option>
    <option value="51">Perú</option>
    <option value="52">México</option>
    <option value="53">Cuba</option>
    <option value="54">Argentina</option>
    <option value="55">Brasil</option>
    <option value="56">Chile</option>
    <option value="57">Colombia</option>
    <option value="58">Venezuela</option>
    <option value="60">Malasia</option>
    <option value="61">Australia</option>
    <option value="62">Indonesia</option>
    <option value="63">Filipinas</option>
    <option value="64">Nueva Zelanda</option>
    <option value="65">Singapur</option>
    <option value="66">Tailandia</option>
    <option value="81">Japón</option>
    <option value="82">Corea del Sur</option>
    <option value="84">Vietnam</option>
    <option value="86">China</option>
    <option value="90">Turquía</option>
    <option value="91">India</option>
    <option value="92">Pakistán</option>
    <option value="93">Afganistán</option>
    <option value="94">Sri Lanka</option>
    <option value="95">Myanmar</option>
    <option value="98">Irán</option>
    <option value="211">Sudán del Sur</option>
    <option value="212">Marruecos</option>
    <option value="213">Argelia</option>
    <option value="216">Túnez</option>
    <option value="218">Libia</option>
    <option value="220">Gambia</option>
    <option value="221">Senegal</option>
    <option value="222">Mauritania</option>
    <option value="223">Mali</option>
    <option value="224">Guinea</option>
    <option value="225">Costa de Marfil</option>
    <option value="226">Burkina Faso</option>
    <option value="227">Níger</option>
    <option value="228">Togo</option>
    <option value="229">Benín</option>
    <option value="230">Mauricio</option>
    <option value="231">Liberia</option>
    <option value="232">Sierra Leona</option>
    <option value="233">Ghana</option>
    <option value="234">Nigeria</option>
    <option value="235">Chad</option>
    <option value="236">República Centroafricana</option>
    <option value="237">Camerún</option>
    <option value="238">Cabo Verde</option>
    <option value="239">Santo Tomé y Príncipe</option>
    <option value="240">Guinea Ecuatorial</option>
    <option value="241">Gabón</option>
    <option value="242">República del Congo</option>
    <option value="243">República Democrática del Congo</option>
    <option value="244">Angola</option>
    <option value="245">Guinea-Bisáu</option>
    <option value="246">Diego García</option>
    <option value="248">Seychelles</option>
    <option value="249">Sudán</option>
    <option value="250">Ruanda</option>
    <option value="251">Etiopía</option>
    <option value="252">Somalia</option>
    <option value="253">Yibuti</option>
    <option value="254">Kenia</option>
    <option value="255">Tanzania</option>
    <option value="256">Uganda</option>
    <option value="257">Burundi</option>
    <option value="258">Mozambique</option>
    <option value="260">Zambia</option>
    <option value="261">Madagascar</option>
    <option value="262">Reunión</option>
    <option value="263">Zimbabue</option>
    <option value="264">Namibia</option>
    <option value="265">Malaui</option>
    <option value="266">Lesoto</option>
    <option value="267">Botsuana</option>
    <option value="268">Esuatini</option>
    <option value="269">Comoras</option>
    <option value="290">Santa Elena</option>
    <option value="291">Eritrea</option>
    <option value="297">Aruba</option>
    <option value="298">Islas Feroe</option>
    <option value="299">Groenlandia</option>
    <option value="350">Gibraltar</option>
    <option value="351">Portugal</option>
    <option value="352">Luxemburgo</option>
    <option value="353">Irlanda</option>
    <option value="354">Islandia</option>
    <option value="355">Albania</option>
    <option value="356">Malta</option>
    <option value="357">Chipre</option>
    <option value="358">Finlandia</option>
    <option value="359">Bulgaria</option>
    <option value="370">Lituania</option>
    <option value="371">Letonia</option>
    <option value="372">Estonia</option>
    <option value="373">Moldavia</option>
    <option value="374">Armenia</option>
    <option value="375">Bielorrusia</option>
    <option value="376">Andorra</option>
    <option value="377">Mónaco</option>
    <option value="378">San Marino</option>
    <option value="380">Ucrania</option>
    <option value="381">Serbia</option>
    <option value="382">Montenegro</option>
    <option value="383">Kosovo</option>
    <option value="385">Croacia</option>
    <option value="386">Eslovenia</option>
    <option value="387">Bosnia y Herzegovina</option>
    <option value="389">Macedonia del Norte</option>
    <option value="420">Chequia</option>
    <option value="421">Eslovaquia</option>
    <option value="423">Liechtenstein</option>
    <option value="500">Islas Malvinas</option>
    <option value="501">Belice</option>
    <option value="502">Guatemala</option>
    <option value="503">El Salvador</option>
    <option value="504">Honduras</option>
    <option value="505">Nicaragua</option>
    <option value="506">Costa Rica</option>
    <option value="507">Panamá</option>
    <option value="508">San Pedro y Miquelón</option>
    <option value="509">Haití</option>
    <option value="590">Guadalupe</option>
    <option value="591">Bolivia</option>
    <option value="592">Guyana</option>
    <option value="593">Ecuador</option>
    <option value="594">Guayana Francesa</option>
    <option value="595">Paraguay</option>
    <option value="596">Martinica</option>
    <option value="597">Surinam</option>
    <option value="598">Uruguay</option>
    <option value="599">Antillas Neerlandesas</option>
    <option value="670">Timor Oriental</option>
    <option value="672">Isla Norfolk</option>
    <option value="673">Brunéi</option>
    <option value="674">Nauru</option>
    <option value="675">Papúa Nueva Guinea</option>
    <option value="676">Tonga</option>
    <option value="677">Islas Salomón</option>
    <option value="678">Vanuatu</option>
    <option value="679">Fiyi</option>
    <option value="680">Palaos</option>
    <option value="681">Wallis y Futuna</option>
    <option value="682">Islas Cook</option>
    <option value="683">Niue</option>
    <option value="685">Samoa</option>
    <option value="686">Kiribati</option>
    <option value="687">Nueva Caledonia</option>
    <option value="688">Tuvalu</option>
    <option value="689">Polinesia Francesa</option>
    <option value="690">Tokelau</option>
    <option value="691">Estados Federados de Micronesia</option>
    <option value="692">Islas Marshall</option>
    <option value="850">Corea del Norte</option>
    <option value="852">Hong Kong</option>
    <option value="853">Macao</option>
    <option value="855">Camboya</option>
    <option value="856">Laos</option>
    <option value="870">Inmarsat</option>
    <option value="880">Bangladés</option>
    <option value="881">Global Mobile Satellite System</option>
    <option value="882">International Networks</option>
    <option value="883">International Networks</option>
    <option value="886">Taiwán</option>
    <option value="960">Maldivas</option>
    <option value="961">Líbano</option>
    <option value="962">Jordania</option>
    <option value="963">Siria</option>
    <option value="964">Irak</option>
    <option value="965">Kuwait</option>
    <option value="966">Arabia Saudita</option>
    <option value="967">Yemen</option>
    <option value="968">Omán</option>
    <option value="970">Palestina</option>
    <option value="971">Emiratos Árabes Unidos</option>
    <option value="972">Israel</option>
    <option value="973">Baréin</option>
    <option value="974">Catar</option>
    <option value="975">Bután</option>
    <option value="976">Mongolia</option>
    <option value="977">Nepal</option>
    <option value="992">Tayikistán</option>
    <option value="993">Turkmenistán</option>
    <option value="994">Azerbaiyán</option>
    <option value="995">Georgia</option>
    <option value="996">Kirguistán</option>
    <option value="998">Uzbekistán</option>
`;	

const optionLang = `
    <option value="es">Español</option>
    <option value="en">Inglés</option>
    <option value="fr">Francés</option>
    <option value="de">Alemán</option>
    <option value="it">Italiano</option>
    <option value="pt">Portugués</option>
    <option value="ru">Ruso</option>
    <option value="ar">Árabe</option>
    <option value="zh">Chino</option>
    <option value="ja">Japonés</option>
    <option value="ko">Coreano</option>
    <option value="hi">Hindi</option>
    <option value="bn">Bengalí</option>
    <option value="pa">Panyabí</option>
    <option value="te">Telugu</option>
    <option value="mr">Maratí</option>
    <option value="ta">Tamil</option>
    <option value="ur">Urdu</option>
    <option value="gu">Guyaratí</option>
    <option value="kn">Canarés</option>
    <option value="ml">Malayalam</option>
    <option value="th">Tailandés</option>
    <option value="vi">Vietnamita</option>
    <option value="id">Indonesio</option>
    <option value="ms">Malayo</option>
    <option value="tl">Tagalo</option>
    <option value="sw">Suajili</option>
    <option value="am">Amhárico</option>
    <option value="ha">Hausa</option>
    <option value="yo">Yoruba</option>
    <option value="ig">Igbo</option>
    <option value="zu">Zulú</option>
    <option value="xh">Xhosa</option>
    <option value="af">Afrikáans</option>
    <option value="sq">Albanés</option>
    <option value="hy">Armenio</option>
    <option value="az">Azerí</option>
    <option value="eu">Vasco</option>
    <option value="be">Bielorruso</option>
`;