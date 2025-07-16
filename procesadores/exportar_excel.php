<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "registro_asistencia");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Encabezados para que el navegador descargue como Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=datos_alumnos.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta a la tabla





$sql = "SELECT a.nombre as nombre, a.correo as correo, a.matricula as matricula, a.especialidad as especialidad, a.ciclo as ciclo, asi.fecha_hora as fecha_hora
from asistencias asi
join alumnos a on a.id_alumno = asi.id_alumno";
$resultado = $conexion->query($sql);

// Imprime la tabla como HTML plano (Excel lo interpreta)
echo "<table border='1'>";
echo "<tr><th>Nombre</th><th>Correo</th><th>Matricula</th><th>Especialidad</th><th>Ciclo</th><th>Fecha y Hora</th></tr>";

while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$fila['nombre']."</td>";
    echo "<td>".$fila['correo']."</td>";
    echo "<td>".$fila['matricula']."</td>";
    echo "<td>".$fila['especialidad']."</td>";
    echo "<td>".$fila['ciclo']."</td>";
    echo "<td>".$fila['fecha_hora']."</td>";
    echo "</tr>";
}

echo "</table>";
?>
