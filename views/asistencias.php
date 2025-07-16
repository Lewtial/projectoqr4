<?php
$contador = 1;

require '../includes/conexion.php';

// Captura de filtros
$f_nombre = $_GET['nombre'] ?? '';
$f_dia = $_GET['dia'] ?? '';
$f_mes = $_GET['mes'] ?? '';
$f_anio = $_GET['anio'] ?? '';
$f_especialidad = $_GET['especialidad'] ?? '';
$f_ciclo = $_GET['ciclo'] ?? '';

// Consulta base con JOIN
$sql = "SELECT 
            asistencias.id,
            alumnos.apellido_nombre,
            especialidades.nombre AS especialidad,
            ciclos.nombre AS ciclo,
            asistencias.fecha_hora
        FROM asistencias
        INNER JOIN alumnos ON asistencias.id_alumno = alumnos.id_alumno
        INNER JOIN especialidades ON alumnos.id_especialidad = especialidades.id_especialidad
        INNER JOIN ciclos ON alumnos.id_ciclo = ciclos.id_ciclo
        WHERE 1=1";


// Filtros dinámicos
if (!empty($f_nombre)) {
    $sql .= " AND alumnos.apellido_nombre LIKE '%$f_nombre%'";
}
if (!empty($f_dia)) {
    $sql .= " AND DAY(fecha_hora) = $f_dia";
}
if (!empty($f_mes)) {
    $sql .= " AND MONTH(fecha_hora) = $f_mes";
}
if (!empty($f_anio)) {
    $sql .= " AND YEAR(fecha_hora) = $f_anio";
}
if (!empty($f_especialidad)) {
    $sql .= " AND alumnos.id_especialidad LIKE '%$f_especialidad%'";
}
if (!empty($f_ciclo)) {
    $sql .= " AND alumnos.id_ciclo LIKE '%$f_ciclo%'";
}

$sql .= " ORDER BY fecha_hora DESC";

$resultado = $mysqli->query($sql);
$alumnos = [];
while ($row = $resultado->fetch_assoc()) {
    $alumnos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/asistencias.css">

    <link rel="stylesheet" href="../assets/css/all.css">
    <title>Asistencias Registradas</title>
</head>
<body>
    <div class="content-tablas">
        <h2>Registro de Asistencias</h2>

        <form method="GET">
            <a class="btn btn-secondary" href="admin.html">Regresar</a>
            <input type="text" name="nombre" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($f_nombre); ?>">
            <input type="number" name="dia" placeholder="Día (1-31)" min="1" max="31" value="<?php echo htmlspecialchars($f_dia); ?>">
            <input type="number" name="mes" placeholder="Mes (1-12)" min="1" max="12" value="<?php echo htmlspecialchars($f_mes); ?>">
            <input type="number" name="anio" placeholder="Año" value="<?php echo htmlspecialchars($f_anio); ?>">
            <select name="especialidad">
    <option value="">Todas las especialidades</option>
    <option value="arquitectura" <?php if ($f_especialidad == '1') echo 'selected'; ?>>Arquitectura</option>
    <option value="enfermeria" <?php if ($f_especialidad == '2') echo 'selected'; ?>>Enfermería</option>
    <option value="agropecuaria" <?php if ($f_especialidad == '3') echo 'selected'; ?>>Agropecuaria</option>
    <option value="contabilidad" <?php if ($f_especialidad == '4') echo 'selected'; ?>>Contabilidad</option>
    <option value="electronica" <?php if ($f_especialidad == '5') echo 'selected'; ?>>Electrónica</option>
</select>

<select name="ciclo">
    <option value="">Todos los ciclos</option>
    <option value="1" <?php if ($f_ciclo == '1') echo 'selected'; ?>>1</option>
    <option value="2" <?php if ($f_ciclo == '2') echo 'selected'; ?>>2</option>
    <option value="3" <?php if ($f_ciclo == '3') echo 'selected'; ?>>3</option>
    <option value="4" <?php if ($f_ciclo == '4') echo 'selected'; ?>>4</option>
    <option value="5" <?php if ($f_ciclo == '5') echo 'selected'; ?>>5</option>
    <option value="6" <?php if ($f_ciclo == '6') echo 'selected'; ?>>6</option>
</select>

            <button type="submit">Filtrar</button>
            <a class="btn btn-primary" href="asistencias.php">Limpiar</a>
        </form>

        <div class="content-table">
            <table>
                <thead>
                    <tr class="encabesados">
                        <th>ID Asistencia</th>
                        <th>Apellido y Nombre</th>
                        <th>Especialidad</th>
                        <th>Ciclo</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $alumno): ?>
                        <tr>
                            <td><?php echo $alumno['id']; ?></td>
                            <td><?php echo $alumno['apellido_nombre']; ?></td>
                            <td><?php echo $alumno['especialidad']; ?></td>
                            <td><?php echo $alumno['ciclo']; ?></td>
                            <td><?php echo $alumno['fecha_hora']; ?></td>
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
                            <p><?php echo $alumno['apellido_nombre']; ?></p>
                        </div>
                        <div>
                            <p>Especialidad</p>
                            <p><?php echo $alumno['especialidad']; ?></p>
                        </div>  
                        <div>
                            <p>Ciclo</p>
                            <p><?php echo $alumno['ciclo']; ?></p>
                        </div>
                        <div>
                            <p>Fecha y hora</p>
                            <p><?php echo $alumno['fecha_hora']; ?></p>
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
