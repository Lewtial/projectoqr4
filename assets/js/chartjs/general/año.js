fetch('../procesadores/tipos datos/año.php')
  .then(res => res.json())
  .then(data => {
    const ctx = document.getElementById('graficoAnual').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [{
          label: 'Cantidad',
          data: data.valores,
          backgroundColor: ['#27ae60', '#c0392b'],
          borderColor: ['#1e8449', '#922b21'],
          borderWidth: 1.5
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: '',
            font: {
              size: 16
            }
          },
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = data.valores.reduce((a, b) => a + b, 0);
                const value = context.raw;
                const percent = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${value} (${percent}%)`;
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            title: {
              display: true,
              text: ''
            }
          }
        }
      }
    });
  })
  .catch(err => console.error('Error al cargar gráfico anual:', err));
