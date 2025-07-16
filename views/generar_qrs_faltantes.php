<?php
require '../librerias/phpqrcode/qrlib.php';
require '../includes/conexion.php';

$carpeta_qr = "../qrcodes/";
if (!file_exists($carpeta_qr)) {
    mkdir($carpeta_qr, 0777, true);
}

// Obtener alumnos que no tienen QR
$resultado = $mysqli->query("SELECT id_alumno, matricula FROM alumnos WHERE qr_code IS NULL");

while ($alumno = $resultado->fetch_assoc()) {
    $id = $alumno['id_alumno'];
    $matricula = $alumno['matricula'];
    $qr_texto = "ALUMNO:$matricula";

    $temp_file = $carpeta_qr . "temp_" . $matricula . ".png";
    $nombre_archivo = $carpeta_qr . $matricula . ".png";

    // Paso 1: Generar QR con fondo blanco
    QRcode::png($qr_texto, $temp_file, QR_ECLEVEL_H, 6, 2);

    // Paso 2: Convertir fondo blanco a transparente
    $original = imagecreatefrompng($temp_file);
    $ancho = imagesx($original);
    $alto = imagesy($original);

    $transparente = imagecreatetruecolor($ancho, $alto);
    imagealphablending($transparente, false);
    imagesavealpha($transparente, true);

    $alpha_color = imagecolorallocatealpha($transparente, 0, 0, 0, 127);
    imagefill($transparente, 0, 0, $alpha_color);

    for ($x = 0; $x < $ancho; $x++) {
        for ($y = 0; $y < $alto; $y++) {
            $color_index = imagecolorat($original, $x, $y);
            $colores = imagecolorsforindex($original, $color_index);

            if ($colores['red'] > 240 && $colores['green'] > 240 && $colores['blue'] > 240) {
                imagesetpixel($transparente, $x, $y, $alpha_color); // blanco → transparente
            } else {
                $negro = imagecolorallocatealpha($transparente, 0, 0, 0, 0);
                imagesetpixel($transparente, $x, $y, $negro);
            }
        }
    }

    imagepng($transparente, $nombre_archivo);
    imagedestroy($original);
    imagedestroy($transparente);
    unlink($temp_file);

    // Actualizar QR en BD
    $stmt = $mysqli->prepare("UPDATE alumnos SET qr_code = ? WHERE id_alumno = ?");
    $stmt->bind_param("si", $nombre_archivo, $id);
    $stmt->execute();

    echo "QR generado para matrícula $matricula<br>";
}

echo "<br><strong>Proceso completado.</strong>";
?>
