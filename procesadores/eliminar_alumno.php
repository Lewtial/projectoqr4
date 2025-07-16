<?php
$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

if (!isset($_GET['id'])) {
    die("ID no proporcionado");
}

$id = $_GET['id'];

// Obtener matrícula para borrar el QR
$resultado = $mysqli->query("SELECT matricula FROM alumnos WHERE id_alumno = $id");
$fila = $resultado->fetch_assoc();
$matricula = $fila['matricula'];

// Borrar QR
$archivoQR = "../qrcodes/{$matricula}.png";
if (file_exists($archivoQR)) {
    unlink($archivoQR);
}

// Borrar alumno de la base
$mysqli->query("DELETE FROM alumnos WHERE id_alumno = $id");

header("Location: ../views/alumnos.php");
exit;
