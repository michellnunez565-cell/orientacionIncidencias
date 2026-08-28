<?php
// Conectar a la base de datos
$db = new PDO("sqlite:database.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Borrar todos los estudiantes que tengan símbolos raros o nombres vacíos
$stmt = $db->prepare("DELETE FROM students WHERE full_name LIKE '%�%' OR full_name = 'N/A' OR full_name IS NULL OR LENGTH(full_name) < 3");
$stmt->execute();

$borrados = $stmt->rowCount();
echo "<h1 style='font-family: Arial; color: green;'>Limpieza completada</h1>";
echo "<p>Se han eliminado <strong>$borrados</strong> registros corruptos de la base de datos.</p>";
echo "<p>Ahora tu sistema está limpio y listo para mostrar solo los estudiantes reales.</p>";
?>