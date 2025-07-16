<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Lima");

require __DIR__ . '/../includes/conexion.php';



$response = [
    "total_alumnos" => 0,
    "asistencias_hoy" => 0,
    "promedio_asistencia" => 0,
    "total_especialidades" => 0,
    "por_especialidad" => [],
    "por_ciclo" => []
];

// Total alumnos
$res = $mysqli->query("SELECT COUNT(*) AS total FROM alumnos");
$response['total_alumnos'] = $res->fetch_assoc()['total'];

// Asistencias de hoy
$hoy = date("Y-m-d");
$res = $mysqli->query("SELECT COUNT(*) AS total FROM asistencias WHERE DATE(fecha_hora) = '$hoy'");
$response['asistencias_hoy'] = $res->fetch_assoc()['total'];

// Promedio de asistencia = (asistencias totales / alumnos registrados * 100)
$res = $mysqli->query("SELECT COUNT(*) AS total FROM asistencias");
$asistencias_totales = $res->fetch_assoc()['total'];
$response['promedio_asistencia'] = $response['total_alumnos'] > 0 
    ? round(($asistencias_totales / $response['total_alumnos']) * 100)
    : 0;

// Total especialidades
$res = $mysqli->query("SELECT COUNT(*) AS total FROM especialidades");
$response['total_especialidades'] = $res->fetch_assoc()['total'];

// Alumnos por especialidad
$res = $mysqli->query("
    SELECT e.nombre AS especialidad, COUNT(a.id_especialidad) AS total
    FROM alumnos a
    JOIN especialidades e ON a.id_especialidad = e.id_especialidad
    GROUP BY e.nombre
    order by e.id_especialidad
");
while ($row = $res->fetch_assoc()) {
    $response['por_especialidad'][] = $row;
}

// Alumnos por ciclo
$res = $mysqli->query("
    SELECT c.nombre AS ciclo, COUNT(a.id_ciclo) AS total
    FROM alumnos a
    JOIN ciclos c ON a.id_ciclo = c.id_ciclo
    GROUP BY c.nombre
");
while ($row = $res->fetch_assoc()) {
    $response['por_ciclo'][] = $row;
}

echo json_encode($response);



