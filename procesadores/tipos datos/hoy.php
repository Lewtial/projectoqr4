<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Lima");
$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Total alumnos
$res = $mysqli->query("SELECT COUNT(*) AS total FROM alumnos");
$total_alumnos = $res->fetch_assoc()['total'];


$hoy = date('Y-m-d');

// Contar alumnos únicos que registraron asistencia hoy
$sql = "SELECT COUNT(DISTINCT id_alumno) AS asistieron
        FROM asistencias
        WHERE DATE(fecha_hora) = '$hoy'";

$result = $mysqli->query($sql);
$row = $result->fetch_assoc();


$asistieron = (int)$row['asistieron'];
$no_asistieron = $total_alumnos - $asistieron;

echo json_encode([
    'labels' => ['Asistieron', 'No asistieron'],
    'valores' => [$asistieron, $no_asistieron]
]);
