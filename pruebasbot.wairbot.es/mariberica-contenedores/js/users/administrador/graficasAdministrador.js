

async function getGraficasAdministrador() {
  let administrador = document.getElementById('ADMINISTRADOR');

  //fecha fin es la fecha actual y fecha inicio es el primer día del mes actual salvo que fecha inicio sea 1 entonces es el primer día del mes anterior
  let fechaFin = new Date().toISOString().split('T')[0];
  let fechaInicio = new Date();
  if (fechaInicio.getDate() === 1) {
    fechaInicio.setMonth(fechaInicio.getMonth() - 1);
  }
  fechaInicio.setDate(1);
  fechaInicio = fechaInicio.toISOString().split('T')[0];

  let clase = 'Graficas';
  let funcion = 'obtenerGraficas';
  let datos = {
    fecha_inicial : fechaInicio,
    fecha_final : fechaFin
  };

  let data = await queryAsync(clase, funcion, datos);

  console.log(data);
  
  let html = `
    <style>
      .inputsFechasContainer {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        width: calc(100% - 20px);
      }
      .inputFecha {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
      }
      .inputFecha label {
        margin-bottom: 5px;
      }
      .inputFecha input {
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
      }
      .tituloGrid {
        text-align: left;
        margin-bottom: 20px;
      }
      .grid-container {
        width: calc(100% - 20px);
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 10px;
        padding: 10px;
      }

      @media (max-width: 600px) {
        .grid-container {
          grid-template-columns: 1fr;
        }
      }
      .grid-item {
        background-color:rgb(255, 255, 255);
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
      }
      .grid-item h3 {
        margin-bottom: 5px;
        margin-top: 0;
        color: #306ff8;
      }
    </style>
    <!-- Inptus de fecha inicio y fecha fin -->
    <div class="inputsFechasContainer">
      <div class="tituloGrid">
        <h2>Gráficas</h2>
      </div>
      <div class="inputFecha">
        <label for="fechaInicio">Fecha de inicio</label>
        <input type="date" id="fechaInicio" name="fechaInicio" value="${fechaInicio}" onchange="actualizarFechasGraficasAdministrador()">
      </div>
      <div class="inputFecha">
        <label for="fechaFin">Fecha de fin</label>
        <input type="date" id="fechaFin" name="fechaFin" value="${fechaFin}" onchange="actualizarFechasGraficasAdministrador()">
      </div>
    </div>
    <div class="grid-container" id="graficasContainer">
      <!-- Aquí puedes agregar dinámicamente los elementos de la grid -->
      <div class="grid-item">Gráfica 1</div>
      <div class="grid-item">Gráfica 2</div>
      <div class="grid-item">Gráfica 3</div>
      <!-- Agrega más elementos según sea necesario -->
    </div>
  `;

  administrador.innerHTML = html;

  insertarGraficasActualizadasAdministador(data);
}

async function actualizarFechasGraficasAdministrador() {
  let fechaInicio = document.getElementById('fechaInicio').value;
  let fechaFin = document.getElementById('fechaFin').value;

  //la fecha de inicio no puede ser mayor que la fecha de fin
  if (fechaInicio > fechaFin) {
    alert('La fecha de inicio no puede ser mayor que la fecha de fin');
    return;
  }

  let clase = 'Graficas';
  let funcion = 'obtenerGraficas';
  let datos = {
    fecha_inicial : fechaInicio,
    fecha_final : fechaFin
  };

  let data = await queryAsync(clase, funcion, datos);
  console.log(data);

  insertarGraficasActualizadasAdministador(data);
}

/*
Ejemplo de data:
[
    {
        "tipo": "line",
        "titulo": "Compras por mes",
        "periodoSolicitado": {
            "labels": ["Enero-21", "Febrero-21", "Marzo-21", "Abril-21", "Mayo-21", "Junio-21"],
            "data": [10, 20, 30, 40, 50, 60],
            "name": "Compras por mes de 01/2021 a 06/2021"
        },
        "periodoAnterior": {
            "labels": ["Comparativa con Enero-20", "Comparativa con Febrero-20", "Comparativa con Marzo-20", "Comparativa con Abril-20", "Comparativa con Mayo-20", "Comparativa con Junio-20"],
            "data": [5, 15, 25, 35, 45, 55],
            "name": "Compras por mes de 01/2020 a 06/2020"
        }
    },
    {
        "tipo": "line",
        "titulo": "Valor de las compras por mes",
        "periodoSolicitado": {
            "labels": ["Enero-21", "Febrero-21", "Marzo-21", "Abril-21", "Mayo-21", "Junio-21"],
            "data": [1000, 2000, 3000, 4000, 5000, 6000],
            "name": "Valor de las compras por mes de 01/2021 a 06/2021"
        },
        "periodoAnterior": {
            "labels": ["Comparativa con Enero-20", "Comparativa con Febrero-20", "Comparativa con Marzo-20", "Comparativa con Abril-20", "Comparativa con Mayo-20", "Comparativa con Junio-20"],
            "data": [500, 1500, 2500, 3500, 4500, 5500],
            "name": "Valor de las compras por mes de 01/2020 a 06/2020"
        }
    },
    {
        "tipo": "line",
        "titulo": "Contenedores por mes",
        "periodoSolicitado": {
            "labels": ["Enero-21", "Febrero-21", "Marzo-21", "Abril-21", "Mayo-21", "Junio-21"],
            "data": [2, 4, 6, 8, 10, 12],
            "name": "Contenedores por mes de 01/2021 a 06/2021"
        },
        "periodoAnterior": {
            "labels": ["Comparativa con Enero-20", "Comparativa con Febrero-20", "Comparativa con Marzo-20", "Comparativa con Abril-20", "Comparativa con Mayo-20", "Comparativa con Junio-20"],
            "data": [1, 3, 5, 7, 9, 11],
            "name": "Contenedores por mes de 01/2020 a 06/2020"
        }
    },
    {
        "tipo": "line",
        "titulo": "Promedio de productos por contenedor",
        "periodoSolicitado": {
            "labels": ["Enero-21", "Febrero-21", "Marzo-21", "Abril-21", "Mayo-21", "Junio-21"],
            "data": [50, 60, 70, 80, 90, 100],
            "name": "Promedio de productos por contenedor de 01/2021 a 06/2021"
        },
        "periodoAnterior": {
            "labels": ["Comparativa con Enero-20", "Comparativa con Febrero-20", "Comparativa con Marzo-20", "Comparativa con Abril-20", "Comparativa con Mayo-20", "Comparativa con Junio-20"],
            "data": [45, 55, 65, 75, 85, 95],
            "name": "Promedio de productos por contenedor de 01/2020 a 06/2020"
        }
    },
    {
        "tipo": "line",
        "titulo": "Número de productos comprados por mes",
        "periodoSolicitado": {
            "labels": ["Enero-21", "Febrero-21", "Marzo-21", "Abril-21", "Mayo-21", "Junio-21"],
            "data": [100, 200, 300, 400, 500, 600],
            "name": "Número de productos comprados por mes de 01/2021 a 06/2021"
        },
        "periodoAnterior": {
            "labels": ["Comparativa con Enero-20", "Comparativa con Febrero-20", "Comparativa con Marzo-20", "Comparativa con Abril-20", "Comparativa con Mayo-20", "Comparativa con Junio-20"],
            "data": [90, 180, 270, 360, 450, 540],
            "name": "Número de productos comprados por mes de 01/2020 a 06/2020"
        }
    },
    {
        "tipo": "bar",
        "titulo": "Proveedores por número de compras",
        "labels": ["Proveedor A", "Proveedor B", "Proveedor C"],
        "data": [10, 20, 30]
    },
    {
        "tipo": "bar",
        "titulo": "Presentaciones por número de compras",
        "labels": ["Presentación A", "Presentación B", "Presentación C"],
        "data": [15, 25, 35]
    },
    {
        "tipo": "bar",
        "titulo": "Productos por número de compras",
        "labels": ["Producto A", "Producto B", "Producto C"],
        "data": [5, 10, 15]
    },
    {
        "tipo": "bar",
        "titulo": "Número de contenedores por estado de embarque",
        "labels": ["En tránsito", "Entregado", "Pendiente"],
        "data": [8, 12, 5]
    },
    {
        "tipo": "bar",
        "titulo": "Número de productos por estado de embarque de contenedor",
        "labels": ["En tránsito", "Entregado", "Pendiente"],
        "data": [80, 120, 50]
    },
    {
        "tipo": "bar",
        "titulo": "Número de Contenedores por naviera",
        "labels": ["Naviera A", "Naviera B", "Naviera C"],
        "data": [3, 6, 9]
    }
]

*/
async function insertarGraficasActualizadasAdministador(data) {
  let graficasContainer = document.getElementById('graficasContainer');
  graficasContainer.innerHTML = '';

  let html = '';
  for (let grafica of data) {
    if (grafica.tipo === 'line') {
      html += `
        <div class="grid-item">
          <h3>${grafica.titulo}</h3>
          <canvas id="lineChart${data.indexOf(grafica)}"></canvas>
        </div>
      `;
    } else if (grafica.tipo === 'bar') {
      html += `
        <div class="grid-item">
          <h3>${grafica.titulo}</h3>
          <canvas id="barChart${data.indexOf(grafica)}"></canvas>
        </div>
      `;
    }
  }

  graficasContainer.innerHTML = html;

  for (let grafica of data) {
    if (grafica.tipo === 'line') {
      var ctx = document.getElementById(`lineChart${data.indexOf(grafica)}`).getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: grafica.periodoSolicitado.labels,
          datasets: [{
            label: grafica.periodoSolicitado.name,
            data: grafica.periodoSolicitado.data,
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            fill: false
          }, {
            label: grafica.periodoAnterior.name,
            data: grafica.periodoAnterior.data,
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1,
            fill: false
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    } else if (grafica.tipo === 'bar') {
      var ctx = document.getElementById(`barChart${data.indexOf(grafica)}`).getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: grafica.labels,
          datasets: [{
            label: grafica.titulo,
            data: grafica.data,
            backgroundColor: [
              'rgba(255, 99, 132, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(255, 206, 86, 0.2)',
              'rgba(75, 192, 192, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  }
}
