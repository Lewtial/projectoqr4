fetch('../procesadores/tipos datos/hoy.php')
      .then(res => res.json())
      .then(data => {
        const ctx = document.getElementById('graficoCircular').getContext('2d');
        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Asistencias hoy',
              data: data.valores,
              backgroundColor: ['#2ecc71', '#e74c3c']
            }]
          },
          options: {
            responsive: true,
            plugins: {
              title: {
                display: true,
                text: ''
              },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    let total = context.chart._metasets[0].total;
                    let valor = context.raw;
                    let porcentaje = ((valor / total) * 100).toFixed(1);
                    return `${context.label}: ${valor} (${porcentaje}%)`;
                  }
                }
              }
            }
          }
        });
      });