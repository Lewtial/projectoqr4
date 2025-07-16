<?php
require '../../includes/conexion.php';
header('Content-Type: application/json');

if (!isset($_GET['especialidad'], $_GET['anio'], $_GET['mes'], $_GET['dia'])) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$especialidad = $_GET['especialidad'];
$anio = $_GET['anio'];
$mes = str_pad($_GET['mes'], 2, '0', STR_PAD_LEFT);
$dia = str_pad($_GET['dia'], 2, '0', STR_PAD_LEFT);
$fecha = "$anio-$mes-$dia";

// Obtener alumnos por especialidad
$sql = "SELECT a.id_alumno, a.apellido_nombre, e.nombre AS especialidad, c.nombre AS ciclo
        FROM alumnos a
        JOIN especialidades e ON a.id_especialidad = e.id_especialidad
        JOIN ciclos c ON a.id_ciclo = c.id_ciclo
        WHERE e.nombre = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $especialidad);
$stmt->execute();
$res = $stmt->get_result();

$alumnos = [];
while ($row = $res->fetch_assoc()) {
    $alumnos[$row['id_alumno']] = $row;
}

$totalEstudiantes = count($alumnos);

// Obtener asistencias por fecha
$sql2 = "SELECT id_alumno, fecha_hora
         FROM asistencias
         WHERE DATE(fecha_hora) = ?";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param('s', $fecha);
$stmt2->execute();
$res2 = $stmt2->get_result();

$asistencias = [];
while ($row = $res2->fetch_assoc()) {
    $id = $row['id_alumno'];
    $hora = date('H:i', strtotime($row['fecha_hora']));
    $asistencias[$id] = $hora; // Solo se guarda hora
}

$totalAsistencias = 0;
$totalFaltas = 0;
$resultadoFinal = [];

foreach ($alumnos as $id => $datos) {
    if (isset($asistencias[$id])) {
        $estado = '✔️';
        $hora = $asistencias[$id];
        $totalAsistencias++;
    } else {
        $estado = '❌';
        $hora = '-';
        $totalFaltas++;
    }

    $resultadoFinal[] = [
        "nombre" => $datos['apellido_nombre'],
        "especialidad" => $datos['especialidad'],
        "ciclo" => $datos['ciclo'],
        "hora" => $hora,
        "estado" => $estado
    ];
}

echo json_encode([
    "totalEstudiantes" => $totalEstudiantes,
    "totalAsistencias" => $totalAsistencias,
    "totalFaltas" => $totalFaltas,
    "alumnos" => $resultadoFinal
]);
