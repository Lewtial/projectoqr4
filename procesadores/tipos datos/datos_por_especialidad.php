<?php
header('Content-Type: application/json');
require '../../includes/conexion.php';

$especialidad = $_GET['especialidad'] ?? '';
$ciclo = $_GET['ciclo'] ?? 'total';
$anio = $_GET['anio'] ?? date('Y');

$rangos_ciclo = [
    '1' => [1, 2],
    '2' => [3, 4],
    '3' => [5, 6],
    'total' => [1, 6]
];

if (!isset($rangos_ciclo[$ciclo]) || !$especialidad) {
    echo json_encode(["error" => "Parámetros inválidos"]);
    exit;
}

[$inicioCiclo, $finCiclo] = $rangos_ciclo[$ciclo];

$inicioAnio = "$anio-01-01";
$finAnio = "$anio-12-31";

// Obtener IDs
$sql = "SELECT id_alumno FROM alumnos WHERE id_especialidad = ? AND id_ciclo BETWEEN ? AND ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sii", $especialidad, $inicioCiclo, $finCiclo);
$stmt->execute();
$result = $stmt->get_result();
$ids = [];
while ($row = $result->fetch_assoc()) {
    $ids[] = $row['id_alumno'];
}
if (empty($ids)) {
    echo json_encode([
        "semana" => [],
        "anual" => [],
        "hoy" => ["asistencias" => 0, "faltas" => 0]
    ]);
    exit;
}
$idList = implode(",", $ids);

// Semana actual (lunes a viernes)
$hoy = new DateTime();
$diaSemana = $hoy->format('N');
$lunes = (clone $hoy)->modify('-' . ($diaSemana - 1) . ' days');
$diasSemana = [];
for ($i = 0; $i < 5; $i++) {
    $dia = (clone $lunes)->modify("+$i days")->format('Y-m-d');
    $diasSemana[] = $dia;
}

$asistenciasSemana = [];
foreach ($diasSemana as $fecha) {
    $sql = "SELECT COUNT(*) as total FROM asistencias WHERE DATE(fecha_hora) = ? AND id_alumno IN ($idList)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $asistencias = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $asistenciasSemana[] = [
        "dia" => $fecha,
        "asistencias" => $asistencias
    ];
}

// Anual por mes
$asistenciasAnual = [];
for ($mes = 1; $mes <= 12; $mes++) {
    $inicio = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
    $fin = date("Y-m-t", strtotime($inicio));

    $sql = "SELECT COUNT(*) as total FROM asistencias WHERE fecha_hora BETWEEN ? AND ? AND id_alumno IN ($idList)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $inicio, $fin);
    $stmt->execute();
    $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

    $asistenciasAnual[] = [
        "mes" => $mes,
        "asistencias" => $total
    ];
}

// Asistencia del día
$fechaHoy = date("Y-m-d");
$sql = "SELECT COUNT(*) as total FROM asistencias WHERE DATE(fecha_hora) = ? AND id_alumno IN ($idList)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $fechaHoy);
$stmt->execute();
$totalHoy = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

$sql = "SELECT COUNT(*) as total FROM alumnos WHERE id_especialidad = ? AND id_ciclo BETWEEN ? AND ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sii", $especialidad, $inicioCiclo, $finCiclo);
$stmt->execute();
$totalAlumnos = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 1);

echo json_encode([
    "semana" => $asistenciasSemana,
    "anual" => $asistenciasAnual,
    "hoy" => [
        "asistencias" => $totalHoy,
        "faltas" => max(0, $totalAlumnos - $totalHoy)
    ]
]);
