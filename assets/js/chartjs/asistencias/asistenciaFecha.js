function filtrar() {
  const especialidad = document.getElementById("filtroEspecialidad").value;
  const anio = document.getElementById("filtroAño").value;
  const mes = document.getElementById("filtroMes").value;
  const dia = document.getElementById("filtroDia").value;

  if (!especialidad || !anio || !mes || !dia) return;

  fetch("../procesadores/tipos datos/asistenciaFecha.php?especialidad=" + especialidad + "&anio=" + anio + "&mes=" + mes + "&dia=" + dia)
    .then(res => res.json())
    .then(data => {
      actualizarGrafico(data.totalAsistencias, data.totalFaltas);
      document.getElementById("totalEstudiantes").textContent = "Total de estudiantes: " + data.totalEstudiantes;
      generarTabla(data.alumnos);
    });
}

let graficoCircular = null;
function actualizarGrafico(asistencias, faltas) {
  const ctx = document.getElementById("asistenciaGraficoCircular").getContext("2d");

  if (graficoCircular) graficoCircular.destroy();

  graficoCircular = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Asistencias', 'Faltas'],
      datasets: [{
        data: [asistencias, faltas],
        backgroundColor: ['#4CAF50', '#F44336']
      }]
    }
  });
}

function generarTabla(alumnos) {
  const tbody = document.getElementById("tablaAsistenciasBody");
  tbody.innerHTML = "";
  let i = 1;
  alumnos.forEach(alumno => {
    const fila = document.createElement("tr");
    fila.innerHTML = `
      <td>${i++}</td>
      <td>${alumno.nombre}</td>
      <td>${alumno.especialidad}</td>
      <td>${alumno.ciclo}</td>
      <td>${alumno.hora}</td>
      <td style="text-align:center">${alumno.estado === '✔️' ? '✔️' : '❌'}</td>
    `;
    tbody.appendChild(fila);
  });
}