<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

$anio = date('Y');

// Consulta: asistencias por mes
$sql = "SELECT MONTH(fecha_hora) AS mes, COUNT(*) AS total 
        FROM asistencias 
        WHERE YEAR(fecha_hora) = '$anio' 
        GROUP BY mes 
        ORDER BY mes";

$result = $mysqli->query($sql);

// Inicializar todos los meses con 0
$meses_es = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
             'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$valores = array_fill(0, 12, 0);

while ($row = $result->fetch_assoc()) {
    $indice = (int)$row['mes'] - 1; // 0 basado
    $valores[$indice] = (int)$row['total'];
}

echo json_encode([
    'labels' => $meses_es,
    'valores' => $valores
]);
