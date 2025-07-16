<?php

$url = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "registro_asistencia";

$mysqli = new mysqli($url, $usuario, $contraseña, $base_datos);
if ($mysqli->connect_error) {
    die(json_encode(["registrado" => false, "error" => "Error de conexión"]));
}

?>