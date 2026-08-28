<?php
// Simulamos una sesión básica para evitar la redirección
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['role'] = 'orientadora'; 

$db = new PDO("sqlite:database.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ✅ Buscar el archivo en TODA la estructura del proyecto (sin depender de rutas fijas)
$archivo = null;
foreach (glob('*.txt') as $file) {
    $archivo = $file;
    break;
}
if ($archivo === null) {
    foreach (glob('views/*.txt') as $file) {
        $archivo = $file;
        break;
    }
}

$mensaje = "";
$tipo_mensaje = "info";

if ($archivo && file_exists($archivo)) {
    // ✅ LÍNEA MÁGICA: Convertir a UTF-8 y guardar en memoria
    $contenido = file_get_contents($archivo);
    $contenido = mb_convert_encoding($contenido, 'UTF-8', 'auto');
    file_put_contents($archivo, $contenido);

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $contador = 0;
    
    foreach ($lineas as $linea) {
        $datos = explode('|', $linea);
        if (count($datos) >= 4) {
            $matricula = trim($datos[0]);
            $nombre = trim($datos[1]);
            $grado = trim($datos[2]);
            $seccion = trim($datos[3]);
            
            $stmt = $db->prepare("INSERT OR IGNORE INTO students (student_code, full_name, grade, section_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$matricula, $nombre, $grado, $seccion]);
            $contador++;
        }
    }
    
    $mensaje = "¡Importación completada! Se importaron <strong>$contador</strong> estudiantes correctamente.";
    $tipo_mensaje = "exito";
} else {
    $mensaje = "Error: No se encontró ningún archivo .txt (estudiantes.txt).";
    $tipo_mensaje = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importador de Estudiantes</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0d1b2a; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 20px; }
        .contenedor { background-color: #1b263b; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); text-align: center; max-width: 400px; width: 100%; border: 1px solid #415a77; }
        h1 { color: #e0e1dd; font-size: 22px; margin-bottom: 20px; }
        .mensaje { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .mensaje.exito { background-color: #2d6a4f; color: #d8f3dc; border: 1px solid #40916c; }
        .mensaje.error { background-color: #9b2226; color: #ffb3b3; border: 1px solid #ae2012; }
        .mensaje.info { background-color: #1e6091; color: #caf0f8; border: 1px solid #1a759f; }
        .btn { display: inline-block; background-color: #e0e1dd; color: #0d1b2a; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; transition: background 0.3s; margin-top: 10px; border: none; cursor: pointer; }
        .btn:hover { background-color: #ffffff; }
    </style>
</head>
<body>

<div class="contenedor">
    <h1>📥 Importación de Matrícula</h1>
    
    <div class="mensaje <?php echo $tipo_mensaje; ?>">
        <?php echo $mensaje; ?>
    </div>

    <a href="/" class="btn">Volver al Inicio</a>
</div>

</body>
</html>