fetch('../procesadores/tipos datos/semana.php')
    .then(res => res.json())
    .then(data => {
        const ctx = document.getElementById('graficoSemana').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Asistencias',
                        data: data.asistencias,
                        backgroundColor: '#27ae60'
                    },
                    {
                        label: 'Faltas',
                        data: data.faltas,
                        backgroundColor: '#c0392b'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Asistencias y Faltas Semanales'
                    }
                }
            }
        });
    });