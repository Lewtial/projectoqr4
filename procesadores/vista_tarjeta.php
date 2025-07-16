<?php
$nombre = $_GET['nombre'] ?? 'Alumno';
$matricula = $_GET['matricula'] ?? 'A000';

$logo_path = "../assets/img/logo iestp.png";
$qr_path = "../qrcodes/$matricula.png";

function imgToBase64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

$logo_base64 = imgToBase64($logo_path);
$qr_base64 = imgToBase64($qr_path);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarjeta de Estudiante</title>
    <style>
        :root {
            --ancho-tarjeta: 68mm;
            --alto-tarjeta: 98.5mm;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            text-align: center;
        }

        .tarjeta, .reverso {
            width: var(--ancho-tarjeta);
            height: var(--alto-tarjeta);
            border-radius: 10px;
            margin: 10mm auto;
            box-sizing: border-box;
            overflow: hidden;
        }

        .tarjeta {
            background-color: #fdfdfd;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3mm;
            border-radius: 10px;
            position: relative;
        }

        .tarjeta {
   background: linear-gradient(0deg,rgba(53, 232, 46, 1) 30%, rgba(237, 228, 79, 1) 70%);

    /* border: 0.8mm solid #000; */
    width: var(--ancho-tarjeta);
    height: var(--alto-tarjeta);
    margin: 10mm auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3mm;
    
    box-sizing: border-box;
    position: relative;
    color: #000;
}


        .content-1 {
            height: 50%;
            z-index: 10;
        }
        .content-2 {
            width: 100%;
            height: 50%;
            display: flex;
            align-items: end;
            justify-content: center;
            z-index: 10;
        }



        .logo img {
            max-width: 100%;
            max-height: 25mm;
            object-fit: contain;
        }

        .estudiante {
            font-size: 7pt;
            font-weight: bold;
            color: #000000;
            margin-top: 7px;
        }

        .nombre {
            font-size: 10pt;
            margin: 2mm 0;
            color: #000;
        }

        .qr img {
            max-width: 100%;
            max-height: 35mm;
            object-fit: contain;
        }

        .btn-imprimir {
            margin: 10px;
            padding: 10px 20px;
            font-size: 14pt;
            background-color: #2b8d0a;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .reverso {
    width: var(--ancho-tarjeta);
    height: var(--alto-tarjeta);
    /* border: 0.8mm solid #000; */
    margin: 10mm auto;
    box-sizing: border-box;
    overflow: hidden;
    background: linear-gradient(0deg,rgba(53, 232, 46, 1) 30%, rgba(237, 228, 79, 1) 70%);
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.contenido-reverso {
    display: flex;
    
    align-items: center;
    justify-content: center;
    transform: rotate(90deg); /* texto vertical */
}

.logo-reverso {
    width: 21mm;
    height: auto;
    margin-bottom: 4mm;
}

.texto-vertical {
    width: 200px;
    font-size: 9pt;
    font-weight: bold;
    text-align: center;
    color: white;
    line-height: 1.4;
}






        .orificio {
    width: 4mm;
    height: 4mm;
    border: 1px solid #000;
    border-radius: 50%;
    background-color: white;
    margin: 0 auto 2mm auto;
}

        @media print {
            body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .btn-imprimir {
        display: none;
    }
        }



    </style>
</head>
<body>

    <button class="btn-imprimir" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>

    <!-- Cara Frontal -->
    <div class="tarjeta">
        

        <div class="content-1">
            <div class="orificio"></div>


        <div class="logo">
            <img src="<?php echo $logo_base64; ?>" alt="Logo">
        </div>

        <div class="estudiante">ESTUDIANTE</div>
        <div class="nombre"><?php echo htmlspecialchars($nombre); ?></div>
        </div>
        <div class="content-2">
            <div class="qr">
            <img src="<?php echo $qr_base64; ?>" alt="QR">
        </div>
        </div>
    </div>

    <!-- Reverso -->
    <div class="reverso">
    <div class="contenido-reverso">
        <img class="logo-reverso" src="<?php echo $logo_base64; ?>" alt="Logo">
        <div class="texto-vertical">
            Instituto de Educación Superior<br>
            Tecnológico Publico Cañete
        </div>
    </div>
</div>


</body>
</html>