/*
// Obtener key por id
let search3 = document.getElementById('search3');
let key = search3.getSelectedKey();

let key = document.getElementById('search3').getSelectedKey();

<data-search 
    id="search3"
    data='{"id7": "Strawberry", "id8": "Pineapple", "id9": "Blueberry"}'
    default-value="id8"
    label="Frutas">
</data-search>
*/

class DataSearch extends HTMLElement {
    constructor() {
        super();

        // Crear el shadow DOM
        this.attachShadow({ mode: 'open' });

        // Crear el contenedor principal
        this.container = document.createElement('div');
        this.container.className = 'modalInputGroup';

        // Crear el elemento de la etiqueta
        this.labelElement = document.createElement('div');
        this.labelElement.className = 'modalLabel';

        // Crear el input
        this.input = document.createElement('input');
        this.input.id = this.id;

        // Agregar los elementos al contenedor
        this.container.appendChild(this.labelElement);
        this.container.appendChild(this.input);

        // Agregar el contenedor al shadow DOM
        this.shadowRoot.appendChild(this.container);

        //obtener la url
        let url = window.location.href;
        let urlCss = url + 'css/inputs.css';

        const link = document.createElement('link');
        link.setAttribute('rel', 'stylesheet');
        link.setAttribute('href', urlCss);

        // Agregar el elemento de enlace al shadow DOM
        this.shadowRoot.appendChild(link);

        // Manejar eventos de selección
        this.input.addEventListener('input', () => this.onInputChange());

        // Almacenar la key seleccionada
        this.selectedKey = null;
    }

    // Observar cambios en los atributos data, default-value y label
    static get observedAttributes() {
        return ['data', 'default-value', 'label'];
    }

    // Función que maneja cambios en los atributos
    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'data') {
            this.data = JSON.parse(newValue);
            this.updateDatalist();
        } else if (name === 'default-value') {
            this.defaultValue = newValue;
            this.setDefaultValue();
        } else if (name === 'label') {
            this.labelElement.textContent = newValue;
        }
    }

    // Función que actualiza las opciones del datalist
    updateDatalist() {
        // Limpiar el datalist si existe
        if (this.datalist) {
            this.datalist.innerHTML = '';
        }

        // Verificar si this.data es un objeto vacío
        if (this.data && Object.keys(this.data).length > 0) {
            // Crear el datalist si no existe
            if (!this.datalist) {
                this.datalist = document.createElement('datalist');
                this.datalist.id = `${this.id}_list`;
                this.input.setAttribute('list', this.datalist.id);
                this.container.appendChild(this.datalist);
            }

            Object.keys(this.data).forEach((dataKey) => {
                const option = document.createElement('option');
                option.value = this.data[dataKey]; // Mostrar el valor en el datalist
                option.dataset.key = dataKey; // Asignar la key como data attribute
                this.datalist.appendChild(option);
            });
        } else {
            // Eliminar el datalist si existe y data es un objeto vacío
            if (this.datalist) {
                this.container.removeChild(this.datalist);
                this.datalist = null;
                this.input.removeAttribute('list');
            }
        }

        // Establecer el valor por defecto si está presente
        this.setDefaultValue();
    }

    // Función para establecer el valor por defecto
    setDefaultValue() {
        if (this.defaultValue && this.data) {
            const defaultValue = this.data[this.defaultValue];

            if (defaultValue) {
                this.input.value = defaultValue;
                this.selectedKey = this.defaultValue;
            }
        }
    }

    // Función que se ejecuta cuando el usuario ingresa o selecciona un valor
    onInputChange() {
        const inputValue = this.input.value;

        // Encontrar la clave correspondiente al valor seleccionado
        const selectedKey = Object.keys(this.data).find(
            (key) => this.data[key] === inputValue
        );

        if (selectedKey) {
            // Asignar la clave seleccionada (internamente)
            this.selectedKey = selectedKey;
        } else {
            // Si no encuentra coincidencia, no se selecciona ninguna clave
            this.selectedKey = null;
        }
    }

    // Método para obtener la clave seleccionada
    getSelectedKey() {
        return this.selectedKey;
    }
}

// Registrar el componente customizado
customElements.define('data-search', DataSearch);