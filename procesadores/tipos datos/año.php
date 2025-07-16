<?php
header('Content-Type: application/json');
date_default_timezone_set("America/Lima");

$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Conexión fallida: ' . $mysqli->connect_error]));
}

// Total de alumnos reales
$res = $mysqli->query("SELECT COUNT(*) AS total FROM alumnos");
$total_alumnos = (int)($res->fetch_assoc()['total'] ?? 0);

// Obtener primera y última fecha con asistencia este año
$anio = date('Y');
$res = $mysqli->query("SELECT MIN(DATE(fecha_hora)) AS inicio, MAX(DATE(fecha_hora)) AS fin FROM asistencias WHERE YEAR(fecha_hora) = '$anio'");
$fechas = $res->fetch_assoc();

$inicio = $fechas['inicio'] ?? "$anio-01-01";
$fin = $fechas['fin'] ?? "$anio-12-31";

$inicio_dt = new DateTime($inicio);
$fin_dt = new DateTime($fin);

$asistencias_totales = 0;
$faltas_totales = 0;

// Recorrer días hábiles
while ($inicio_dt <= $fin_dt) {
    $diaSemana = $inicio_dt->format('N'); // 1 = lunes, 7 = domingo

    if ($diaSemana <= 5) { // Solo lunes a viernes
        $fecha = $inicio_dt->format('Y-m-d');

        // Verificar cuántos alumnos únicos asistieron ese día
        $res = $mysqli->query("SELECT COUNT(DISTINCT id_alumno) AS total FROM asistencias WHERE DATE(fecha_hora) = '$fecha'");
        $asistencias_dia = (int)($res->fetch_assoc()['total'] ?? 0);

        if ($asistencias_dia > 0) {
            $asistencias_totales += $asistencias_dia;
            $faltas_totales += max(0, $total_alumnos - $asistencias_dia);
        }
        // Si asistencias_dia == 0, lo consideramos feriado, y no sumamos faltas
    }

    $inicio_dt->modify('+1 day');
}

// Enviar JSON
echo json_encode([
    'labels' => ['Asistencias', 'Faltas'],
    'valores' => [$asistencias_totales, $faltas_totales]
]);