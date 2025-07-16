document.addEventListener("DOMContentLoaded", async () => {
    try {
        const res = await fetch('../procesadores/datos.php');
        const data = await res.json();

        // Insertar datos rápidos
        document.querySelectorAll('.stat-item')[0].querySelector('.stat-value').textContent = data.total_alumnos;
        document.querySelectorAll('.stat-item')[1].querySelector('.stat-value').textContent = data.asistencias_hoy;
        document.querySelectorAll('.stat-item')[2].querySelector('.stat-value').textContent = data.promedio_asistencia + '%';
        document.querySelectorAll('.stat-item')[3].querySelector('.stat-value').textContent = data.total_especialidades;


        // Insertar tarjeta de especialidades
        const totalCard = document.querySelectorAll('.card')[0];
        totalCard.innerHTML = '<h3>Total Alumnos</h3>';
        totalCard.innerHTML += `<p><strong>${data.total_alumnos}</strong></p>`;

        // Insertar tarjeta de especialidades
        const especialidadCard = document.querySelectorAll('.card')[1];
        especialidadCard.innerHTML = '<h3>Especialidades</h3>';
        data.por_especialidad.forEach(e => {
            especialidadCard.innerHTML += `<p>${e.especialidad}: ${e.total}</p>`;
        });

        // Insertar tarjeta de ciclos
        const cicloCard = document.querySelectorAll('.card')[2];
        cicloCard.innerHTML = '<h3>Ciclos</h3>';
        data.por_ciclo.forEach(c => {
            cicloCard.innerHTML += `<p>Ciclo ${c.ciclo}: ${c.total}</p>`;
        });

    } catch (error) {
        console.error('Error cargando estadísticas:', error);
    }

    
});







function mostrarSeccion(id) {
    // Remover clase active de todas las secciones
    document.querySelectorAll('.section').forEach(sec => {
        sec.classList.remove('active');
    });

    // Mostrar la sección seleccionada
    const seccionSeleccionada = document.getElementById(id);
    if (seccionSeleccionada) {
        seccionSeleccionada.classList.add('active');
    }

    // Cerrar sidebar en móvil
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (sidebar) sidebar.classList.remove('active');
    if (overlay) overlay.classList.remove('active');

    // Actualizar enlaces activos
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
    });
    
    const linkActivo = document.querySelector(`[onclick="mostrarSeccion('${id}')"]`);
    if (linkActivo) linkActivo.classList.add('active');

    // Cargar datos específicos de la sección
    cargarDatosSeccion(id);
}


// ----------------------------------------------------------------------

function renderGraficoSemana(canvasId, data) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map(d => d.dia),
      datasets: [{
        label: "Asistencias",
        data: data.map(d => d.asistencias),
        backgroundColor: "rgba(75, 192, 192, 0.7)"
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "top" },
        title: { display: true, text: "Asistencias de la Semana" }
      }
    }
  });
}

function renderGraficoAnual(canvasId, data) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.map(d => `Mes ${d.mes}`),
      datasets: [{
        label: "Asistencias",
        data: data.map(d => d.asistencias),
        backgroundColor: "rgba(153, 102, 255, 0.7)"
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: "Asistencias Anuales" }
      }
    }
  });
}

function renderGraficoCircular(canvasId, data) {
  const ctx = document.getElementById(canvasId).getContext("2d");
  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Asistencias", "Faltas"],
      datasets: [{
        data: [data.asistencias, data.faltas],
        backgroundColor: ["rgba(54, 162, 235, 0.7)", "rgba(255, 99, 132, 0.7)"]
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: "Asistencia del Día" }
      }
    }
  });
}

const años = (() => {
  const actual = new Date().getFullYear();
  const arr = [];
  for (let i = actual; i >= actual - 5; i--) arr.push(i);
  return arr;
})();

function poblarSelectAños(especialidad) {
  const select = document.getElementById(`año${capitalize(especialidad)}`);
  if (!select) return;
  años.forEach(a => {
    const option = document.createElement("option");
    option.value = a;
    option.textContent = a;
    select.appendChild(option);
  });
  select.value = new Date().getFullYear();
}

function cambiarCiclo(especialidad, ciclo) {
  window[`cicloSeleccionado${especialidad}`] = ciclo;
  actualizarGraficosEspecialidad(especialidad);
}

function actualizarGraficosEspecialidad(especialidad) {
  const año = document.getElementById(`año${capitalize(especialidad)}`).value;
  const ciclo = window[`cicloSeleccionado${especialidad}`] || "total";

  fetch(`../procesadores/tipos datos/datos_por_especialidad.php?especialidad=${especialidad}&anio=${año}&ciclo=${ciclo}`)
    .then(res => res.json())
    .then(data => {
      renderGraficoSemana(`graficoSemana${capitalize(especialidad)}`, data.semana);
      renderGraficoAnual(`graficoAnual${capitalize(especialidad)}`, data.anual);
      renderGraficoCircular(`graficoDia${capitalize(especialidad)}`, data.hoy);
    })
    .catch(err => console.error("Error al cargar los datos:", err));
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

document.addEventListener("DOMContentLoaded", () => {
  ['arquitectura'].forEach(e => {
    poblarSelectAños(e);
    window[`cicloSeleccionado${e}`] = 'total';
    actualizarGraficosEspecialidad(e);
  });
});