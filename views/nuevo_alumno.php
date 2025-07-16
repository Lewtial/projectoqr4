<?php
require '../librerias/phpqrcode/qrlib.php';

require '../includes/conexion.php';

$mensaje = "";

// Obtener especialidades
$especialidades = $mysqli->query("SELECT id_especialidad, nombre FROM especialidades");

// Obtener ciclos
$ciclos = $mysqli->query("SELECT id_ciclo, nombre FROM ciclos");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $matricula = $_POST['matricula'];
    $id_especialidad = $_POST['especialidad'];
    $id_ciclo = $_POST['ciclo'];

    // Verificar si la matrícula ya existe
    $check = $mysqli->prepare("SELECT id_alumno FROM alumnos WHERE matricula = ?");
    $check->bind_param("s", $matricula);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "Error: La matrícula ya existe.";
    } else {
        // Insertar alumno sin qr_code aún
        $stmt = $mysqli->prepare("INSERT INTO alumnos (apellido_nombre, correo, matricula, id_especialidad, id_ciclo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $nombre, $correo, $matricula, $id_especialidad, $id_ciclo);
        $stmt->execute();

        // Obtener el ID del nuevo alumno
        $id_nuevo = $stmt->insert_id;

        // Generar el QR con fondo transparente
        $qr_texto = "ALUMNO:$matricula";
        $carpeta_qr = "../qrcodes/";
        if (!file_exists($carpeta_qr)) {
            mkdir($carpeta_qr, 0777, true);
        }

        $temp_file = $carpeta_qr . "temp_" . $matricula . ".png";
        $nombre_archivo = $carpeta_qr . $matricula . ".png";

        // Paso 1: Crear el QR en escala de grises (fondo blanco)
        QRcode::png($qr_texto, $temp_file, QR_ECLEVEL_H, 6, 2);

        // Paso 2: Convertir fondo blanco a transparente
        $original = imagecreatefrompng($temp_file);
        $ancho = imagesx($original);
        $alto = imagesy($original);

        // Crear imagen truecolor con transparencia
        $transparente = imagecreatetruecolor($ancho, $alto);
        imagealphablending($transparente, false);
        imagesavealpha($transparente, true);

        // Rellenar fondo transparente
        $alpha_color = imagecolorallocatealpha($transparente, 0, 0, 0, 127);
        imagefill($transparente, 0, 0, $alpha_color);

        // Copiar píxeles y convertir blanco a transparente
        for ($x = 0; $x < $ancho; $x++) {
            for ($y = 0; $y < $alto; $y++) {
                $color_index = imagecolorat($original, $x, $y);
                $colores = imagecolorsforindex($original, $color_index);

                if ($colores['red'] > 240 && $colores['green'] > 240 && $colores['blue'] > 240) {
                    // Es blanco, hacer transparente
                    imagesetpixel($transparente, $x, $y, $alpha_color);
                } else {
                    // Copiar pixel oscuro tal cual
                    $negro = imagecolorallocatealpha($transparente, 0, 0, 0, 0);
                    imagesetpixel($transparente, $x, $y, $negro);
                }
            }
        }

        // Guardar PNG con fondo transparente
        imagepng($transparente, $nombre_archivo);
        imagedestroy($original);
        imagedestroy($transparente);
        unlink($temp_file); // eliminar archivo temporal

        // Guardar ruta del QR en la Base de Datos
        $stmt_qr = $mysqli->prepare("UPDATE alumnos SET qr_code = ? WHERE id_alumno = ?");
        $stmt_qr->bind_param("si", $nombre_archivo, $id_nuevo);
        $stmt_qr->execute();

        $mensaje = "Alumno registrado y QR generado correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/nuevo_alumno.css">
    <title>Registrar Alumno</title>
</head>
<body>
    <div class="container">
        <div class="content_form">
            <h2>Registrar Nuevo Alumno</h2>

    <?php if ($mensaje): ?>
        <p style="color:<?php echo str_starts_with($mensaje, "✅") ? 'green' : 'red'; ?>">
            <?php echo $mensaje; ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Correo:</label>
        <input type="email" name="correo" required>

        <label>Matrícula:</label>
        <input type="text" name="matricula" required>

        <label>Especialidad:</label>
<select name="especialidad" required>
    <option value="">Seleccione una especialidad</option>
    <?php while ($row = $especialidades->fetch_assoc()): ?>
        <option value="<?php echo $row['id_especialidad']; ?>">
            <?php echo htmlspecialchars($row['nombre']); ?>
        </option>
    <?php endwhile; ?>
</select>

<label>Ciclo:</label>
<select name="ciclo" required>
    <option value="">Seleccione un ciclo</option>
    <?php while ($row = $ciclos->fetch_assoc()): ?>
        <option value="<?php echo $row['id_ciclo']; ?>">
            <?php echo htmlspecialchars($row['nombre']); ?>
        </option>
    <?php endwhile; ?>
</select>

        <button type="submit">Registrar Alumno</button>
    </form>

        <a class="btn btn-primary" href="alumnos.php">Volver a la lista</a>
        <a class="btn btn-secondary" href="admin.html">Regresar</a>
        </div>

        <div class="content_img"></div>

    </div>


</body>
</html>