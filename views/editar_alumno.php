<?php
require '../librerias/phpqrcode/qrlib.php'; 

require '../includes/conexion.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado");
}

$id = $_GET['id'];
$consulta = $mysqli->query("SELECT * FROM alumnos WHERE id_alumno = $id");
$alumno = $consulta->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $matricula_nueva = $_POST['matricula'];
    $especialidad = $_POST['especialidad'];
    $ciclo = $_POST['ciclo'];

    $matricula_anterior = $alumno['matricula'];

    // Validar que la matrícula no esté en uso por otro alumno
    $stmt_check = $mysqli->prepare("SELECT id_alumno FROM alumnos WHERE matricula = ? AND id_alumno != ?");
    $stmt_check->bind_param("si", $matricula_nueva, $id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        die("Error: La matrícula ya está registrada por otro alumno.");
    }

    // Si la matrícula cambió, elimina el QR anterior y genera uno nuevo
    if ($matricula_nueva !== $matricula_anterior) {
        $archivo_qr_anterior = "../qrcodes/{$matricula_anterior}.png";
        if (file_exists($archivo_qr_anterior)) {
            unlink($archivo_qr_anterior); // Borra QR viejo
        }

        // Genera QR nuevo
        $ruta_qr_nuevo = "../qrcodes/{$matricula_nueva}.png";
        QRcode::png($matricula_nueva, $ruta_qr_nuevo, QR_ECLEVEL_H, 10);
    }

    // Actualiza en la base de datos
    $stmt = $mysqli->prepare("UPDATE alumnos SET nombre=?, correo=?, matricula=?, especialidad=?, ciclo=? WHERE id_alumno=?");
    $stmt->bind_param("sssssi", $nombre, $correo, $matricula_nueva, $especialidad, $ciclo, $id);
    $stmt->execute();

    header("Location: alumnos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/global.css">
    <title>Editar Alumno</title>
</head>
<body>
    <h2>Editar Alumno</h2>
    <form method="post">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?php echo $alumno['nombre']; ?>"><br>

        <label>Correo:</label><br>
        <input type="email" name="correo" value="<?php echo $alumno['correo']; ?>"><br>

        <label>Matrícula:</label><br>
        <input type="text" name="matricula" value="<?php echo $alumno['matricula']; ?>"><br>

        <label>Especialidad:</label><br>
        <input type="text" name="especialidad" value="<?php echo $alumno['especialidad']; ?>"><br>

        <label>Ciclo:</label><br>
        <input type="text" name="ciclo" value="<?php echo $alumno['ciclo']; ?>"><br><br>

        <button type="submit">Guardar Cambios</button>
        <a class="btn btn-secondary" href="alumnos.php">Cancelar</a>
    </form>
</body>
</html>
