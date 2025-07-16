<?php
require '../../includes/conexion.php';

$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Obtener días hábiles con asistencia para ese año
$sqlDiasHabiles = "SELECT DISTINCT DATE(fecha_hora) AS dia
                   FROM asistencias
                   WHERE YEAR(fecha_hora) = $anio
                   AND DAYOFWEEK(fecha_hora) BETWEEN 2 AND 6";

$resultDias = $mysqli->query($sqlDiasHabiles);
$totalDias = $resultDias->num_rows ?: 1;

$sql = "
SELECT 
    a.id_alumno,
    a.apellido_nombre AS nombre,
    e.nombre AS especialidad,
    COUNT(*) AS asistencias,
    ROUND(COUNT(*) * 100 / $totalDias, 2) AS porcentaje
FROM asistencias s
JOIN alumnos a ON a.id_alumno = s.id_alumno
LEFT JOIN especialidades e ON a.id_especialidad = e.id_especialidad
WHERE YEAR(s.fecha_hora) = $anio
GROUP BY a.id_alumno
ORDER BY asistencias DESC, MIN(TIME(s.fecha_hora)) ASC
LIMIT 10
";

$result = $mysqli->query($sql);

$topEstudiantes = [];

while ($row = $result->fetch_assoc()) {
    $topEstudiantes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($topEstudiantes);
?>