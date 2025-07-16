<?php
$contador = 1;

$mysqli = new mysqli("localhost", "root", "", "registro_asistencia");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

$sql2 = "SELECT 
            a.id_alumno,
            a.apellido_nombre as nombre,
            a.correo,
            a.matricula,
            e.nombre AS especialidad,
            c.nombre AS ciclo
        FROM alumnos a
        JOIN especialidades e ON a.id_especialidad = e.id_especialidad
        JOIN ciclos c ON a.id_ciclo = c.id_ciclo";
$result2 = $mysqli->query($sql2);
$alumnos = [];
while ($row = $result2->fetch_assoc()) {
    $alumnos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- <link rel="stylesheet" href="../assets/css/global.css"> -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    

    <!-- <script src="https://kit.fontawesome.com/3032bc7490.js" crossorigin="anonymous"></script> -->
    <link rel="stylesheet" href="../assets/css/all.css">
    <title>Lista de Alumnos</title>
</head>
<body>
    <div class="content-tablas">
        <h2>LISTA DE ALUMNOS</h2>

        <div class="filtrados">
            <a class="btn btn-secondary" href="admin.html">Regresar</a>
            <input type="text" id="filtroNombre" onkeyup="filtrar()" placeholder="Filtrar por nombre">
            <select id="filtroEspecialidad" onchange="filtrar()">
    <option value="">Todas las especialidades</option>
    <option value="arquitectura">Arquitectura</option>
    <option value="enfermeria">Enfermería</option>
    <option value="agropecuaria">Agropecuaria</option>
    <option value="contabilidad">Contabilidad</option>
    <option value="electronica">Electrónica</option>
</select>

<select id="filtroCiclo" onchange="filtrar()">
    <option value="">Todos los ciclos</option>
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
</select>
        </div>

        <div class="content-table">    
            <table>
                <thead>
                    <tr class="encabesados">
                        <th>ID</th>
                        <th>Apellido y Nombre</th>
                        <th>Correo</th>
                        <th>Matrícula</th>
                        <th>Especialidad</th>
                        <th>Ciclo</th>
                        <th>QR</th>
                        <th>Modificar</th>
                        <th>Descargar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?php echo $alumno['id_alumno']; ?></td>
                            <td class="nombre"><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['correo']; ?></td>
                            <td><?php echo $alumno['matricula']; ?></td>
                            <td class="especialidad"><?php echo $alumno['especialidad']; ?></td>
                            <td class="ciclo"><?php echo $alumno['ciclo']; ?></td>
                            <td><img src="../qrcodes/<?php echo $alumno['matricula']; ?>.png" alt="QR" width="80"></td>
                            <td><a href="editar_alumno.php?id=<?php echo $alumno['id_alumno']; ?>">📝</a></td>
                            <td><a href="../procesadores/vista_tarjeta.php?nombre=<?php echo urlencode($alumno['nombre']); ?>&matricula=<?php echo urlencode($alumno['matricula']); ?>">🧾</a></td>
                            <td><a href="../procesadores/eliminar_alumno.php?id=<?php echo $alumno['id_alumno']; ?>" onclick="return confirm('¿Estás seguro de eliminar este alumno?')">🗑️</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="content-alumno">
            <?php foreach ($alumnos as $alumno): ?>
                <div class="secion-alumno" id=<?php echo "seccion-".$contador; ?>>
                    <div class="content-boton">
                        <i class="fa-solid fa-angle-down" id=<?php echo "abajo-".$contador; ?> onclick="activarSeccion(<?php echo $contador; ?>)"></i>
                        <i class="fa-solid fa-angle-up arriba" id=<?php echo "arriba-".$contador; ?> onclick="cerrarSeccion(<?php echo $contador ;?>)"></i>
                    </div>

                    <div class="datos">
                        <div>
                            <p>Nombre</p>    
                            <p class="nombre"><?php echo $alumno['nombre']; ?></p>
                        </div>
                        <div>
                            <p>Correo</p>
                            <p><?php echo $alumno['correo']; ?></p>
                        </div>
                        <div>
                            <p>Matrícula</p>
                            <p><?php echo $alumno['matricula']; ?></p>
                        </div>
                        <div>
                            <p>Especialidad</p>
                            <p class="especialidad"><?php echo $alumno['especialidad']; ?></p>
                        </div>  
                        <div>
                            <p>Ciclo</p>
                            <p class="ciclo"><?php echo $alumno['ciclo']; ?></p>
                        </div>
                        <div>
                            <p>QR</p>
                            <p><img src="../qrcodes/<?php echo $alumno['matricula']; ?>.png" alt="QR" width="80"></p>
                        </div>
                        <div>
                            <p>Opciones</p>
                            <div class="content-opciones">
                                <td><a href="editar_alumno.php?id=<?php echo $alumno['id_alumno']; ?>">📝</a></td>
                                <td><a href="../procesadores/vista_tarjeta.php?nombre=<?php echo urlencode($alumno['nombre']); ?>&matricula=<?php echo urlencode($alumno['matricula']); ?>">🧾</a></td>
                                <td><a href="../procesadores/eliminar_alumno.php?id=<?php echo $alumno['id_alumno']; ?>" onclick="return confirm('¿Estás seguro de eliminar este alumno?')">🗑️</a></td>
                            </div>
                        </div>
                    </div>
                </div>
            
                <?php  $contador = $contador + 1 ?>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="../assets/js/alumnos.js"></script>
</body>
</html>