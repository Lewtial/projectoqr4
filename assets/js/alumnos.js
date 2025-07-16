function filtrar() {
    const filtroNombre = document.getElementById("filtroNombre").value.toLowerCase();
    const filtroEspecialidad = document.getElementById("filtroEspecialidad").value.toLowerCase();
    const filtroCiclo = document.getElementById("filtroCiclo").value.toLowerCase();

    // Filtrar la tabla
    const filas = document.querySelectorAll("tbody tr");

    filas.forEach(fila => {
        const nombre = fila.querySelector(".nombre")?.textContent.toLowerCase() || "";
        const especialidad = fila.querySelector(".especialidad")?.textContent.toLowerCase() || "";
        const ciclo = fila.querySelector(".ciclo")?.textContent.toLowerCase() || "";

        const coincide = nombre.includes(filtroNombre) &&
                         especialidad.includes(filtroEspecialidad) &&
                         ciclo.includes(filtroCiclo);

        fila.style.display = coincide ? "" : "none";
    });

    // Filtrar las tarjetas (vista móvil)
    const tarjetas = document.querySelectorAll(".secion-alumno");

    tarjetas.forEach(tarjeta => {
        const nombre = tarjeta.querySelector(".nombre")?.textContent.toLowerCase() || "";
        const especialidad = tarjeta.querySelector(".especialidad")?.textContent.toLowerCase() || "";
        const ciclo = tarjeta.querySelector(".ciclo")?.textContent.toLowerCase() || "";

        const coincide = nombre.includes(filtroNombre) &&
                         especialidad.includes(filtroEspecialidad) &&
                         ciclo.includes(filtroCiclo);

        tarjeta.style.display = coincide ? "" : "none";
    });
}












function activarSeccion(num) {
    document.getElementById("seccion-"+num).classList.add("secion-alumno-active");

    document.getElementById("abajo-"+num).style.display = "none";
    document.getElementById("arriba-"+num).style.display = "block";

}

function cerrarSeccion(num) {
    document.getElementById("seccion-"+num).classList.remove("secion-alumno-active");

    document.getElementById("abajo-"+num).style.display = "block";
    document.getElementById("arriba-"+num).style.display = "none";

}

