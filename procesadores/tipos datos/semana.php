<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Lima");

$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Total alumnos
$res = $mysqli->query("SELECT COUNT(*) AS total FROM alumnos");
$total_alumnos = (int)$res->fetch_assoc()['total'];

// Mapeo inglés → español
$diasEsp = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes'
];

$labels = array_values($diasEsp);
$asistencias = array_fill_keys($labels, 0);
$faltas = array_fill_keys($labels, 0);

// Fechas de la semana actual
$inicioSemana = date('Y-m-d', strtotime('monday this week'));
$finSemana = date('Y-m-d', strtotime('friday this week'));
$hoy = date('Y-m-d');

// Consulta asistencias por día
$sql = "SELECT 
            DATE(fecha_hora) AS fecha,
            COUNT(DISTINCT id_alumno) AS total
        FROM asistencias
        WHERE DATE(fecha_hora) BETWEEN '$inicioSemana' AND '$finSemana'
        GROUP BY DATE(fecha_hora)
        ORDER BY fecha";

$result = $mysqli->query($sql);

// Procesar resultados
while ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha'];
    $diaIngles = date('l', strtotime($fecha));
    
    if (isset($diasEsp[$diaIngles])) {
        $diaEsp = $diasEsp[$diaIngles];
        $asistencias[$diaEsp] = (int)$row['total'];
        $faltas[$diaEsp] = $total_alumnos - $asistencias[$diaEsp];
    }
}

// Limpiar días futuros: poner 0 si la fecha aún no llega
foreach ($diasEsp as $diaIng => $diaEsp) {
    $fecha_dia = date('Y-m-d', strtotime($diaIng . ' this week'));
    if ($fecha_dia > $hoy) {
        $asistencias[$diaEsp] = 0;
        $faltas[$diaEsp] = 0;
    }
}

echo json_encode([
    'labels' => $labels,
    'asistencias' => array_values($asistencias),
    'faltas' => array_values($faltas)
]);
