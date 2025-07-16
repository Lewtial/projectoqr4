fetch('../procesadores/tipos datos/mes.php')
      .then(res => res.json())
      .then(data => {
        const ctx = document.getElementById('graficoMensual').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Asistencias por mes',
              data: data.valores,
              borderColor: '#2980b9',
              backgroundColor: 'rgba(41, 128, 185, 0.2)',
              tension: 0.4,
              fill: true,
              pointBackgroundColor: '#3498db',
              pointRadius: 4
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: 'Asistencias registradas por mes en ' + new Date().getFullYear()
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Cantidad de asistencias'
                }
              }
            }
          }
        });
      });