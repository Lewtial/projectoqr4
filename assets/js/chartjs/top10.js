let anioTop10 = new Date().getFullYear();

function cargarSelectorAnios() {
    const selector = document.getElementById("top10SelectorAnio");
    const anioActual = new Date().getFullYear();

    for (let i = anioActual; i >= 2020; i--) {
        const option = document.createElement("option");
        option.value = i;
        option.textContent = i;
        if (i === anioTop10) option.selected = true;
        selector.appendChild(option);
    }
}

function cargarTop10() {
    const lista = document.getElementById("top10Lista");
    const selector = document.getElementById("top10SelectorAnio");
    anioTop10 = parseInt(selector.value);

    fetch(`../procesadores/tipos datos/top10.php?anio=${anioTop10}`)
        .then(res => res.json())
        .then(data => {
            lista.innerHTML = "";

            if (data.length === 0) {
                lista.innerHTML = "<p>No hay datos disponibles para este año.</p>";
                return;
            }

            data.forEach((estudiante, index) => {
                const medalIcon = index < 3 ? ["🥇", "🥈", "🥉"][index] : `${index + 1}°`;

                const item = `
                    <div class="top-item">
                        <div class="top-rank">${medalIcon}</div>
                        <div class="top-info">
                            <div class="top-name">${estudiante.nombre}</div>
                            <div class="top-specialty">${estudiante.especialidad || 'Sin especialidad'}</div>
                        </div>
                        <div class="top-stats">
                            <div class="top-percentage">${estudiante.porcentaje}%</div>
                            <div class="top-count">${estudiante.asistencias} asistencias</div>
                        </div>
                    </div>
                `;
                lista.innerHTML += item;
            });
        })
        .catch(err => {
            lista.innerHTML = "<p>Error cargando datos del top 10.</p>";
            console.error("Error cargando top 10:", err);
        });
}

function cambiarAnioTop10() {
    cargarTop10();
}

document.addEventListener("DOMContentLoaded", () => {
    cargarSelectorAnios();
    cargarTop10();
});
